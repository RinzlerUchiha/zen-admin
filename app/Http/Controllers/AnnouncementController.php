<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public static function index($type = 'company')
    {
        $user = Auth::user();
        $emplist = DB::table('tbl201_persinfo')
            ->join('tbl201_jobinfo', 'ji_empno', '=', 'pers_empno')
            ->leftJoin('tbl201_jobrec', function ($join) {
                $join->on('jrec_empno', '=', 'pers_empno')
                    ->on('jrec_status', '=', DB::raw("'Primary'"));
            })
            ->leftJoin('tbl_company', 'C_Code', '=', 'jrec_company')
            ->leftJoin('tbl_department', 'Dept_Code', '=', 'jrec_department')
            ->leftJoin('tbl_jobdescription', 'jd_code', '=', 'jrec_position')
            ->orderBy('C_Name', 'asc')
            ->orderBy('Dept_Name', 'asc')
            ->orderBy('pers_lastname', 'asc')
            ->orderBy('pers_firstname', 'asc')
            ->get();

        $filter = $emplist->where('pers_empno', '=', $user->Emp_No)->first();
        $empname = $filter ? ucwords(trim($filter->pers_lastname . ', ' . $filter->pers_firstname)) : '';

        $emoji['faces'] = [
            '&#128512;',
            '&#128513;',
            '&#128514;',
            '&#128515;',
            '&#128516;',
            '&#128517;',
            '&#128518;',
            '&#128519;',
            '&#129392;',
            '&#129297;',
            '&#129303;',
            '&#129312;',
            '&#129319;',
            '&#129321;',
            '&#129395;',
            '&#129392;',
            '&#129327;',
            '&#128520;',
            '&#128521;',
            '&#128522;',
            '&#128523;',
            '&#128524;',
            '&#128525;',
            '&#128526;',
            '&#128527;',
            '&#128528;',
            '&#128529;',
            '&#128530;',
            '&#128531;',
            '&#128532;',
            '&#128533;',
            '&#128534;',
            '&#128535;',
            '&#128536;',
            '&#128537;',
            '&#128538;',
            '&#128539;',
            '&#128540;',
            '&#128541;',
            '&#128542;',
            '&#128543;',
            '&#128544;',
            '&#128545;',
            '&#128546;',
            '&#128547;',
            '&#128548;',
            '&#128549;',
            '&#128550;',
            '&#128551;',
            '&#128552;',
            '&#128553;',
            '&#128554;',
            '&#128555;',
            '&#128556;',
            '&#128557;',
            '&#128558;',
            '&#128559;',
            '&#128560;',
            '&#128561;',
            '&#128562;',
            '&#128563;',
            '&#128564;',
            '&#128565;',
            '&#128566;',
            '&#128567;',
            '&#129305;',
            '&#129310;',
            '&#128079;',
            '&#128133;',
            '&#129309;',
            '&#9996;',
            '&#128077;',
            '&#128400;'
        ];

        $emoji['heart'] = [
            '&#10084;',
            '&#128140;',
            '&#10083;',
            '&#128147;',
            '&#128148;',
            '&#128149;',
            '&#128150;',
            '&#128151;',
            '&#128152;',
            '&#128153;',
            '&#128154;',
            '&#128155;',
            '&#128156;',
            '&#128157;',
            '&#128158;',
            '&#128159;',
            '&#128420;',
            '&#129293;',
            '&#129294;'
        ];

        $emoji['food'] = [
            '&#127838;',
            '&#129360;',
            '&#129366;',
            '&#129391;',
            '&#129374;',
            '&#129479;',
            '&#129472;',
            '&#127830;',
            '&#127831;',
            '&#129385;',
            '&#127828;',
            '&#127839;',
            '&#127789;',
            '&#127829;',
            '&#129386;',
            '&#129747;',
            '&#127790;',
            '&#127791;',
            '&#129372;',
            '&#129478;',
            '&#127837;',
            '&#127836;',
            '&#127829;',
            '&#129368;',
            '&#129367;',
            '&#127835;',
            '&#127834;',
            '&#127843;',
            '&#127844;',
            '&#127845;',
            '&#129382;',
            '&#129748;',
            '&#127846;',
            '&#127847;',
            '&#127848;',
            '&#127849;',
            '&#127850;',
            '&#127874;',
            '&#127856;',
            '&#129473;',
            '&#129383;',
            '&#127851;',
            '&#127852;',
            '&#127853;',
            '&#127854;',
            '&#127855;',
            '&#128006;',
            '&#9749;',
            '&#129749;',
            '&#127861;',
            '&#127862;',
            '&#127867;',
            '&#127863;',
            '&#127864;',
            '&#127865;',
            '&#127866;',
            '&#127867;',
            '&#129380;',
            '&#129749;',
            '&#127860;',
            '&#129379;',
            '&#127869;',
            '&#129475;',
        ];

        $emoji['plant'] = [
            '&#127793;',
            '&#127794;',
            '&#127795;',
            '&#127796;',
            '&#127797;',
            '&#127806;',
            '&#127807;',
            '&#9752;',
            '&#127808;',
            '&#127809;',
            '&#127810;',
            '&#127811;',
            '&#127799;',
            '&#127800;',
            '&#127801;',
            '&#129344;',
            '&#127802;',
            '&#127803;',
            '&#127804;',
            '&#127806;',
            '&#127805;',
            '&#127807;',
            '&#127812;',
            '&#127883;',
            '&#127885;',
            '&#127815;',
            '&#127816;',
            '&#127817;',
            '&#127818;',
            '&#127819;',
            '&#127820;',
            '&#127821;',
            '&#129389;',
            '&#127822;',
            '&#127823;',
            '&#127824;',
            '&#127825;',
            '&#127826;',
            '&#127827;',
            '&#129744;',
            '&#129373;',
            '&#127813;',
            '&#129381;',
            '&#129361;',
            '&#127814;',
            '&#129364;',
            '&#129365;',
            '&#127805;',
            '&#129362;',
            '&#129388;',
            '&#129382;',
            '&#129476;',
            '&#129477;',
            '&#127812;',
            '&#129745;'
        ];

        $emoji['weather'] = [
            '&#9728;',
            '&#127774;',
            '&#9925;',
            '&#127775;',
            '&#127776;',
            '&#127777;',
            '&#127778;', 
            // '&#128167;',
            '&#127779;',
            '&#9928;',
            '&#127786;',
            '&#127787;',
            '&#127788;',
            '&#10052;',
            '&#9731;',
            '&#127777;',
            '&#127752;',
            '&#9889;',
            '&#127746;',
            '&#9730;',
            '&#128168;',
            '&#127756;',
            '&#127775;',
            '&#127769;',
            '&#127762;',
            '&#127761;',
            '&#11088;',
            '&#9732;',
            '&#127765;',
            '&#127766;',
            '&#127767;',
            '&#127768;',
            '&#127763;',
            '&#127764;',
            '&#127757;',
            '&#127758;',
            '&#127759;',
            '&#129680;'
        ];

        $emoji['symbols'] = [
            '&#127881;',
            '&#127882;',
            '&#129395;',
            '&#127880;',
            '&#127874;',
            '&#127873;',
            '&#129665;',
            '&#129681;',
            '&#127879;',
            '&#127878;',
            '&#129512;',
            '&#10024;',
            '&#127775;',
            '&#128171;',
            '&#127925;',
            '&#127926;',
            '&#127908;',
            '&#127911;',
            '&#129668;',
            '&#127942;',
            '&#127935;',
            '&#129351;',
            '&#129352;',
            '&#129353;',
            '&#10013;',
            '&#9770;',
            '&#9784;',
            '&#9775;',
            '&#10017;',
            '&#128303;',
            '&#128329;',
            '&#128720;',
            '&#128334;',
            '&#9774;',
            '&#129418;',
            '&#9851;',
            '&#9884;',
            '&#9888;',
            '&#128696;',
            '&#9940;',
            '&#128683;',
            '&#10060;',
            '&#10004;',
            '&#128308;',
            '&#128309;',
            '&#9898;',
            '&#9899;',
            '&#128312;',
            '&#128311;'
        ];

        return view('pages.announcement', [
            'main_link' => 'announcement',
            'sub_link' => '',
            'companyList' => Setting::companyList()->mapWithKeys(fn($c) => [$c->C_Code => $c]),
            'empname' => $empname,
            'user_empno' => $user->Emp_No,
            'emplist' => $emplist,
            'emoji' => $emoji,
            'maincat' => $type,
            'type' => $type
        ]);
    }

    public static function showList($type, $offset)
    {
        $limit = 5;
        $user = Auth::user();
        $employees = DB::table('tbl201_persinfo')
            ->join('tbl201_jobinfo', 'ji_empno', '=', 'pers_empno')
            ->leftJoin('tbl201_jobrec', function ($join) {
                $join->on('jrec_empno', '=', 'pers_empno')
                    ->on('jrec_status', '=', DB::raw("'Primary'"));
            })
            ->leftJoin('tbl_company', 'C_Code', '=', 'jrec_company')
            ->leftJoin('tbl_department', 'Dept_Code', '=', 'jrec_department')
            ->leftJoin('tbl_jobdescription', 'jd_code', '=', 'jrec_position')
            ->orderBy('C_Name', 'asc')
            ->orderBy('Dept_Name', 'asc')
            ->orderBy('pers_lastname', 'asc')
            ->orderBy('pers_firstname', 'asc')
            ->get();

        $filter = $employees->where('pers_empno', '=', $user->Emp_No)->first();
        $empname = $filter ? ucwords(trim($filter->pers_lastname . ', ' . $filter->pers_firstname)) : '';

        if($type == 'gov'){
            $posts = DB::table('tbl_gov_announcement')
            ->where('gov_start', '<=', date('Y-m-d'))
            ->where(function($query){
                 $query->where('gov_end', '>=', date('Y-m-d'))
                    ->orWhere('gov_end', '')
                    ->orWhereNull('gov_end');
             })
            ->orderBy('gov_timestamp', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(function($p) use($employees){
                $p->empinfo = $employees->where('pers_empno', $p->gov_postby)->first();
                $p->gov_img = explode(',', str_replace('assets/announcement/', '', $p->gov_img));
                $p->pic = $p->gov_postby;
                return $p;
            });
        }else{
            $posts = Announcement::leftJoin('tbl_reaction', function($j) use($user){
                $j->on('post_id', '=', 'ann_id')
                    ->where('reaction_by', $user->Emp_No);
            })
            ->whereIn('ann_status', ['Approved', 'Reported'])
            ->where(function($query) use($filter){
                 $query->where('ann_receiver', 'All')
                    ->orWhere('ann_receiver', 'Only Me')
                    ->orWhereRaw('ann_receiver LIKE ?', [$filter ? '%'.$filter->jrec_company.'%' : '']);
             })
            ->orderBy('ann_timestatmp', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(function($p) use($employees){
                $p->empinfo = $employees->where('pers_empno', $p->ann_approvedby)->first();
                $p->reportby_info = $employees->where('pers_empno', $p->ann_reportby)->first();
                $p->ann_content = array_filter(explode(',', str_replace('assets/announcement/', '', $p->ann_content)));

                $p->pic = $p->ann_approvedby;

                $p->reportby_pic = $p->ann_reportby;
                return $p;
            });

            $reactions = DB::table('tbl_reaction')
            ->whereNot('reaction_type', '')
            ->whereIn('post_id', $posts->pluck('post_id'))
            // ->selectRaw('post_id, GROUP_CONCAT(DISTINCT reaction_type) AS reaction, COUNT(reaction_type) AS cnt')
            // ->groupBy('post_id')
            ->get();

            $posts = $posts->map(function($p) use($reactions){
                $p->reactionList = $reactions->where('post_id', $p->ann_id);
                return $p;
            });
        }

        $comments = DB::table('tbl_post_comment')
        ->whereIn('com_post_id', $posts->pluck('ann_id'))
        ->where('com_status', 1)
        // ->orderByDesc('com_date')
        ->get()
        ->map(function($c) use($employees){
            $c->age_string = Carbon::parse($c->com_date)->diffForHumans();
            $c->empinfo = $employees->where('pers_empno', $c->com_post_by)->first();
            $c->pic = $c->com_post_by;
            return $c;
        });

        $emoji['faces'] = ['&#128512;', '&#128513;', '&#128514;', '&#128515;', '&#128516;', '&#128517;', '&#128518;', '&#128519;', '&#129392;', '&#129297;', '&#129303;', '&#129312;', '&#129319;', '&#129321;', '&#129395;', '&#129392;', '&#129327;', '&#128520;', '&#128521;', '&#128522;', '&#128523;', '&#128524;', '&#128525;', '&#128526;', '&#128527;', '&#128528;', '&#128529;', '&#128530;', '&#128531;', '&#128532;', '&#128533;', '&#128534;', '&#128535;', '&#128536;', '&#128537;', '&#128538;', '&#128539;', '&#128540;', '&#128541;', '&#128542;', '&#128543;', '&#128544;', '&#128545;', '&#128546;', '&#128547;', '&#128548;', '&#128549;', '&#128550;', '&#128551;', '&#128552;', '&#128553;', '&#128554;', '&#128555;', '&#128556;', '&#128557;', '&#128558;', '&#128559;', '&#128560;', '&#128561;', '&#128562;', '&#128563;', '&#128564;', '&#128565;', '&#128566;', '&#128567;', '&#129305;', '&#129310;', '&#128079;', '&#128133;', '&#129309;', '&#9996;', '&#128077;', '&#128400;'];

        $emoji['heart'] = ['&#10084;', '&#128140;', '&#10083;', '&#128147;', '&#128148;', '&#128149;', '&#128150;', '&#128151;', '&#128152;', '&#128153;', '&#128154;', '&#128155;', '&#128156;', '&#128157;', '&#128158;', '&#128159;', '&#128420;', '&#129293;', '&#129294;'];

        $emoji['food'] = [
            '&#127838;',
            '&#129360;',
            '&#129366;',
            '&#129391;',
            '&#129374;',
            '&#129479;',
            '&#129472;',
            '&#127830;',
            '&#127831;',
            '&#129385;',
            '&#127828;',
            '&#127839;',
            '&#127789;',
            '&#127829;',
            '&#129386;',
            '&#129747;',
            '&#127790;',
            '&#127791;',
            '&#129372;',
            '&#129478;',
            '&#127837;',
            '&#127836;',
            '&#127829;',
            '&#129368;',
            '&#129367;',
            '&#127835;',
            '&#127834;',
            '&#127843;',
            '&#127844;',
            '&#127845;',
            '&#129382;',
            '&#129748;',
            '&#127846;',
            '&#127847;',
            '&#127848;',
            '&#127849;',
            '&#127850;',
            '&#127874;',
            '&#127856;',
            '&#129473;',
            '&#129383;',
            '&#127851;',
            '&#127852;',
            '&#127853;',
            '&#127854;',
            '&#127855;',
            '&#128006;',
            '&#9749;',
            '&#129749;',
            '&#127861;',
            '&#127862;',
            '&#127867;',
            '&#127863;',
            '&#127864;',
            '&#127865;',
            '&#127866;',
            '&#127867;',
            '&#129380;',
            '&#129749;',
            '&#127860;',
            '&#129379;',
            '&#127869;',
            '&#129475;',
        ];

        $emoji['plant'] = [
            '&#127793;',
            '&#127794;',
            '&#127795;',
            '&#127796;',
            '&#127797;',
            '&#127806;',
            '&#127807;',
            '&#9752;',
            '&#127808;',
            '&#127809;',
            '&#127810;',
            '&#127811;',
            '&#127799;',
            '&#127800;',
            '&#127801;',
            '&#129344;',
            '&#127802;',
            '&#127803;',
            '&#127804;',
            '&#127806;',
            '&#127805;',
            '&#127807;',
            '&#127812;',
            '&#127883;',
            '&#127885;',
            '&#127815;',
            '&#127816;',
            '&#127817;',
            '&#127818;',
            '&#127819;',
            '&#127820;',
            '&#127821;',
            '&#129389;',
            '&#127822;',
            '&#127823;',
            '&#127824;',
            '&#127825;',
            '&#127826;',
            '&#127827;',
            '&#129744;',
            '&#129373;',
            '&#127813;',
            '&#129381;',
            '&#129361;',
            '&#127814;',
            '&#129364;',
            '&#129365;',
            '&#127805;',
            '&#129362;',
            '&#129388;',
            '&#129382;',
            '&#129476;',
            '&#129477;',
            '&#127812;',
            '&#129745;'
        ];

        $emoji['weather'] = [
            '&#9728;',
            '&#127774;',
            '&#9925;',
            '&#127775;',
            '&#127776;',
            '&#127777;',
            '&#127778;', 
            // '&#128167;',
            '&#127779;',
            '&#9928;',
            '&#127786;',
            '&#127787;',
            '&#127788;',
            '&#10052;',
            '&#9731;',
            '&#127777;',
            '&#127752;',
            '&#9889;',
            '&#127746;',
            '&#9730;',
            '&#128168;',
            '&#127756;',
            '&#127775;',
            '&#127769;',
            '&#127762;',
            '&#127761;',
            '&#11088;',
            '&#9732;',
            '&#127765;',
            '&#127766;',
            '&#127767;',
            '&#127768;',
            '&#127763;',
            '&#127764;',
            '&#127757;',
            '&#127758;',
            '&#127759;',
            '&#129680;'
        ];

        $emoji['symbols'] = [
            '&#127881;',
            '&#127882;',
            '&#129395;',
            '&#127880;',
            '&#127874;',
            '&#127873;',
            '&#129665;',
            '&#129681;',
            '&#127879;',
            '&#127878;',
            '&#129512;',
            '&#10024;',
            '&#127775;',
            '&#128171;',
            '&#127925;',
            '&#127926;',
            '&#127908;',
            '&#127911;',
            '&#129668;',
            '&#127942;',
            '&#127935;',
            '&#129351;',
            '&#129352;',
            '&#129353;',
            '&#10013;',
            '&#9770;',
            '&#9784;',
            '&#9775;',
            '&#10017;',
            '&#128303;',
            '&#128329;',
            '&#128720;',
            '&#128334;',
            '&#9774;',
            '&#129418;',
            '&#9851;',
            '&#9884;',
            '&#9888;',
            '&#128696;',
            '&#9940;',
            '&#128683;',
            '&#10060;',
            '&#10004;',
            '&#128308;',
            '&#128309;',
            '&#9898;',
            '&#9899;',
            '&#128312;',
            '&#128311;'
        ];

        return view('pages.announcement.announcement-post', [
            'empname' => $empname,
            'posts' => $posts,
            'comments' => $comments,
            'user_empno' => $user->Emp_No,
            'employees' => $employees,
            'emoji' => $emoji,
            'type' => $type,

        ]);
    }

    public static function store(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'description' => 'required|string',
                'audience' => 'nullable|string',
                'type' => 'required|string',
                'post-on' => 'nullable|string',
                'post-date' => 'nullable|date',
                'post-end-date' => 'nullable|date',
                'files.*' => 'nullable|file|mimetypes:image/*'
                // 'files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf'
                // 'files.*' => 'nullable|image'
            ]);

            if ($request->hasFile('files')) {
                $uploadedFiles = $request->file('files');
                $validated['filenames'] = [];
                foreach ($uploadedFiles as $file) {
                    $fileName = time() . '_' . str_replace(',', ' ', $file->getClientOriginalName());
                    // $file->storeAs('announcements', $fileName, 's3');
                    $fileName = reduceImageFileSizeToWebP(
                        's3',
                        $file->getRealPath(), 
                        1000, 
                        'announcements/'.$fileName
                    );
                    // $file->move($_SERVER['DOCUMENT_ROOT'].'/zen/assets/announcement', $fileName);
                    // $validated['filenames'][] = 'assets/announcement/'.$fileName;
                    $validated['filenames'][] = basename($fileName);
                }
            }

            if($validated['type'] == 'gov'){
                DB::table('tbl_gov_announcement')
                ->insert([
                    'gov_title' => $validated['description'],
                    'gov_start' => $validated['post-date'],
                    'gov_end' => $validated['post-end-date'],
                    'gov_img' => implode(',', $validated['filenames']),
                    'gov_postby' => Auth::user()->Emp_No
                ]);
            }else{
                Announcement::insert([
                    'ann_title' => $validated['description'],
                    'ann_content' => implode(',', $validated['filenames']),
                    'ann_receiver' => $validated['audience'],
                    'ann_approvedby' => Auth::user()->Emp_No,
                    'ann_status' => 'Approved',
                    'ann_timestatmp' => $validated['post-on'] == 'now' ? now() : $validated['post-date']
                ]);
            }

            return response()->json(['success' => true, 'data' => $validated]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function storeComment(Request $request)
    {
        try {
            $user = Auth::user();

            // Validate the form data
            $validated = $request->validate([
                'post' => 'required|string',
                'comment' => 'required|string'
            ]);

            DB::table('tbl_post_comment')->insert([
                'com_post_id' => $validated['post'],
                'com_content' => $validated['comment'],
                'com_post_by' => $user->Emp_No,
                'com_status' => 1
            ]);

            return response()->json(['success' => true, 'name' => Auth::user()->FirstLastName]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function denyReport($id)
    {
        try {

            Announcement::where('ann_id', $id)->update(['ann_status' => 'Approved']);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function delete($id, $type = 'company')
    {
        try {
            if($type == 'gov'){
                DB::table('tbl_gov_announcement')->where('gov_id', $id)->delete();
            }else{
                Announcement::where('ann_id', $id)->update(['ann_status' => 'Removed']);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function storeReaction(Request $request)
    {
        try {
            $user = Auth::user();

            // Validate the form data
            $validated = $request->validate([
                'post' => 'required|integer',
                'reaction' => 'nullable|string'
            ]);

            if(DB::table('tbl_reaction')
                ->where([
                    ['post_id', $validated['post']],
                    ['reaction_by', $user->Emp_No]
                ])
                ->count()){
                
                DB::table('tbl_reaction')
                ->where([
                    ['post_id', $validated['post']],
                    ['reaction_by', $user->Emp_No]
                ])
                ->update(['reaction_type' => $validated['reaction']]);
            }else{
                DB::table('tbl_reaction')
                ->insert([
                    'post_id' => $validated['post'],
                    'reaction_by' => $user->Emp_No,
                    'reaction_type' => $validated['reaction']
                ]);
            }

            $reactions = DB::table('tbl_reaction')
                ->whereNot('reaction_type', '')
                ->where('post_id', $validated['post'])
                ->get();

            return response()->json([
                'success' => true, 
                'reactions' => $reactions->pluck('reaction_type')->unique(),
                'reaction_cnt' => $reactions->count()
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }
}
