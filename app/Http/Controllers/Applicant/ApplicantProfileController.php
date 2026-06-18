<?php

namespace App\Http\Controllers\Applicant;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Applicant\ApplicantPersonal;
use App\Models\Applicant\InterviewDeets;
use App\Models\Employee;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ApplicantProfileController extends Controller
{
    public function index(Request $request)
    {
        // If request is AJAX (DataTables)
        if ($request->ajax()) {

        $query = ApplicantPersonal::query()
        ->leftJoin('tblapp_address', 'tblapp_address.app_id', '=', 'tblapp_persinfo.app_id')
        ->leftJoin('tbl_user2', 'tbl_user2.U_Name', '=', 'tblapp_persinfo.app_email')
                ->groupBy('tblapp_persinfo.app_id')
                ->select([
            'tblapp_persinfo.app_id',
            'tbl_user2.U_Remarks as user_status',
                DB::raw("CONCAT_WS(
                    ' ',
                    app_fname,
                    NULLIF(CONCAT(LEFT(app_mname, 1), '.'), '.'),
                    app_lname
                ) AS name"),
                    'app_date as date_created',
                    'app_email as email',
                    DB::raw("IFNULL(app_mobile, app_telephone) as contact"),
                    'app_posapplied as position',
                ]);

                // 🔽 Custom status filter
                if ($request->filled('status')) {
                    $query->where('app_status', $request->status);
                }
    
                // 🔽 User active/inactive filter
                if ($request->filled('user_status')) {
                    $query->where('tbl_user2.U_Remarks', $request->user_status);
                }

            // 🔍 Search
            if ($request->search['value'] ?? false) {
                $search = $request->search['value'];
                $query->where(function ($q) use ($search) {
                    $q->whereRaw("CONCAT_WS(
                            ' ',
                            app_fname,
                            NULLIF(CONCAT(LEFT(app_mname, 1), '.'), '.'),
                            app_lname
                        ) LIKE ?", ["%{$search}%"])
                        ->orWhere('app_date', 'like', "%{$search}%")
                        ->orWhere('app_email', 'like', "%{$search}%")
                        ->orWhereRaw("IFNULL(app_mobile, app_telephone) LIKE ?", "%{$search}%");
                });
            }

            $totalRecords = ApplicantPersonal::count();
            $filteredRecords = $query->count();

            // 📄 Pagination
            $applicants = $query
                ->offset($request->start)
                ->limit($request->length)
                ->orderBy('tblapp_persinfo.app_id', 'desc')
                ->get();

            return response()->json([
                "draw"            => intval($request->draw),
                "recordsTotal"    => $totalRecords,
                "recordsFiltered" => $filteredRecords,
                "data"            => $applicants->map(function ($applicant) {
                    return [
                        'name' => $applicant->name,
                        'date_created' => $applicant->date_created,
                        'email' => $applicant->email,
                        'contact' => $applicant->contact,
                        'position' => $applicant->position,
                        'show_url'  => route('applicant.show', $applicant->app_id),
                    ];
                }),
            ]);
        }

        // Normal page load
        return view('pages.applicant.index', [
            'main_link' => 'applicant',
            'sub_link' => '',
            'maincat' => ''
        ]);
    }

    public function show($id, $tab = 'personal')
    {
        $applicant = ApplicantPersonal::find($id);
        $params = [
            'main_link' => 'applicant',
            'sub_link' => $tab,
            'maincat' => '',
            'applicant' => $applicant,
            // 'position_list' => Setting::positionList(),
            // 'employment_status' => Setting::emplStatusList()
        ];

        if ($tab == 'personal') {
            $params['provinceList'] = DB::table('tbl_province')->get();
            $params['municipalityList'] = DB::table('tbl_municipality as a')
                ->leftJoin('tbl_province as b', 'pr_code', '=', 'ct_province')
                ->select('a.*', 'b.pr_name as ct_province_name')
                ->get();
            $params['barangayList'] = DB::table('tbl_barangay as a')
                ->leftJoin('tbl_municipality as b', 'ct_id', '=', 'br_city')
                ->select('a.*', 'b.ct_name as br_city_name')
                ->get();
        }

        if ($tab == 'skill') {
            $params['skillsCategoryList'] = DB::table('tbl_skill_category')
                ->where('sc_stat', '=', '1')
                ->orderByRaw("IF(sc_id = 7, 1, 0) asc")
                ->orderBy('sc_title', 'asc')
                ->get();

            $params['skillsList'] = DB::table('tbl_skill_type as a')
                ->where('a.status', '=', '1')
                ->orderBy('skill_name', 'asc')
                ->get();

            $applicant->skill = $applicant?->skill->map(function ($s) use ($params) {
                $s->sc_title = $params['skillsCategoryList']->where('sc_id', $s->skill_category)->first()?->sc_title;
                $s->skill_name = $params['skillsList']->where('id', $s->skill_type)->first()?->skill_name;
                return $s;
            });
        }

        if ($tab == 'enneagram' && $applicant?->enneagram?->enneagram_ans) {
            $applicant->enneagram->enneagram_ans = json_decode($applicant->enneagram->enneagram_ans, true);
            $params['answerList'] = config('exams.enneagram');
            $scores = collect([
                1 => $applicant->enneagram->{'1_perfectionist'},
                2 => $applicant->enneagram->{'2_helper'},
                3 => $applicant->enneagram->{'3_achiever'},
                4 => $applicant->enneagram->{'4_romantic'},
                5 => $applicant->enneagram->{'5_observer'},
                6 => $applicant->enneagram->{'6_questioner'},
                7 => $applicant->enneagram->{'7_adventurer'},
                8 => $applicant->enneagram->{'8_asserter'},
                9 => $applicant->enneagram->{'9_peacemaker'}
            ])->sortDesc();
            $counter = 0;
            $prevscore = $scores->values()[0];
            $params['topItems'] = $scores
                ->filter(function ($value) use (&$counter, &$prevscore) {
                    if ($counter < 3 || $value == $prevscore) {
                        $counter++;
                        $prevscore = $value;
                        return true;
                    }
                    return false;
                });
        }

        if ($tab == 'tapt' && $applicant?->tapt?->tapt_ans) {
            $applicant->tapt->tapt_ans = json_decode($applicant->tapt->tapt_ans, true);
            $params['taptResult'] = implode('', [$applicant->tapt->e_i, $applicant->tapt->s_n, $applicant->tapt->t_f, $applicant->tapt->j_p]);
            // $params['answerList'] = config('exams.tapt');
            $params['answerList'] = collect(config('exams.tapt'))->map(function ($category, $c) use ($applicant) {
                $collection = collect($category);
                $keys = array_keys($collection->first());
                return [
                    $keys[0] => $collection->map(fn($item) => $item[$keys[0]])->filter(fn($item, $i) => data_get($applicant->tapt->tapt_ans, "$c.$i", '') == $keys[0])->all(),
                    $keys[1] => $collection->map(fn($item) => $item[$keys[1]])->filter(fn($item, $i) => data_get($applicant->tapt->tapt_ans, "$c.$i", '') == $keys[1])->all(),
                ];
            });
        }

        if ($tab == 'disc' && $applicant?->disc?->disc_ans) {
            $applicant->disc->disc_ans = json_decode($applicant->disc->disc_ans, true);
            $params['answerList'] = config('exams.disc');
            $scores = collect([
                "D" => $applicant->disc->{'_d'},
                "I" => $applicant->disc->{'_i'},
                "S" => $applicant->disc->{'_s'},
                "C" => $applicant->disc->{'_c'}
            ])->sortDesc();
            $counter = 0;
            $prevscore = $scores->values()[0];
            $params['discResult'] = $scores
                ->filter(function ($value) use (&$counter, &$prevscore) {
                    if ($counter < 1 || $value == $prevscore) {
                        $counter++;
                        $prevscore = $value;
                        return true;
                    }
                    return false;
                });
        }

        if ($tab == 'miq' && $applicant?->miq?->miq_ans) {
            $applicant->miq->miq_ans = json_decode($applicant->miq->miq_ans, true);
            $params['answerList'] = config('exams.miq');
            $miqCategory = config('exams.miq_category');
            $scores = collect([
                1 => $applicant->miq->{'_1'},
                2 => $applicant->miq->{'_2'},
                3 => $applicant->miq->{'_3'},
                4 => $applicant->miq->{'_4'},
                5 => $applicant->miq->{'_5'},
                6 => $applicant->miq->{'_6'},
                7 => $applicant->miq->{'_7'},
                8 => $applicant->miq->{'_8'}
            ])->sortDesc();
            $counter = 0;
            $prevscore = $scores->values()[0];
            $params['miqResult'] = $scores
                ->filter(function ($value) use (&$counter, &$prevscore) {
                    if ($counter < 3 || $value == $prevscore) {
                        $counter++;
                        $prevscore = $value;
                        return true;
                    }
                    return false;
                })
                ->mapWithKeys(fn($value, $key) => [$key => $miqCategory[$key]]);
        }

        if ($tab == 'color' && $applicant?->color?->wcay_ans) {
            $applicant->color->wcay_ans = json_decode($applicant->color->wcay_ans, true);
            $params['answerList'] = config('exams.color');
            $colorCategory = config('exams.color_category');
            $scores = collect([
                1 => $applicant->color->{'_1'},
                2 => $applicant->color->{'_2'},
                3 => $applicant->color->{'_3'},
                4 => $applicant->color->{'_4'}
            ])->sortDesc();
            $counter = 0;
            $prevscore = $scores->values()[0];
            $params['colorResult'] = $scores
                ->filter(function ($value) use (&$counter, &$prevscore) {
                    if ($counter < 1 || $value == $prevscore) {
                        $counter++;
                        $prevscore = $value;
                        return true;
                    }
                    return false;
                })
                ->mapWithKeys(fn($value, $key) => [$key => $colorCategory[$key]]);
        }

        if ($tab == 'vak' && $applicant?->vak?->vak_ans) {
            $applicant->vak->vak_ans = json_decode($applicant->vak->vak_ans, true);
            $params['answerList'] = config('exams.vak');
            $vakCategory = config('exams.vak_category');
            $scores = collect([
                'a' => $applicant->vak->{'_a'},
                'b' => $applicant->vak->{'_b'},
                'c' => $applicant->vak->{'_c'}
            ])->sortDesc();
            $counter = 0;
            $prevscore = $scores->values()[0];
            $params['vakResult'] = $scores
                ->filter(function ($value) use (&$counter, &$prevscore) {
                    if ($counter < 1 || $value == $prevscore) {
                        $counter++;
                        $prevscore = $value;
                        return true;
                    }
                    return false;
                })
                ->mapWithKeys(fn($value, $key) => [$key => $vakCategory[$key]]);
        }

        if ($tab == 'why-i-work' && $applicant?->whyIWork) {
            $whyIWorkResult = [
                1 => $applicant->whyIWork->outcome_1,
                2 => $applicant->whyIWork->outcome_2,
                3 => $applicant->whyIWork->outcome_3,
                4 => $applicant->whyIWork->outcome_4,
                5 => $applicant->whyIWork->outcome_5,
                6 => $applicant->whyIWork->outcome_6,
                7 => $applicant->whyIWork->outcome_7,
                8 => $applicant->whyIWork->outcome_8,
                9 => $applicant->whyIWork->outcome_9,
                10 => $applicant->whyIWork->outcome_10,
                11 => $applicant->whyIWork->outcome_11,
                12 => $applicant->whyIWork->outcome_12
            ];

            $params['answerList'] = config('exams.why_i_work');
            $params['whyIWorkResult'] = collect($params['answerList'])->map(function ($item, $key) use ($whyIWorkResult) {
                $item['rank'] = $whyIWorkResult[$key];
                return $item;
            })
                ->sortBy('rank');
            // ->sortBy(fn ($value, $key) => $whyIWorkResult[$key]);
        }

        if ($tab == 'career-anchors' && $applicant?->careerAnchor?->career_ans) {
            $applicant->careerAnchor->career_ans = json_decode($applicant->careerAnchor->career_ans, true);
            $applicant->careerAnchor->career_highest = json_decode($applicant->careerAnchor->career_highest ?? '', true);
            $params['careerAnchorResult']['category'] = collect(config('exams.career_anchors_category'))->mapWithKeys(function ($value, $key) use ($applicant) {
                $data[$key] = collect($applicant?->careerAnchor?->career_ans)->filter(fn($v, $k) => in_array($k, $value));
                return $data;
            });
            $params['answerList'] = config('exams.career_anchors');
            $params['careerAnchorResult']['answer'] = collect($params['answerList'])->map(function ($item, $key) use ($applicant) {
                $data['sequence'] = $key;
                $data['desc'] = $item;
                $data['rate'] = $applicant->careerAnchor->career_ans[$key];
                $data['isHighest'] = !empty($applicant->careerAnchor->career_highest[$key]);
                // $data['category'] = $category->filter(fn ($values) => in_array($key, $values, true))->keys()->first();
                return $data;
            })
                ->sortBy([
                    ['rate', 'desc'],
                    ['sequence', 'asc'],
                ]);
        }

        if ($tab == 'abstract-reasoning' && $applicant?->basicAbstractReasoning?->abstract_ans) {
            $applicant->basicAbstractReasoning->abstract_ans = json_decode($applicant->basicAbstractReasoning->abstract_ans, true);
            $params['answerList'] = config('exams.basic_abstract_reasoning');
            $params['abstractReasoningResult'] = collect($applicant->basicAbstractReasoning->abstract_ans)->filter(fn($value, $key) => $params['answerList'][$key]['answer'] == $value)->count();
        }

        if ($tab == 'basic-math' && $applicant?->basicMath?->math_ans) {
            $applicant->basicMath->math_ans = json_decode($applicant->basicMath->math_ans, true);
            $params['answerList'] = config('exams.basic_math');
            $params['basicMathResult'] = collect($applicant->basicMath->math_ans)->filter(fn($value, $key) => $params['answerList'][$key]['answer'] == $value)->count();
        }

        if ($tab == 'maya' && $applicant?->maya?->maya_ans) {
            $applicant->maya->maya_ans = json_decode($applicant->maya->maya_ans, true);
            $params['answerList'] = config('exams.maya');

            $result = collect($params['answerList'])
                ->map(function ($set, $s) use ($applicant) {
                    return collect($set)->map(function ($item, $i) use ($applicant, $s) {
                        $item['selected'] = $applicant->maya->maya_ans[$s . $i] ?? '';
                        $item['isCorrect'] = $item['selected'] == $item['answer'];
                        return $item;
                    });
                });

            $totalPerSet = $result->map(fn($set) => $set->where('isCorrect', true)->count());

            $params['mayaResult'] = [
                'totalPerSet' => $totalPerSet,
                'totalOverallSet' => $totalPerSet->sum(),
                'percentile' => round((($totalPerSet->sum() / $result->flatMap(fn($set) => $set)->count()) * 100), 2),
                'totalPerDifficulty' => $result->flatMap(fn($set) => $set)
                    ->filter(fn($item) => isset($item['difficulty']))
                    ->groupBy('difficulty')
                    ->map(fn($grp) => $grp->where('isCorrect', true)->count())
            ];
        }

        if ($tab == 'interview-details') {
            $records = InterviewDeets::where('app_id', $id)->get()->keyBy('interview_type');
            $params['interviewDetails'] = [
                'Initial' => $records->get('Initial'),
                '2nd Prelim' => $records->get('2nd Prelim'),
                'Final' => $records->get('Final'),
            ];

            $params['employees'] = DB::connection('hrd2')
                ->table('tbl201_basicinfo')
                ->join('tbl_user2', 'tbl_user2.Emp_No', '=', 'tbl201_basicinfo.bi_empno')
                ->where('tbl_user2.U_Remarks', 'Active')
                ->select('bi_empno', 'bi_empfname', 'bi_empmname', 'bi_emplname')
                ->orderBy('bi_empfname')
                ->orderBy('bi_emplname')
                ->distinct()
                ->get()
                ->map(function ($emp) {
                    $mInitial = $emp->bi_empmname ? substr($emp->bi_empmname, 0, 1) . '.' : '';
                    return [
                        'empno' => $emp->bi_empno,
                        'name'  => trim("{$emp->bi_empfname} {$mInitial} {$emp->bi_emplname}"),
                    ];
                });

            $params['employeeJobMap'] = DB::connection('hrd2')
                ->table('tbl201_jobrec')
                ->select('jrec_empno', 'jrec_company', 'jrec_department')
                ->where('jrec_status', 'Primary')
                ->get()
                ->keyBy('jrec_empno')
                ->map(fn ($job) => [
                    'company'    => $job->jrec_company,
                    'department' => $job->jrec_department,
                ]);

            $params['companyOptions'] = DB::connection('hrd2')
                ->table('tbl201_jobrec')
                ->where('jrec_status', 'Primary')
                ->whereNotNull('jrec_company')->where('jrec_company', '!=', '')
                ->distinct()->orderBy('jrec_company')->pluck('jrec_company');

            $params['departmentOptions'] = DB::connection('hrd2')
                ->table('tbl201_jobrec')
                ->where('jrec_status', 'Primary')
                ->whereNotNull('jrec_department')->where('jrec_department', '!=', '')
                ->distinct()->orderBy('jrec_department')->pluck('jrec_department');
        }

        if (view()->exists("pages.applicant.{$tab}")) {
            $view = "pages.applicant.{$tab}";
        } else {
            abort(404);
            // $view = "pages.applicant.personal";
        }
        return view($view, $params);
    }

    public function saveInterviewDetails(Request $request, $id)
    {
        $validated = $request->validate([
            'interview_type' => 'nullable|string',
            'interview_date' => 'nullable|date',
            'interviewer_empno' => 'nullable|string',
            'interviewer_name' => 'nullable|string',
            'company' => 'nullable|string',
            'department' => 'nullable|string',
            'position' => 'nullable|string',
            'remarks' => 'nullable|string',
            'recommendation' => 'nullable|string',
            'verdict' => 'nullable|string',
        ]);

        InterviewDeets::updateOrCreate(
            ['app_id' => $id, 'interview_type' => $request->interview_type],
            $validated
        );

        return redirect()->back()->with('success', 'Interview details saved successfully.')->with('active_type', $request->interview_type);
    }

    public function showFormHireContent($id)
    {
        return view('pages.applicant.form-hire-content', [
            'applicant' => ApplicantPersonal::find($id),
            'employment_status' => Setting::emplStatusList(),
            'company_list' => Setting::companyList(),
            'department_list' => Setting::departmentList(),
            'position_list' => Setting::positionList(),
            'jobstep_list' => Setting::jobStepList(),
            'jobgrade_list' => Setting::jobGradeList(),
            'section_list' => Setting::sectionList(),
            'area_list' => Setting::areaList(),
            'outlet_list' => Setting::outletList(),
            'report_to' => Employee::employeeList('Active')
        ]);
    }

    public function hire(Request $request, $id)
    {
        $validated = $request->validate(
            [
                'hire-dt' => 'required|date',
                'hire-employment-status' => 'required|string',
                'hire-outlet' => 'required|string',
                'hire-area' => 'required|string',
                'hire-company' => 'required|string',
                'hire-department' => 'required|string',
                'hire-position' => 'required|string',
                'hire-jobstep' => 'required|string',
                'hire-jobgrade' => 'required|string',
                'hire-section' => 'nullable|string',
                'hire-reportto' => 'nullable|string',
                'hire-empno' => 'required|string',
                'hire-username' => 'required|string',
                'hire-pw' => 'required|string',
            ],
            [
                'hire-dt.required' => 'Date Hired is required',
                'hire-employment-status.required' => 'Employement Status is required',
                'hire-outlet.required' => 'Outlet is required',
                'hire-area.required' => 'Area is required',
                'hire-company.required' => 'Company is required',
                'hire-department.required' => 'Department is required',
                'hire-position.required' => 'Position is required',
                'hire-jobstep.required' => 'Job Step is required',
                'hire-jobgrade.required' => 'Job Grade is required',
                'hire-empno.required' => 'Employee No is required',
                'hire-username.required' => 'Username is required',
                'hire-pw.required' => 'Password is required',

                'hire-dt.date' => 'Invalid Input for Date Hired',
                'hire-employment-status.string' => 'Invalid Input for Employement Status',
                'hire-outlet.string' => 'Invalid Input for Outlet',
                'hire-area.string' => 'Invalid Input for Area',
                'hire-company.string' => 'Invalid Input for Company',
                'hire-department.string' => 'Invalid Input for Department',
                'hire-position.string' => 'Invalid Input for Position',
                'hire-jobstep.string' => 'Invalid Input for Job Step',
                'hire-jobgrade.string' => 'Invalid Input for Job Grade',
                'hire-section.string' => 'Invalid Input for Section',
                'hire-reportto.string' => 'Invalid Input for Reports To',
                'hire-empno.string' => 'Invalid Input for Employee No',
                'hire-username.string' => 'Invalid Input for Username',
                'hire-pw.string' => 'Invalid Input for Password',
            ]
        );

        try {

            DB::transaction(function () use ($validated, $id) {
                $exist = DB::table('tbl201_persinfo')->where('pers_empno', $validated['hire-empno'])->exists();
                if ($exist) {
                    return redirect()->back()->withErrors(['error' => 'Employee No already exist']);
                }

                $applicant = ApplicantPersonal::find($id);

                // $applicant?->basicAbstractReasoning
                // $applicant?->basicMath
                // $applicant?->maya

                DB::table('tbl201_persinfo')->insert([
                    // 'pers_id' => 
                    'pers_empno' => $validated['hire-empno'],
                    'pers_lastname' => $applicant?->app_lname,
                    'pers_midname' => $applicant?->app_mname,
                    'pers_firstname' => $applicant?->app_fname,
                    'pers_suffix' => $applicant?->app_suffix,
                    'pers_civilstat' => $applicant?->app_cstatus,
                    'pers_sex' => $applicant?->app_sex,
                    'pers_religion' => $applicant?->app_religion,
                    'pers_birthdate' => $applicant?->app_bdate,
                    'pers_bloodtype' => $applicant?->app_btype,
                    'pers_dialect' => $applicant?->app_dialect,
                    'pers_height' => $applicant?->app_height,
                    'pers_weight' => $applicant?->app_weight
                ]);

                DB::table('tbl201_contact')->insert([
                    'cont_empno' => $validated['hire-empno'],
                    'cont_person_num' => $applicant?->app_mobile,
                    'cont_company_num' => '',
                    'cont_telephone' => $applicant?->app_telephone,
                    'cont_email' => $applicant?->app_email,
                    'cont_status' => 1
                ]);

                DB::table('tbl201_address')->insert([
                    'add_empno' => $validated['hire-empno'],
                    'add_perm_prov' => $applicant?->address?->add_perm_prov,
                    'add_perm_city' => $applicant?->address?->add_perm_city,
                    'add_perm_brngy' => $applicant?->address?->add_perm_brngy,
                    'add_cur_prov' => $applicant?->address?->add_cur_prov,
                    'add_cur_city' => $applicant?->address?->add_cur_city,
                    'add_cur_brngy' => $applicant?->address?->add_cur_brngy,
                    'add_birth_prov' => $applicant?->address?->add_birth_prov,
                    'add_birth_city' => $applicant?->address?->add_birth_city,
                    'add_birth_brngy' => $applicant?->address?->add_birth_brngy,
                    'add_perm_location' => $applicant?->address?->add_perm_location,
                    'add_cur_location' => $applicant?->address?->add_cur_location,
                    'add_birth_location' => $applicant?->address?->add_birth_location,
                    'add_status' => 1
                ]);

                DB::table('tbl201_gov_req')->insert([
                    'gov_empno' => $validated['hire-empno'],
                    'gov_sss' => $applicant?->app_sss,
                    'gov_pagibig' => $applicant?->app_pagibig,
                    'gov_philhealth' => $applicant?->app_philhealth,
                    'gov_tin' => $applicant?->app_tin,
                    'gov_status' => 1
                ]);

                DB::table('tbl201_jobinfo')->insert([
                    'ji_empno' => $validated['hire-empno'],
                    'ji_datehired' => $validated['hire-dt'],
                    'ji_remarks' => 'Active',
                ]);

                DB::table('tbl201_jobrec')->insert([
                    'jrec_empno' => $validated['hire-empno'],
                    'jrec_company' => $validated['hire-company'],
                    'jrec_department' => $validated['hire-department'],
                    'jrec_section' => $validated['hire-section'],
                    'jrec_area' => $validated['hire-area'],
                    'jrec_outlet' => $validated['hire-outlet'],
                    'jrec_position' => $validated['hire-position'],
                    'jrec_jobgrade' => $validated['hire-jobgrade'],
                    'jrec_step' => $validated['hire-jobstep'],
                    'jrec_effectdate' => $validated['hire-dt'],
                    'jrec_reportto' => $validated['hire-reportto'],
                    'jrec_status' => 'Primary',
                    'jrec_timestamp' => now(),
                    'jrec_sharedservice' => ($validated['hire-company'] == 'TNGC' ? 1 : 0),
                    'jrec_type' => 'Primary'
                ]);

                DB::table('tbl201_emplstatus')->insert([
                    'estat_empno' => $validated['hire-empno'],
                    'estat_effectdate' => $validated['hire-dt'],
                    'estat_empstat' => $validated['hire-employment-status'],
                    'estat_stat' => 'Active',
                    'estat_timestamp' => now()
                ]);

                $insert_rec = $applicant?->family->map(function ($item) use ($validated) {
                    return [
                        'fam_empno' => $validated['hire-empno'],
                        'fam_relationship' => $item->fam_relationship,
                        'fam_firstname' => $item->fam_firstname,
                        'fam_midname' => $item->fam_midname,
                        'fam_lastname' => $item->fam_lastname,
                        'fam_suffix' => $item->fam_suffix,
                        'fam_maidenname' => $item->fam_maidenname ?? '',
                        'fam_birthdate' => $item->fam_birthdate,
                        'fam_sex' => $item->fam_sex,
                        'fam_contact' => $item->fam_contact,
                        'fam_add' => $item->fam_add,
                        'fam_occupation' => $item->fam_occupation,
                        'fam_workplace' => $item->fam_workplace
                    ];
                })->toArray();

                DB::table('tbl201_family')->insert($insert_rec);

                $insert_rec = $applicant?->education->map(function ($item) use ($validated) {
                    return [
                        'educ_empno' => $validated['hire-empno'],
                        'educ_level' => $item->edu_level,
                        'educ_degreetitle' => $item->edu_degreetitle,
                        'educ_major' => $item->edu_major,
                        'educ_school' => $item->edu_school,
                        'educ_schooladd' => $item->edu_schooladd,
                        'educ_yeargrad' => $item->edu_yeargrad,
                        'educ_currStatus' => $item->edu_status,
                        'status' => 1
                    ];
                })->toArray();

                DB::table('tbl201_education')->insert($insert_rec);

                $insert_rec = $applicant?->skill->map(function ($item) use ($validated) {
                    return [
                        'skill_empno' => $validated['hire-empno'],
                        'skill_category' => $item->skill_category,
                        'skill_type' => $item->skill_category,
                        'skill_others' => $item->skill_others,
                        'status' => 1
                    ];
                })->toArray();

                DB::table('tbl201_skills')->insert($insert_rec);

                $insert_rec = $applicant?->license->map(function ($item) use ($validated) {
                    return [
                        'el_empno' => $validated['hire-empno'],
                        'el_type' => $item->el_type,
                        'el_profession' => $item->el_profession,
                        'el_regdate' => $item->el_regdate,
                        'el_expdate' => $item->el_expdate,
                        'el_file' => $item->el_file,
                    ];
                })->toArray();

                DB::table('tbl201_eligibility')->insert($insert_rec);

                $insert_rec = $applicant?->certificate->map(function ($item) use ($validated) {
                    return [
                        'cert_empno' => $validated['hire-empno'],
                        'cert_title' => $item->cert_title,
                        'cert_address' => $item->cert_address,
                        'cert_date' => $item->cert_date,
                        'cert_speaker' => $item->cert_speaker,
                        'cert_file' => $item->cert_file,
                    ];
                })->toArray();

                DB::table('tbl201_certificate')->insert($insert_rec);

                $insert_rec = $applicant?->employmentRec->map(function ($item) use ($validated) {
                    return [
                        'empl_empno' => $validated['hire-empno'],
                        'empl_from' => $item->empl_from,
                        'empl_to' => $item->empl_to,
                        'empl_company' => $item->empl_company,
                        'empl_address' => $item->empl_address,
                        'empl_position' => $item->empl_position,
                        'empl_contact' => $item->empl_contact,
                        'empl_supervisor' => $item->empl_supervisor,
                        'empl_reason' => $item->empl_reason,
                        'empl_timestamp' => now(),
                    ];
                })->toArray();

                DB::table('tbl201_employment')->insert($insert_rec);

                $insert_rec = $applicant?->characterRef->map(function ($item) use ($validated) {
                    return [
                        'ref_empno' => $validated['hire-empno'],
                        'ref_fullname' => $item->ref_fullname,
                        'ref_position' => $item->ref_position,
                        'ref_company' => $item->ref_company,
                        'ref_address' => $item->ref_address,
                        'ref_contact' => $item->ref_contact,
                        'ref_relationship' => $item->ref_relationship,
                        'ref_timestamp' => now()
                    ];
                })->toArray();

                DB::table('tbl201_reference')->insert($insert_rec);

                $insert_rec = $applicant?->enneagram;

                DB::table('tbl201_enneagramtest')->insert([
                    'enneagram_empno' => $validated['hire-empno'],
                    '1_perfectionist' => $insert_rec?->{'1_perfectionist'},
                    '2_helper' => $insert_rec?->{'2_helper'},
                    '3_achiever' => $insert_rec?->{'3_achiever'},
                    '4_romantic' => $insert_rec?->{'4_romantic'},
                    '5_observer' => $insert_rec?->{'5_observer'},
                    '6_questioner' => $insert_rec?->{'6_questioner'},
                    '7_adventurer' => $insert_rec?->{'7_adventurer'},
                    '8_asserter' => $insert_rec?->{'8_asserter'},
                    '9_peacemaker' => $insert_rec?->{'9_peacemaker'},
                    'enneagram_ans' => $insert_rec?->{'enneagram_ans'},
                    'enneagram_dt' => $insert_rec?->{'enneagram_dt'}
                ]);

                $insert_rec = $applicant?->tapt;

                DB::table('tbl201_tapt')->insert([
                    'tapt_empno' => $validated['hire-empno'],
                    'e_i' => $insert_rec?->{'e_i'},
                    's_n' => $insert_rec?->{'s_n'},
                    't_f' => $insert_rec?->{'t_f'},
                    'j_p' => $insert_rec?->{'j_p'},
                    'tapt_ans' => $insert_rec?->{'tapt_ans'},
                    'tapt_dt' => $insert_rec?->{'tapt_dt'}
                ]);

                $insert_rec = $applicant?->disc;

                DB::table('tbl201_disc')->insert([
                    'disc_empno' => $validated['hire-empno'],
                    '_d' => $insert_rec?->{'_d'},
                    '_i' => $insert_rec?->{'_i'},
                    '_s' => $insert_rec?->{'_s'},
                    '_c' => $insert_rec?->{'_c'},
                    'disc_ans' => $insert_rec?->{'disc_ans'},
                    'disc_dt' => $insert_rec?->{'disc_dt'}
                ]);

                $insert_rec = $applicant?->miq;

                DB::table('tbl201_miq')->insert([
                    'miq_empno' => $validated['hire-empno'],
                    'miq_ans' => $insert_rec?->{'miq_ans'},
                    'miq_dt' => $insert_rec?->{'miq_dt'}
                ]);

                $insert_rec = $applicant?->color;

                DB::table('tbl201_whatcolorareyou')->insert([
                    'wcay_empno' => $validated['hire-empno'],
                    '_1' => $insert_rec?->{'_1'},
                    '_2' => $insert_rec?->{'_2'},
                    '_3' => $insert_rec?->{'_3'},
                    '_4' => $insert_rec?->{'_4'},
                    'wcay_ans' => $insert_rec?->{'wcay_ans'},
                    'wcay_dt' => $insert_rec?->{'wcay_dt'}
                ]);

                $insert_rec = $applicant?->vak;

                DB::table('tbl201_vak')->insert([
                    'vak_empno' => $validated['hire-empno'],
                    '_a' => $insert_rec?->{'_a'},
                    '_b' => $insert_rec?->{'_b'},
                    '_c' => $insert_rec?->{'_c'},
                    'vak_ans' => $insert_rec?->{'vak_ans'},
                    'vak_dt' => $insert_rec?->{'vak_dt'}
                ]);

                $insert_rec = $applicant?->whyIWork;

                DB::table('tbl201_whyiwork')->insert([
                    'wiw_empno' => $validated['hire-empno'],
                    'outcome_1' => $insert_rec?->{'outcome_1'},
                    'outcome_2' => $insert_rec?->{'outcome_2'},
                    'outcome_3' => $insert_rec?->{'outcome_3'},
                    'outcome_4' => $insert_rec?->{'outcome_4'},
                    'outcome_5' => $insert_rec?->{'outcome_5'},
                    'outcome_6' => $insert_rec?->{'outcome_6'},
                    'outcome_7' => $insert_rec?->{'outcome_7'},
                    'outcome_8' => $insert_rec?->{'outcome_8'},
                    'outcome_9' => $insert_rec?->{'outcome_9'},
                    'outcome_10' => $insert_rec?->{'outcome_10'},
                    'outcome_11' => $insert_rec?->{'outcome_11'},
                    'outcome_12' => $insert_rec?->{'outcome_12'},
                    'wiw_dt' => $insert_rec?->{'wiw_dt'}
                ]);

                $insert_rec = $applicant?->careerAnchor;

                DB::table('tbl201_careeranchors')->insert([
                    'career_empno' => $validated['hire-empno'],
                    'career_ans' => $insert_rec?->{'career_ans'},
                    'career_highest' => $insert_rec?->{'career_highest'},
                    'career_dt' => $insert_rec?->{'career_dt'}
                ]);

                User::create([
                    'Emp_No' => $validated['hire-empno'],
                    'U_Name' => $validated['hire-username'],
                    'U_Password' => $validated['hire-pw'],
                    'U_Remarks' => 'Active',
                    'U_timestamp' => now(),
                    'U_Password_hashed' => $validated['hire-pw'],
                ]);

                Setting::saveAccessUserWithGroup($validated['hire-empno'], 'Employee');

                // throw new \Exception('Cancel transaction');
            });

            return redirect("/employee/profile/personal/{$validated['hire-empno']}")->with('success', 'Employee created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to process employee data: ' . $e->getMessage()]);
        }
    }
}
