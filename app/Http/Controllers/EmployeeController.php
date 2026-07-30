<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Employee;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;

class EmployeeController extends Controller
{
    /**
     * Maps "maincat/subcat" to the static method that loads its data.
     * Replaces the previous 20-branch if/elseif chain in showInfo().
     * Add a new tab by adding one line here.
     */
    protected static function infoResolvers(): array
    {
        return [
            'profile/personal'        => [self::class, 'personalInfo'],
            'profile/family'          => [self::class, 'familyInfo'],
            'profile/skills'          => [self::class, 'skillsInfo'],
            'profile/education'       => [self::class, 'educationInfo'],
            'professional/license'    => [self::class, 'licenseInfo'],
            'professional/certificate'=> [self::class, 'eduCertificateInfo'],
            'work/job'                => [self::class, 'jobInfo'],
            'work/employment'         => [self::class, 'employmentInfo'],
            'work/certificate'        => [self::class, 'workCertificateInfo'],
            'work/payslip'            => [self::class, 'payslipInfo'],
            'work/contracts'          => [self::class, 'contractsInfo'],
            'work/characterref'       => [self::class, 'characterrefInfo'],
            'personality/enneagram'   => [self::class, 'formattedEnneagramInfo'],
            'personality/tapt'        => [self::class, 'formattedTaptInfo'],
            'personality/disc'        => [self::class, 'formattedDiscInfo'],
            'personality/miq'         => [self::class, 'formattedMiqInfo'],
            'personality/color'       => [self::class, 'formattedColorInfo'],
            'personality/vak'         => [self::class, 'formattedVakInfo'],
        ];
    }

    // Show details of a single employee
    public function showInfo($maincat = 'profile', $subcat = 'personal', $empno = '')
    {
        if ($empno == '') {
            $empno = Auth::user()?->Emp_No;
        }
        // *** TO DO: add employee not found for invalid employee number ***
        $main_link = 'employee';
        $sub_link = 'employee';
        $page = 'pages.employee.' . $maincat . '.' . $subcat;
        $employeeList = Employee::employeeList();

        $resolver = self::infoResolvers()["{$maincat}/{$subcat}"] ?? null;
        $empData = $resolver ? call_user_func($resolver, $empno) : [];

        $return = ['employeeList', 'empData', 'empno', 'main_link', 'sub_link', 'maincat', 'subcat', 'page'];
        if ($subcat == 'personal') {
            $provinceList = Setting::provinceList();
            $municipalityList = Setting::municipalityList();
            $barangayList = Setting::barangayList();
            array_push($return, 'provinceList', 'municipalityList', 'barangayList');
        } elseif ($subcat == 'job') {
            $companyList = Setting::companyList();
            $departmentList = Setting::departmentList();
            $sectionList = Setting::sectionList();
            $positionList = Setting::positionList();
            $jobGradeList = Setting::jobGradeList();
            $jobStepList = Setting::jobStepList();
            $areaList = Setting::areaList();
            $outletList = Setting::outletList();
            array_push($return, 'companyList', 'departmentList', 'sectionList', 'positionList', 'jobGradeList', 'jobStepList', 'areaList', 'outletList');
        } elseif ($subcat == 'skills') {
            $skillsList = Setting::skillsList();
            $skillsCategoryList = Setting::skillsCategoryList();
            array_push($return, 'skillsList', 'skillsCategoryList');
        }

        return view('pages.employee.employee', compact($return));
    }

    /** Show new employee form */
    public static function newEmployee()
    {
        $main_link = 'employee';
        $sub_link = 'new';
        $employeeList = Employee::employeeList();
        $provinceList = Setting::provinceList();
        $municipalityList = Setting::municipalityList();
        $barangayList = Setting::barangayList();
        $emplStatusList = Setting::emplStatusList();
        $companyList = Setting::companyList();
        $departmentList = Setting::departmentList();
        $sectionList = Setting::sectionList();
        $positionList = Setting::positionList();
        $jobGradeList = Setting::jobGradeList();
        $jobStepList = Setting::jobStepList();
        $areaList = Setting::areaList();
        $outletList = Setting::outletList();

        return view('pages.employee.new', compact([
            'main_link', 'sub_link', 'employeeList', 'provinceList', 'municipalityList',
            'barangayList', 'emplStatusList', 'companyList', 'departmentList', 'sectionList',
            'positionList', 'jobGradeList', 'jobStepList', 'areaList', 'outletList',
        ]));
    }

    /**
     * Shared file-storage routine used by license, education certificate,
     * work certificate, and contract uploads. Images get compressed to
     * WebP; everything else is stored as-is.
     */
    protected static function storeEmployeeFile(UploadedFile $file, string $folder, string $fileName): string
    {
        if (in_array(mime_content_type($file->getRealPath()), ['image/jpeg', 'image/png'])) {
            return basename(reduceImageFileSizeToWebP('s3', $file->getRealPath(), 1024, "{$folder}/{$fileName}"));
        }

        $file->storeAs($folder, $fileName, 's3');
        return $fileName;
    }

    /** Save new employee */
    public static function createEmployee(Request $request)
    {
        try {
            $validated = $request->validate([
                'new-employee-number' => 'required|string|max:20',
                'new-employee-firstname' => 'required|string|max:255',
                'new-employee-middlename' => 'nullable|string|max:255',
                'new-employee-lastname' => 'required|string|max:255',
                'new-employee-suffix' => 'nullable|string|max:50',
                'new-employee-email' => 'required|email|max:255',
                'new-employee-contact' => 'required|string|max:13',
                'new-employee-company-contact' => 'nullable|string|max:13',
                'new-employee-telephone' => 'nullable|string|max:20',
                'new-employee-padd-province' => 'nullable|string|max:255',
                'new-employee-padd-city' => 'nullable|string|max:255',
                'new-employee-padd-barangay' => 'nullable|string|max:255',
                'new-employee-padd-specific' => 'nullable|string|max:255',
                'new-employee-cadd-province' => 'nullable|string|max:255',
                'new-employee-cadd-city' => 'nullable|string|max:255',
                'new-employee-cadd-barangay' => 'nullable|string|max:255',
                'new-employee-cadd-specific' => 'nullable|string|max:255',
                'new-employee-badd-province' => 'nullable|string|max:255',
                'new-employee-badd-city' => 'nullable|string|max:255',
                'new-employee-badd-barangay' => 'nullable|string|max:255',
                'new-employee-badd-specific' => 'nullable|string|max:255',
                'new-employee-birthdate' => 'required|date|before:today',
                'new-employee-civil-status' => 'required|in:Single,Married,Separated/Divorced,Widow/Widower',
                'new-employee-sex' => 'required|in:Male,Female',
                'new-employee-bloodtype' => 'nullable|string|max:3',
                'new-employee-height' => 'nullable|numeric|min:50|max:250',
                'new-employee-weight' => 'nullable|numeric|min:20|max:300',
                'new-employee-religion' => 'nullable|string|max:255',
                'new-employee-dialect' => 'nullable|string|max:255',
                'new-employee-sss' => 'nullable|string|max:50',
                'new-employee-hdmf' => 'nullable|string|max:50',
                'new-employee-phic' => 'nullable|string|max:50',
                'new-employee-tin' => 'nullable|string|max:50',
                'new-employee-date-hired' => 'required|date|before_or_equal:today',
                'new-employee-employment-status' => 'required|string|max:50',
                'new-employee-company' => 'required|string|max:50',
                'new-employee-department' => 'required|string|max:50',
                'new-employee-section' => 'required|string|max:50',
                'new-employee-position' => 'required|string|max:50',
                'new-employee-job-step' => 'required|string|max:50',
                'new-employee-job-grade' => 'required|string|max:50',
                'new-employee-area' => 'required|string|max:50',
                'new-employee-outlet' => 'required|string|max:50',
                'new-employee-reportto' => 'nullable|string|max:50',
            ]);

            DB::transaction(function () use ($validated) {
                Employee::createEmployee($validated);
            });

            return redirect("/employee/profile/personal/{$validated['new-employee-number']}")->with('success', 'Employee created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to insert employee: ' . $e->getMessage()]);
        }
    }

    public function generateEmpNo(Request $request)
    {
        $validated = $request->validate([
            'dateHired' => 'required|date',
            'area' => 'required|string',
            'outlet' => 'required|string',
        ]);

        $prefix = ($validated['outlet'] == 'ADMIN' ? '045' : $validated['area']) . '-' . date('Y', strtotime($validated['dateHired']));

        $search = DB::table('tbl201_persinfo')
            ->select('pers_empno')
            ->where('pers_empno', 'like', "{$prefix}%")
            ->orderByDesc('pers_empno')
            ->first();

        if ($search?->pers_empno) {
            $parts = explode('-', $search?->pers_empno);
            return $prefix . '-' . str_pad((intval($parts[2] ?? 1) + 1), 3, '0', STR_PAD_LEFT);
        }

        return $prefix . '-001';
    }

    public static function personalInfo($empno)
    {
        $empData = Employee::personalInfo($empno);
        if (empty($empData)) {
            $empData = new stdClass();
            $empData->pers_firstname = '';
            $empData->pers_midname = '';
            $empData->pers_lastname = '';
            $empData->pers_birthdate = '';
            $empData->age = '';
            $empData->pers_civilstat = '';
            $empData->pers_sex = '';
            $empData->pers_bloodtype = '';
            $empData->pers_height = '';
            $empData->pers_weight = '';
            $empData->pers_religion = '';
            $empData->pers_dialect = '';
            $empData->cont_email = '';
            $empData->cont_person_num = '';
            $empData->cont_company_num = '';
            $empData->cont_telephone = '';
            $empData->add_perm_prov = '';
            $empData->add_perm_city = '';
            $empData->add_perm_brngy = '';
            $empData->add_perm_location = '';
            $empData->add_cur_prov = '';
            $empData->add_cur_city = '';
            $empData->add_cur_brngy = '';
            $empData->add_cur_location = '';
            $empData->add_birth_prov = '';
            $empData->add_birth_city = '';
            $empData->add_birth_brngy = '';
            $empData->add_birth_location = '';
            $empData->gov_sss = '';
            $empData->gov_pagibig = '';
            $empData->gov_philhealth = '';
            $empData->gov_tin = '';
        }
        return $empData;
    }

    /** Save employee personal info */
    public static function savePersonalInfo(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee-number' => 'required|string|max:20',
                'personal-firstname' => 'required|string|max:255',
                'personal-middlename' => 'nullable|string|max:255',
                'personal-lastname' => 'required|string|max:255',
                'personal-suffix' => 'nullable|string|max:50',
                'personal-email' => 'required|email|max:255',
                'personal-contact' => 'required|string|max:13',
                'personal-company-contact' => 'nullable|string|max:13',
                'personal-telephone' => 'nullable|string|max:20',
                'personal-padd-province' => 'nullable|string|max:255',
                'personal-padd-city' => 'nullable|string|max:255',
                'personal-padd-barangay' => 'nullable|string|max:255',
                'personal-padd-specific' => 'nullable|string|max:255',
                'personal-cadd-province' => 'nullable|string|max:255',
                'personal-cadd-city' => 'nullable|string|max:255',
                'personal-cadd-barangay' => 'nullable|string|max:255',
                'personal-cadd-specific' => 'nullable|string|max:255',
                'personal-badd-province' => 'nullable|string|max:255',
                'personal-badd-city' => 'nullable|string|max:255',
                'personal-badd-barangay' => 'nullable|string|max:255',
                'personal-badd-specific' => 'nullable|string|max:255',
                'personal-birthdate' => 'required|date|before:today',
                'personal-civil-status' => 'required|in:Single,Married,Separated/Divorced,Widow/Widower',
                'personal-sex' => 'required|in:Male,Female',
                'personal-bloodtype' => 'nullable|string|max:3',
                'personal-height' => 'nullable|numeric',
                'personal-weight' => 'nullable|numeric',
                'personal-religion' => 'nullable|string|max:255',
                'personal-dialect' => 'nullable|string|max:255',
                'personal-sss' => 'nullable|string|max:50',
                'personal-hdmf' => 'nullable|string|max:50',
                'personal-phic' => 'nullable|string|max:50',
                'personal-tin' => 'nullable|string|max:50',
            ]);

            DB::transaction(function () use ($validated) {
                Employee::savePersonalInfo($validated);
            });

            return redirect("/employee/profile/personal/{$validated['employee-number']}")->with('success', 'Personal Info updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to update personal info: ' . $e->getMessage()]);
        }
    }

    public static function savePersonalImg(Request $request)
    {
        try {
            $validated = $request->validate([
                'empno' => 'required|string',
                'image' => 'mimes:jpg,jpeg,png',
            ]);

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $fileName = $validated['empno'] . '.' . $file->getClientOriginalExtension();
                self::storeEmployeeFile($file, 'images/employees', $fileName);

                return response()->json(['success' => true]);
            }

            return response()->json(['error' => 'No file uploaded'], 400);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function familyInfo($empno)
    {
        return Employee::familyInfo($empno);
    }

    /** Save employee family info */
    public static function saveFamilyInfo(Request $request)
    {
        try {
            $validated = $request->validate([
                'family-id' => 'nullable|numeric',
                'employee-number' => 'required|string|max:20',
                'family-relationship' => 'required|string',
                'family-firstname' => 'required|string',
                'family-middlename' => 'nullable|string',
                'family-lastname' => 'required|string',
                'family-suffix' => 'nullable|string',
                'family-maidenname' => 'nullable|string',
                'family-birthdate' => 'required|date|before:today',
                'family-sex' => 'required|string',
                'family-contact' => 'required|string',
                'family-address' => 'required|string',
                'family-occupation' => 'nullable|string',
                'family-workplace' => 'nullable|string',
            ]);

            DB::transaction(function () use ($validated) {
                Employee::saveFamilyInfo($validated);
            });

            return redirect("/employee/profile/family/{$validated['employee-number']}")->with('success', 'Family Info updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to update family info: ' . $e->getMessage()]);
        }
    }

    /** Remove employee family info */
    public static function removeFamilyInfo($empno, $id)
    {
        try {
            Employee::removeFamilyInfo($empno, $id);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed to update family info: ' . $e->getMessage()]);
        }
    }

    public static function skillsInfo($empno)
    {
        return Employee::skillsInfo($empno);
    }

    /** Save employee skill info */
    public static function saveSkillsInfo(Request $request)
    {
        try {
            $validated = $request->validate([
                'skill-id' => 'nullable|numeric',
                'employee-number' => 'required|string|max:20',
                'skill-category' => 'required|numeric',
                'skill-type' => 'nullable|numeric',
                'skill-other' => 'nullable|string',
            ]);

            DB::transaction(function () use ($validated) {
                Employee::saveSkillsInfo($validated);
            });

            return redirect("/employee/profile/skills/{$validated['employee-number']}")->with('success', 'Skills Info updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to update skills info: ' . $e->getMessage()]);
        }
    }

    /** Remove employee skill info */
    public static function removeSkillsInfo($empno, $id)
    {
        try {
            Employee::removeSkillsInfo($empno, $id);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed to update skills info: ' . $e->getMessage()]);
        }
    }

    public static function educationInfo($empno)
    {
        return Employee::educationInfo($empno);
    }

    /** Save employee education info */
    public static function saveEducationInfo(Request $request)
    {
        try {
            $validated = $request->validate([
                'education-id' => 'nullable|numeric',
                'employee-number' => 'required|string|max:20',
                'education-level' => 'required|string',
                'education-degree' => 'nullable|string',
                'education-major' => 'nullable|string',
                'education-school' => 'nullable|string',
                'education-address' => 'nullable|string',
                'education-year-graduated' => 'nullable|numeric',
                'education-curstat' => 'nullable|string',
            ]);

            DB::transaction(function () use ($validated) {
                Employee::saveEducationInfo($validated);
            });

            return redirect("/employee/profile/education/{$validated['employee-number']}")->with('success', 'Education Info updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to update education info: ' . $e->getMessage()]);
        }
    }

    /** Remove employee education info */
    public static function removeEducationInfo($empno, $id)
    {
        try {
            Employee::removeEducationInfo($empno, $id);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed to update education info: ' . $e->getMessage()]);
        }
    }

    public static function licenseInfo($empno)
    {
        return Employee::licenseInfo($empno);
    }

    /** Save employee license/eligibility info */
    public static function saveLicenseInfo(Request $request)
    {
        try {
            $validated = $request->validate([
                'license-id' => 'nullable|numeric',
                'employee-number' => 'required|string|max:20',
                'license-registration-date' => 'required|date|before_or_equal:today',
                'license-valid-until' => 'required|date',
                'license-type' => 'required|string',
                'license-profession' => 'nullable|string',
                'license-attachment' => 'nullable|file',
                'license-attachment-current' => 'nullable|string',
            ]);

            if ($request->hasFile('license-attachment')) {
                $file = $request->file('license-attachment');
                $fileName = $validated['employee-number'] . '_' . time() . '.' . $file->getClientOriginalExtension();
                $validated['license-attachment'] = self::storeEmployeeFile($file, 'licenses', $fileName);
            }

            DB::transaction(function () use ($validated) {
                Employee::saveLicenseInfo($validated);
            });

            return redirect("/employee/professional/license/{$validated['employee-number']}")->with('success', 'License Info updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to update license/eligibility info: ' . $e->getMessage()]);
        }
    }

    /** Remove employee license/eligibility info */
    public static function removeLicenseInfo($empno, $id)
    {
        try {
            Employee::removeLicenseInfo($empno, $id);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed to update license/eligibility info: ' . $e->getMessage()]);
        }
    }

    public static function eduCertificateInfo($empno)
    {
        return Employee::eduCertificateInfo($empno);
    }

    /** Save employee edu certificate info */
    public static function saveEduCertificateInfo(Request $request)
    {
        try {
            $validated = $request->validate([
                'educcertificate-id' => 'nullable|numeric',
                'employee-number' => 'required|string|max:20',
                'educcertificate-title' => 'required|string',
                'educcertificate-location' => 'required|string',
                'educcertificate-completion-date' => 'required|date|before_or_equal:today',
                'educcertificate-speaker' => 'nullable|string',
                'educcertificate-attachment' => 'nullable|file',
                'educcertificate-attachment-current' => 'nullable|string',
            ]);

            if ($request->hasFile('educcertificate-attachment')) {
                $file = $request->file('educcertificate-attachment');
                $fileName = $validated['employee-number'] . '_' . time() . '.' . $file->getClientOriginalExtension();
                $validated['educcertificate-attachment'] = self::storeEmployeeFile($file, 'certificates', $fileName);
            }

            DB::transaction(function () use ($validated) {
                Employee::saveEduCertificateInfo($validated);
            });

            return redirect("/employee/professional/certificate/{$validated['employee-number']}")->with('success', 'Professional certificate Info updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to update certificate info: ' . $e->getMessage()]);
        }
    }

    /** Remove employee edu certificate info */
    public static function removeEduCertificateInfo($empno, $id)
    {
        try {
            Employee::removeEduCertificateInfo($empno, $id);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed to update certificate info: ' . $e->getMessage()]);
        }
    }

    public static function employmentInfo($empno)
    {
        return Employee::employmentInfo($empno);
    }

    /** Save employee employment info */
    public static function saveEmploymentInfo(Request $request)
    {
        try {
            $validated = $request->validate([
                'employment-id' => 'nullable|numeric',
                'employee-number' => 'required|string|max:20',
                'employment-start-date' => 'required|date',
                'employment-end-date' => 'required|date',
                'employment-company' => 'nullable|string',
                'employment-address' => 'nullable|string',
                'employment-position' => 'nullable|string',
                'employment-contact' => 'nullable|string',
                'employment-supervisor' => 'nullable|string',
                'employment-reason' => 'nullable|string',
            ]);

            DB::transaction(function () use ($validated) {
                Employee::saveEmploymentInfo($validated);
            });

            return redirect("/employee/work/employment/{$validated['employee-number']}")->with('success', 'Employment Info updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to update employment info: ' . $e->getMessage()]);
        }
    }

    /** Remove employee employment info */
    public static function removeEmploymentInfo($empno, $id)
    {
        try {
            Employee::removeEmploymentInfo($empno, $id);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed to update employment info: ' . $e->getMessage()]);
        }
    }

    public static function jobInfo($empno)
    {
        return Employee::jobInfo($empno);
    }

    public static function saveJobInfo(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee-number' => 'required|string|max:20',
                'jobinfo-date-hired' => 'required|date|before_or_equal:today',
                'jobinfo-date-regular' => 'nullable|date',
                'jobinfo-date-resigned' => 'nullable|date',
                'jobinfo-remarks' => 'required|in:Active,Inactive',
                'jobinfo-separation-type' => 'nullable|string|max:50',
                'jobinfo-remarks-description' => 'nullable|string|max:255',
            ]);

            DB::transaction(function () use ($validated) {
                Employee::saveJobInfo($validated);
            });

            return redirect("/employee/work/job/{$validated['employee-number']}")->with('success', 'Job Info updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to update job info: ' . $e->getMessage()]);
        }
    }

    public static function saveJobRecord(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee-number' => 'required|string|max:20',
                'jobrec-id' => 'nullable|numeric',
                'jobrec-date-effect' => 'required|date',
                'jobrec-company' => 'required|string|max:50',
                'jobrec-department' => 'required|string|max:50',
                'jobrec-section' => 'required|string|max:50',
                'jobrec-position' => 'required|string|max:50',
                'jobrec-job-step' => 'required|string|max:50',
                'jobrec-job-grade' => 'required|string|max:50',
                'jobrec-area' => 'required|string|max:50',
                'jobrec-outlet' => 'required|string|max:50',
                'jobrec-reportto' => 'nullable|string|max:50',
                'jobrec-status' => 'required|string|in:Primary,Secondary,Inactive',
            ]);

            DB::transaction(function () use ($validated) {
                Employee::saveJobRecord($validated);
            });

            return redirect("/employee/work/job/{$validated['employee-number']}")->with('success', 'Job Info updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to update job info: ' . $e->getMessage()]);
        }
    }

    public static function removeJobRecord($empno, $id)
    {
        try {
            Employee::removeJobRecord($empno, $id);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed to update job info: ' . $e->getMessage()]);
        }
    }

    public static function workCertificateInfo($empno)
    {
        return Employee::workCertificateInfo($empno);
    }

    /** Save employee work certificate info */
    public static function saveWorkCertificateInfo(Request $request)
    {
        try {
            $validated = $request->validate([
                'internalcertificate-id' => 'nullable|numeric',
                'employee-number' => 'required|string|max:20',
                'internalcertificate-title' => 'required|string',
                'internalcertificate-location' => 'required|string',
                'internalcertificate-completion-date' => 'required|date|before_or_equal:today',
                'internalcertificate-speaker' => 'nullable|string',
                'internalcertificate-attachment' => 'nullable|file',
                'internalcertificate-attachment-current' => 'nullable|string',
            ]);

            if ($request->hasFile('internalcertificate-attachment')) {
                $file = $request->file('internalcertificate-attachment');
                $fileName = $validated['employee-number'] . '_' . time() . '.' . $file->getClientOriginalExtension();
                $validated['internalcertificate-attachment'] = self::storeEmployeeFile($file, 'certificates', $fileName);
            }

            DB::transaction(function () use ($validated) {
                Employee::saveWorkCertificateInfo($validated);
            });

            return redirect("/employee/work/certificate/{$validated['employee-number']}")->with('success', 'Certificate Info updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to update certificate info: ' . $e->getMessage()]);
        }
    }

    /** Remove employee work certificate info */
    public static function removeWorkCertificateInfo($empno, $id)
    {
        try {
            Employee::removeWorkCertificateInfo($empno, $id);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed to update certificate info: ' . $e->getMessage()]);
        }
    }

    public static function payslipInfo($empno)
    {
        return Employee::payslipInfo($empno);
    }

    public static function contractsInfo($empno)
    {
        return Employee::contractsInfo($empno);
    }

    /** Save employee work contract info */
    public static function saveContractInfo(Request $request)
    {
        try {
            $validated = $request->validate([
                'contract-id' => 'nullable|numeric',
                'employee-number' => 'required|string|max:20',
                'contract-description' => 'required|string',
                'contract-start-date' => 'required|date',
                'contract-end-date' => 'required|date',
                'contract-attachment.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
                'contract-attachment-current' => 'nullable|array',
                'contract-attachment-current.*' => 'string',
            ]);

            $data = [
                'id' => $validated['contract-id'],
                'emp' => $validated['employee-number'],
                'description' => $validated['contract-description'],
                'start-date' => $validated['contract-start-date'],
                'end-date' => $validated['contract-end-date'],
                'filenames' => null,
                'curfiles' => $validated['contract-attachment-current'] ?? [],
            ];

            if ($request->hasFile('contract-attachment')) {
                $uploadedFiles = $request->file('contract-attachment');
                $data['filenames'] = [];
                foreach ($uploadedFiles as $index => $file) {
                    $suffix = $index ? "({$index})" : '';
                    $fileName = $data['emp'] . '_' . time() . $suffix . '.' . $file->getClientOriginalExtension();
                    $data['filenames'][] = self::storeEmployeeFile($file, 'contracts', $fileName);
                }
            }

            $data['filenames'] = json_encode(array_merge($data['filenames'] ?? [], $data['curfiles'] ?? []));

            DB::transaction(function () use ($data) {
                Contract::store($data);
            });

            return redirect("/employee/work/contracts/{$validated['employee-number']}")->with('success', 'Contract Info updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to update contract info: ' . $e->getMessage()]);
        }
    }

    /** Remove employee work contract info */
    public static function removeContractInfo($id)
    {
        try {
            Contract::destroy($id);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed to update contract info: ' . $e->getMessage()]);
        }
    }

    public static function characterrefInfo($empno)
    {
        return Employee::characterrefInfo($empno);
    }

    /** Save employee character reference info */
    public static function saveCharacterrefInfo(Request $request)
    {
        try {
            $validated = $request->validate([
                'characterref-id' => 'nullable|numeric',
                'employee-number' => 'required|string|max:20',
                'characterref-name' => 'required|string',
                'characterref-position' => 'nullable|string',
                'characterref-company' => 'nullable|string',
                'characterref-address' => 'required|string',
                'characterref-contact' => 'required|string',
                'characterref-relationship' => 'required|string',
            ]);

            DB::transaction(function () use ($validated) {
                Employee::saveCharacterrefInfo($validated);
            });

            return redirect("/employee/work/characterref/{$validated['employee-number']}")->with('success', 'Character reference Info updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to update character reference info: ' . $e->getMessage()]);
        }
    }

    /** Remove employee character reference info */
    public static function removeCharacterrefInfo($empno, $id)
    {
        try {
            Employee::removeCharacterrefInfo($empno, $id);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed to update character reference info: ' . $e->getMessage()]);
        }
    }

    public static function enneagramInfo($empno)
    {
        return Employee::enneagramInfo($empno);
    }

    public static function taptInfo($empno)
    {
        return Employee::taptInfo($empno);
    }

    public static function discInfo($empno)
    {
        return Employee::discInfo($empno);
    }

    public static function miqInfo($empno)
    {
        return Employee::miqInfo($empno);
    }

    public static function colorInfo($empno)
    {
        return Employee::colorInfo($empno);
    }

    public static function vakInfo($empno)
    {
        return Employee::vakInfo($empno);
    }

    // ── Personality results, pre-formatted for display ──
    // Pulled out of the showInfo() if-chain so each map/transform lives
    // next to its raw-data counterpart above, and the dispatch table stays
    // a flat one-resolver-per-tab lookup.

    public static function formattedEnneagramInfo($empno)
    {
        return self::enneagramInfo($empno)->map(function ($item) {
            $item->result = $item->result->map(fn ($v, $k) => "{$k}: {$v}")->implode(', ');
            return $item;
        });
    }

    public static function formattedTaptInfo($empno)
    {
        return self::taptInfo($empno)->map(function ($item) {
            $item->result = $item->result->implode(', ');
            return $item;
        });
    }

    public static function formattedDiscInfo($empno)
    {
        return self::discInfo($empno)->map(function ($item) {
            $item->result = $item->result->keys()->implode(', ');
            return $item;
        });
    }

    public static function formattedMiqInfo($empno)
    {
        return self::miqInfo($empno)->map(function ($item) {
            $item->result = $item->result->map(fn ($v, $k) => "{$k}: {$v}")->implode(', ');
            return $item;
        });
    }

    public static function formattedColorInfo($empno)
    {
        return self::colorInfo($empno)->map(function ($item) {
            $item->result = $item->result->keys()->implode(', ');
            return $item;
        });
    }

    public static function formattedVakInfo($empno)
    {
        return self::vakInfo($empno)->map(function ($item) {
            $item->result = $item->result->keys()->implode(', ');
            return $item;
        });
    }

    public static function outgoingList()
    {
        $data = Employee::outgoingList();

        // Scoped styles (unique wrapper class) so this renders consistently
        // wherever it's injected, without leaking into the rest of the page.
        $html = "<div class='outgoing-list-wrap'>";
        $html .= "<style>";
        $html .= ".outgoing-list-wrap table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 13.5px; }";
        $html .= ".outgoing-list-wrap thead th { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #868e96; background: #fcfcfd; border-bottom: 1px solid #e9ecef; padding: .8rem 1rem; white-space: nowrap; }";
        $html .= ".outgoing-list-wrap tbody td { padding: .8rem 1rem; border-bottom: 1px solid #f1f3f5; color: #343a40; vertical-align: middle; }";
        $html .= ".outgoing-list-wrap tbody tr { cursor: pointer; transition: background-color .12s ease; }";
        $html .= ".outgoing-list-wrap tbody tr:hover { background-color: #f8f9fb; }";
        $html .= ".outgoing-list-wrap tbody tr:last-child td { border-bottom: none; }";
        $html .= ".outgoing-list-wrap .og-name { font-weight: 600; color: #1c1f24; }";
        $html .= ".outgoing-list-wrap .og-lastday { display: inline-block; padding: 2px 10px; border-radius: 999px; background: #FDEEEE; color: #9c2b2b; font-size: 12px; font-weight: 600; white-space: nowrap; }";
        $html .= ".outgoing-list-wrap .og-empty { padding: 3rem 1rem; text-align: center; color: #868e96; font-size: 13.5px; }";
        $html .= "</style>";

        $html .= "<table class='table table-sm mb-0'>";
        $html .= "<thead><tr>";
        $html .= "<th>Emp #</th><th>Company</th><th>Department</th><th>Name</th><th>Position</th><th>Last Day</th>";
        $html .= "</tr></thead>";
        $html .= "<tbody>";

        if (count($data) === 0) {
            $html .= "<tr><td colspan='6' class='og-empty'>No outgoing employees found.</td></tr>";
        }

        // e() escapes each value individually so employee-supplied data
        // (name, position, etc.) can't inject markup into the report.
        foreach ($data as $v) {
            $empno = e($v->pers_empno);
            $html .= "<tr ondblclick=\"viewInfo('" . $empno . "')\">";
            $html .= "<td>" . $empno . "</td>";
            $html .= "<td>" . e($v->C_Name) . "</td>";
            $html .= "<td>" . e($v->Dept_Name) . "</td>";
            $html .= "<td class='og-name'>" . e(trim(ucwords($v->pers_lastname . ', ' . $v->pers_firstname))) . "</td>";
            $html .= "<td>" . e($v->jd_title) . "</td>";
            $html .= "<td><span class='og-lastday'>" . e($v->ji_resdate) . "</span></td>";
            $html .= "</tr>";
        }

        $html .= "</tbody>";
        $html .= "</table>";
        $html .= "</div>";

        return $html;
    }

    public static function retentionList($ym)
    {
        $data = collect();
        foreach (Employee::retentionList($ym) as $v) {
            if (!$data->has($v->C_Code)) {
                $data->put($v->C_Code, collect());
            }
            if (!$data[$v->C_Code]->has($v->Dept_Name)) {
                $data[$v->C_Code]->put($v->Dept_Name, collect());
            }
            if (date('Y-m', strtotime($v->ji_resdate)) == $ym) {
                if (!$data[$v->C_Code][$v->Dept_Name]->has('separated')) {
                    $data[$v->C_Code][$v->Dept_Name]->put('separated', collect());
                }
                $data[$v->C_Code][$v->Dept_Name]['separated']->push($v);
            }
            if (date('Y-m', strtotime($v->ji_datehired)) == $ym) {
                if (!$data[$v->C_Code][$v->Dept_Name]->has('new')) {
                    $data[$v->C_Code][$v->Dept_Name]->put('new', collect());
                }
                $data[$v->C_Code][$v->Dept_Name]['new']->push($v);
            }
            if (date('Y-m', strtotime($v->ji_datehired)) < $ym) {
                if (!$data[$v->C_Code][$v->Dept_Name]->has('old')) {
                    $data[$v->C_Code][$v->Dept_Name]->put('old', collect());
                }
                $data[$v->C_Code][$v->Dept_Name]['old']->push($v);
            }
        }

        // Rate math defined once and reused for both the company summary
        // row and each department sub-row (previously duplicated 2x).
        $turnoverRate = fn ($total, $remaining, $separated) => $total
            ? round(($remaining ? $separated / $remaining : 0) * 100) . '%'
            : '-';
        $retentionRate = fn ($total, $remaining) => $total
            ? round(($total ? $remaining / $total : 0) * 100) . '%'
            : '-';
        // Escapes each name individually before joining with a literal
        // <br> (previously concatenated unescaped, an XSS risk).
        $names = fn ($collection) => $collection?->map(
            fn ($i) => e(ucwords(trim($i->pers_lastname . ', ' . $i->pers_firstname)))
        )->implode('<br>');

        // Scoped styles (unique wrapper class) so this renders consistently
        // wherever it's injected, without leaking into the rest of the page.
        $html = "<div class='retention-list-wrap'>";
        $html .= "<style>";
        $html .= ".retention-list-wrap table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 13.5px; }";
        $html .= ".retention-list-wrap thead th { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #868e96; background: #fcfcfd; border-bottom: 1px solid #e9ecef; padding: .8rem 1rem; white-space: nowrap; }";
        $html .= ".retention-list-wrap tbody td { padding: .75rem 1rem; border-bottom: 1px solid #f1f3f5; color: #343a40; vertical-align: middle; }";
        $html .= ".retention-list-wrap .rl-company-row { background: #f5f6f8; cursor: pointer; font-weight: 600; color: #1c1f24; }";
        $html .= ".retention-list-wrap .rl-company-row td { font-weight: 600; }";
        $html .= ".retention-list-wrap .rl-dept-row { cursor: pointer; color: #495057; }";
        $html .= ".retention-list-wrap .rl-dept-row td:nth-child(2) { padding-left: 2rem; }";
        $html .= ".retention-list-wrap .rl-names-row td { background: #fafbfc; font-size: 12.5px; color: #868e96; line-height: 1.7; }";
        $html .= ".retention-list-wrap .rl-rate { display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }";
        $html .= ".retention-list-wrap .rl-rate-good { background: #E1F5EE; color: #085041; }";
        $html .= ".retention-list-wrap .rl-rate-bad { background: #FDEEEE; color: #9c2b2b; }";
        $html .= ".retention-list-wrap .rl-empty { padding: 3rem 1rem; text-align: center; color: #868e96; font-size: 13.5px; }";
        $html .= "</style>";

        $html .= "<table class='table table-sm mb-0'>";
        $html .= "<thead>";
        $html .= "<tr>";
        $html .= "<th>Company</th>";
        $html .= "<th>Department</th>";
        $html .= "<th>Old</th>";
        $html .= "<th>New</th>";
        $html .= "<th>Total</th>";
        $html .= "<th>Separated</th>";
        $html .= "<th>Remaining</th>";
        $html .= "<th>Turn over rate</th>";
        $html .= "<th>Retention Rate</th>";
        $html .= "</tr>";
        $html .= "</thead>";
        $html .= "<tbody>";

        if (count($data) === 0) {
            $html .= "<tr><td colspan='9' class='rl-empty'>No retention data for this period.</td></tr>";
        }

        // Wraps a rate value in a green/red pill depending on whether it
        // reads as favorable, so the table communicates at a glance.
        $rateBadge = fn ($value, $good) => $value === '-'
            ? '-'
            : "<span class='rl-rate " . ($good ? 'rl-rate-good' : 'rl-rate-bad') . "'>{$value}</span>";

        foreach ($data as $c => $cv) {
            $old = $cv->sum(fn ($item) => ($item['old'] ?? null)?->count());
            $new = $cv->sum(fn ($item) => ($item['new'] ?? null)?->count());
            $separated = $cv->sum(fn ($item) => ($item['separated'] ?? null)?->count());
            $remaining = ($old + $new - $separated);
            $total = ($old + $new);

            $cEsc = e($c);
            $cAttr = e(str_replace(' ', '', $c));

            $html .= "<tr class='list1 rl-company-row list-" . $cAttr . "' listsub='" . $cAttr . "'>";
            $html .= "<td colspan=\"2\">" . $cEsc . "</td>";
            $html .= "<td style=\"display: none;\"></td>";
            $html .= "<td>" . $old . "</td>";
            $html .= "<td>" . $new . "</td>";
            $html .= "<td>" . $total . "</td>";
            $html .= "<td>" . $separated . "</td>";
            $html .= "<td>" . $remaining . "</td>";
            $html .= "<td>" . $rateBadge($turnoverRate($total, $remaining, $separated), false) . "</td>";
            $html .= "<td>" . $rateBadge($retentionRate($total, $remaining), true) . "</td>";
            $html .= "</tr>";

            foreach ($cv as $d => $dv) {
                $old = ($dv['old'] ?? null)?->count();
                $new = ($dv['new'] ?? null)?->count();
                $separated = ($dv['separated'] ?? null)?->count();
                $remaining = ($old + $new - $separated);
                $total = ($old + $new);

                $dEsc = e($d);
                $dAttr = e($c . str_replace(' ', '', $d));

                $html .= "<tr class='list2 rl-dept-row list-" . $cAttr . "' listsub='" . $dAttr . "' style='display: none;'>";
                $html .= "<td></td>";
                $html .= "<td>" . $dEsc . "</td>";
                $html .= "<td>" . $old . "</td>";
                $html .= "<td>" . $new . "</td>";
                $html .= "<td>" . $total . "</td>";
                $html .= "<td>" . $separated . "</td>";
                $html .= "<td>" . $remaining . "</td>";
                $html .= "<td>" . $rateBadge($turnoverRate($total, $remaining, $separated), false) . "</td>";
                $html .= "<td>" . $rateBadge($retentionRate($total, $remaining), true) . "</td>";
                $html .= "</tr>";

                $html .= "<tr class='list3 rl-names-row list-" . $cAttr . " list-" . $dAttr . "' style='display: none;'>";
                $html .= "<td></td>";
                $html .= "<td></td>";
                $html .= "<td>" . ($names($dv['old'] ?? null) ?? '') . "</td>";
                $html .= "<td>" . ($names($dv['new'] ?? null) ?? '') . "</td>";
                $html .= "<td></td>";
                $html .= "<td>" . ($names($dv['separated'] ?? null) ?? '') . "</td>";
                $html .= "<td></td>";
                $html .= "<td></td>";
                $html .= "<td></td>";
                $html .= "</tr>";
            }
        }

        $html .= "</tbody>";
        $html .= "</table>";
        $html .= "</div>";

        return $html;
    }
}