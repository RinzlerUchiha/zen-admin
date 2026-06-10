<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Employee;
use App\Models\Setting;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;

class EmployeeController extends Controller
{

    // Show details of a single employee
    public function showInfo($maincat = 'profile', $subcat = 'personal', $empno = '')
    {
        if($empno == ''){
            $empno = Auth::user()?->Emp_No;
        }
        // *** TO DO: add employee not found for invalid employee number ***
        $main_link = 'employee';
        $sub_link = 'employee';
        $page = 'pages.employee.' . $maincat . '.' . $subcat;
        $employeeList = Employee::employeeList();
        $empData = [];
        if ($maincat . '/' . $subcat == 'profile/personal') {
            $empData = self::personalInfo($empno);
        } else if ($maincat . '/' . $subcat == 'profile/family') {
            $empData = self::familyInfo($empno);
        } else if ($maincat . '/' . $subcat == 'profile/skills') {
            $empData = self::skillsInfo($empno);
        } else if ($maincat . '/' . $subcat == 'profile/education') {
            $empData = self::educationInfo($empno);
        } else if ($maincat . '/' . $subcat == 'professional/license') {
            $empData = self::licenseInfo($empno);
        } else if ($maincat . '/' . $subcat == 'professional/certificate') {
            $empData = self::eduCertificateInfo($empno);
        } else if ($maincat . '/' . $subcat == 'work/job') {
            $empData = self::jobInfo($empno);
        } else if ($maincat . '/' . $subcat == 'work/employment') {
            $empData = self::employmentInfo($empno);
        } else if ($maincat . '/' . $subcat == 'work/certificate') {
            $empData = self::workCertificateInfo($empno);
        } else if ($maincat . '/' . $subcat == 'work/payslip') {
            $empData = self::payslipInfo($empno);
        } else if ($maincat . '/' . $subcat == 'work/contracts') {
            $empData = self::contractsInfo($empno);
        } else if ($maincat . '/' . $subcat == 'work/characterref') {
            $empData = self::characterrefInfo($empno);
        } else if ($maincat . '/' . $subcat == 'personality/enneagram') {
            $empData = self::enneagramInfo($empno)->map(function ($item) {
                $item->result = $item->result->map(fn($v, $k) => "{$k}: {$v}")->implode(', ');
                return $item;
            });
        } else if ($maincat . '/' . $subcat == 'personality/tapt') {
            $empData = self::taptInfo($empno)->map(function ($item) {
                $item->result = $item->result->implode(', ');
                return $item;
            });
        } else if ($maincat . '/' . $subcat == 'personality/disc') {
            $empData = self::discInfo($empno)->map(function ($item) {
                $item->result = $item->result->keys()->implode(', ');
                return $item;
            });
        } else if ($maincat . '/' . $subcat == 'personality/miq') {
            $empData = self::miqInfo($empno)->map(function ($item) {
                $item->result = $item->result->map(fn($v, $k) => "{$k}: {$v}")->implode(', ');
                return $item;
            });
        } else if ($maincat . '/' . $subcat == 'personality/color') {
            $empData = self::colorInfo($empno)->map(function ($item) {
                $item->result = $item->result->keys()->implode(', ');
                return $item;
            });
        } else if ($maincat . '/' . $subcat == 'personality/vak') {
            $empData = self::vakInfo($empno)->map(function ($item) {
                $item->result = $item->result->keys()->implode(', ');
                return $item;
            });
        }

        $return = ['employeeList', 'empData', 'empno', 'main_link', 'sub_link', 'maincat', 'subcat', 'page'];
        if ($subcat == 'personal') {
            $provinceList = Setting::provinceList();
            $municipalityList = Setting::municipalityList();
            $barangayList = Setting::barangayList();
            array_push($return, 'provinceList', 'municipalityList', 'barangayList');
        } else if ($subcat == 'job') {
            $companyList = Setting::companyList();
            $departmentList = Setting::departmentList();
            $sectionList = Setting::sectionList();
            $positionList = Setting::positionList();
            $jobGradeList = Setting::jobGradeList();
            $jobStepList = Setting::jobStepList();
            $areaList = Setting::areaList();
            $outletList = Setting::outletList();
            array_push($return, 'companyList', 'departmentList', 'sectionList', 'positionList', 'jobGradeList', 'jobStepList', 'areaList', 'outletList');
        } else if ($subcat == 'skills') {
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
            'main_link',
            'sub_link',
            'employeeList',
            'provinceList',
            'municipalityList',
            'barangayList',
            'emplStatusList',
            'companyList',
            'departmentList',
            'sectionList',
            'positionList',
            'jobGradeList',
            'jobStepList',
            'areaList',
            'outletList'
        ]));
    }

    /** Save new employee */
    public static function createEmployee(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'new-employee-number' => 'required|string|max:20', // Assuming unique employee number
                'new-employee-firstname' => 'required|string|max:255',
                'new-employee-middlename' => 'nullable|string|max:255', // Middlename can be optional
                'new-employee-lastname' => 'required|string|max:255',
                'new-employee-suffix' => 'nullable|string|max:50', // Suffix is optional, e.g. Jr., Sr.
                'new-employee-email' => 'required|email|max:255', // Email should be unique
                'new-employee-contact' => 'required|string|max:13', // Contact +639, 09
                'new-employee-company-contact' => 'nullable|string|max:13', // Company contact is optional
                'new-employee-telephone' => 'nullable|string|max:20', // Telephone is optional, range for length
                'new-employee-padd-province' => 'nullable|string|max:255',
                'new-employee-padd-city' => 'nullable|string|max:255',
                'new-employee-padd-barangay' => 'nullable|string|max:255',
                'new-employee-padd-specific' => 'nullable|string|max:255', // Specific address details, e.g., street
                'new-employee-cadd-province' => 'nullable|string|max:255',
                'new-employee-cadd-city' => 'nullable|string|max:255',
                'new-employee-cadd-barangay' => 'nullable|string|max:255',
                'new-employee-cadd-specific' => 'nullable|string|max:255', // Corresponding for current address if available
                'new-employee-badd-province' => 'nullable|string|max:255',
                'new-employee-badd-city' => 'nullable|string|max:255',
                'new-employee-badd-barangay' => 'nullable|string|max:255',
                'new-employee-badd-specific' => 'nullable|string|max:255', // Birth address (optional)
                'new-employee-birthdate' => 'required|date|before:today', // Birthdate must be a date and in the past
                // 'new-employee-age' => 'required|numeric', // Employee age
                'new-employee-civil-status' => 'required|in:Single,Married,Separated/Divorced,Widow/Widower', // Example statuses
                'new-employee-sex' => 'required|in:Male,Female', // Gender
                'new-employee-bloodtype' => 'nullable|string|max:3', // Blood type should be a 3-character string
                'new-employee-height' => 'nullable|numeric|min:50|max:250', // Height in centimeters (realistic range)
                'new-employee-weight' => 'nullable|numeric|min:20|max:300', // Weight in kilograms (realistic range)
                'new-employee-religion' => 'nullable|string|max:255',
                'new-employee-dialect' => 'nullable|string|max:255',
                'new-employee-sss' => 'nullable|string|max:50', // SSS number format (if applicable)
                'new-employee-hdmf' => 'nullable|string|max:50', // HDMF (Pag-IBIG) number
                'new-employee-phic' => 'nullable|string|max:50', // PHIC (PhilHealth) number
                'new-employee-tin' => 'nullable|string|max:50', // TIN (Tax Identification Number)
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
                'new-employee-reportto' => 'nullable|string|max:50'
            ]);

            DB::transaction(function () use ($validated) {
                // Insert the employee data into the employees table
                Employee::createEmployee($validated);

                // // Additional operations can go here, for example logging the action
                // DB::table('employee_logs')->insert([
                //     'employee_number' => $validated['new-employee-number'],
                //     'action' => 'created',
                //     'created_at' => now(),
                // ]);
            });

            return redirect("/employee/profile/personal/{$validated['new-employee-number']}")->with('success', 'Employee created successfully!');
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            // return response()->json(['error' => 'Failed to insert employee: ' . $e->getMessage()], 500);
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
        
        if($search?->pers_empno){
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
            // $empData->pers_empext = '';

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
            // Validate the form data
            $validated = $request->validate([
                'employee-number' => 'required|string|max:20', // Assuming unique employee number
                'personal-firstname' => 'required|string|max:255',
                'personal-middlename' => 'nullable|string|max:255', // Middlename can be optional
                'personal-lastname' => 'required|string|max:255',
                'personal-suffix' => 'nullable|string|max:50', // Suffix is optional, e.g. Jr., Sr.

                'personal-email' => 'required|email|max:255', // Email should be unique
                'personal-contact' => 'required|string|max:13', // Contact +639, 09
                'personal-company-contact' => 'nullable|string|max:13', // Company contact is optional
                'personal-telephone' => 'nullable|string|max:20', // Telephone is optional, range for length

                'personal-padd-province' => 'nullable|string|max:255',
                'personal-padd-city' => 'nullable|string|max:255',
                'personal-padd-barangay' => 'nullable|string|max:255',
                'personal-padd-specific' => 'nullable|string|max:255', // Specific address details, e.g., street
                'personal-cadd-province' => 'nullable|string|max:255',
                'personal-cadd-city' => 'nullable|string|max:255',
                'personal-cadd-barangay' => 'nullable|string|max:255',
                'personal-cadd-specific' => 'nullable|string|max:255', // Corresponding for current address if available
                'personal-badd-province' => 'nullable|string|max:255',
                'personal-badd-city' => 'nullable|string|max:255',
                'personal-badd-barangay' => 'nullable|string|max:255',
                'personal-badd-specific' => 'nullable|string|max:255', // Birth address (optional)
                'personal-birthdate' => 'required|date|before:today', // Birthdate must be a date and in the past
                // 'personal-age' => 'required|numeric', // Employee age
                'personal-civil-status' => 'required|in:Single,Married,Separated/Divorced,Widow/Widower', // Example statuses
                'personal-sex' => 'required|in:Male,Female', // Gender
                'personal-bloodtype' => 'nullable|string|max:3', // Blood type should be a 3-character string
                'personal-height' => 'nullable|numeric', // |min:50|max:250 Height in centimeters (realistic range)
                'personal-weight' => 'nullable|numeric', // |min:20|max:300 Weight in kilograms (realistic range)
                'personal-religion' => 'nullable|string|max:255',
                'personal-dialect' => 'nullable|string|max:255',
                'personal-sss' => 'nullable|string|max:50', // SSS number format (if applicable)
                'personal-hdmf' => 'nullable|string|max:50', // HDMF (Pag-IBIG) number
                'personal-phic' => 'nullable|string|max:50', // PHIC (PhilHealth) number
                'personal-tin' => 'nullable|string|max:50', // TIN (Tax Identification Number)

                // 'new-employee-date-hired' => 'required|date|before_or_equal:today',
                // 'new-employee-employment-status' => 'required|string|max:50',
                // 'new-employee-company' => 'required|string|max:50',
                // 'new-employee-department' => 'required|string|max:50',
                // 'new-employee-section' => 'required|string|max:50',
                // 'new-employee-position' => 'required|string|max:50',
                // 'new-employee-job-step' => 'required|string|max:50',
                // 'new-employee-job-grade' => 'required|string|max:50',
                // 'new-employee-area' => 'required|string|max:50',
                // 'new-employee-outlet' => 'required|string|max:50',
                // 'new-employee-reportto' => 'nullable|string|max:50'
            ]);

            DB::transaction(function () use ($validated) {
                // Insert the employee data into the employees table
                Employee::savePersonalInfo($validated);
            });

            return redirect("/employee/profile/personal/{$validated['employee-number']}")->with('success', 'Personal Info updated successfully!');
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            // return response()->json(['error' => 'Failed to insert employee: ' . $e->getMessage()], 500);
            return redirect()->back()->withErrors(['error' => 'Failed to update personal info: ' . $e->getMessage()]);
        }
    }

    public static function savePersonalImg(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'empno' => 'required|string',
                'image' => 'mimes:jpg,jpeg,png'
            ]);

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $fileName = $validated['empno'] . '.' . $file->getClientOriginalExtension();
                // $file->move($_SERVER['DOCUMENT_ROOT'].'/zen/assets/image/img', $fileName);

                if(in_array(mime_content_type($file->getRealPath()), ['image/jpeg', 'image/png'])){
                    $fileName = basename(reduceImageFileSizeToWebP(
                        's3',
                        $file->getRealPath(), 
                        1024, 
                        'images/employees/'.$fileName
                    ));
                }else{
                    $file->storeAs('images/employees', $fileName, 's3');
                }

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
            // Validate the form data
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
                'family-workplace' => 'nullable|string'
            ]);

            DB::transaction(function () use ($validated) {
                // Insert the employee data into the employees table
                Employee::saveFamilyInfo($validated);
            });

            return redirect("/employee/profile/family/{$validated['employee-number']}")->with('success', 'Family Info updated successfully!');
            // } catch (QueryException $e) {
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
            // } catch (QueryException $e) {
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
            // Validate the form data
            $validated = $request->validate([
                'skill-id' => 'nullable|numeric',
                'employee-number' => 'required|string|max:20',
                'skill-category' => 'required|numeric',
                'skill-type' => 'nullable|numeric',
                'skill-other' => 'nullable|string'
            ]);

            DB::transaction(function () use ($validated) {
                // Insert the employee data into the employees table
                Employee::saveSkillsInfo($validated);
            });

            return redirect("/employee/profile/skills/{$validated['employee-number']}")->with('success', 'Skills Info updated successfully!');
            // } catch (QueryException $e) {
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
            // } catch (QueryException $e) {
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
            // Validate the form data
            $validated = $request->validate([
                'education-id' => 'nullable|numeric',
                'employee-number' => 'required|string|max:20',
                'education-level' => 'required|string',
                'education-degree' => 'nullable|string',
                'education-major' => 'nullable|string',
                'education-school' => 'nullable|string',
                'education-address' => 'nullable|string',
                'education-year-graduated' => 'nullable|numeric',
                'education-curstat' => 'nullable|string'
            ]);

            DB::transaction(function () use ($validated) {
                // Insert the employee data into the employees table
                Employee::saveEducationInfo($validated);
            });

            return redirect("/employee/profile/education/{$validated['employee-number']}")->with('success', 'Education Info updated successfully!');
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to update skills info: ' . $e->getMessage()]);
        }
    }

    /** Remove employee education info */
    public static function removeEducationInfo($empno, $id)
    {
        try {

            Employee::removeEducationInfo($empno, $id);

            return response()->json(['success' => true]);
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed to update skills info: ' . $e->getMessage()]);
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
            // Validate the form data
            $validated = $request->validate([
                'license-id' => 'nullable|numeric',
                'employee-number' => 'required|string|max:20',
                'license-registration-date' => 'required|date|before_or_equal:today',
                'license-valid-until' => 'required|date',
                'license-type' => 'required|string',
                'license-profession' => 'nullable|string',
                'license-attachment' => 'nullable|file',
                'license-attachment-current' => 'nullable|string'
            ]);

            // $request->validate([
            //     'file' => ['required', 'file', function ($attribute, $value, $fail) {
            //         $allowedExtensions = ['jpeg', 'png', 'pdf'];
            //         $extension = $value->getClientOriginalExtension();
            //         if (!in_array($extension, $allowedExtensions)) {
            //             $fail('The ' . $attribute . ' must be a file of type: jpeg, png, pdf.');
            //         }
            //     }],
            // ]);
            // $request->validate([
            //     'file' => 'required|file|mimes:jpeg,png,pdf|max:2048', // Validation rules
            // ]);

            if ($request->hasFile('license-attachment')) {
                $file = $request->file('license-attachment');
                // $fileName = time() . '_' . $file->getClientOriginalName();
                // $path = $file->storeAs('', $fileName, 'custom_s3');
                $fileName = $validated['employee-number'] . '_' . time() . '.' . $file->getClientOriginalExtension();
                // $file->move($_SERVER['DOCUMENT_ROOT'].'/zen/assets/license', $fileName);

                if(in_array(mime_content_type($file->getRealPath()), ['image/jpeg', 'image/png'])){
                    $fileName = basename(reduceImageFileSizeToWebP(
                        's3',
                        $file->getRealPath(), 
                        1024, 
                        'licenses/'.$fileName
                    ));
                }else{
                    $file->storeAs('licenses', $fileName, 's3');
                }

                $validated['license-attachment'] = $fileName;
            }

            DB::transaction(function () use ($validated) {
                // Insert the employee data into the employees table
                Employee::saveLicenseInfo($validated);
            });

            return redirect("/employee/professional/license/{$validated['employee-number']}")->with('success', 'License Info updated successfully!');
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to update license/eligibility info: ' . $e->getMessage()]);
        }
    }

    /** Save employee license/eligibility info */
    public static function removeLicenseInfo($empno, $id)
    {
        try {

            Employee::removeLicenseInfo($empno, $id);

            return response()->json(['success' => true]);
            // } catch (QueryException $e) {
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
            // Validate the form data
            $validated = $request->validate([
                'educcertificate-id' => 'nullable|numeric',
                'employee-number' => 'required|string|max:20',
                'educcertificate-title' => 'required|string',
                'educcertificate-location' => 'required|string',
                'educcertificate-completion-date' => 'required|date|before_or_equal:today',
                'educcertificate-speaker' => 'nullable|string',
                'educcertificate-attachment' => 'nullable|file',
                'educcertificate-attachment-current' => 'nullable|string'
            ]);

            if ($request->hasFile('educcertificate-attachment')) {
                $file = $request->file('educcertificate-attachment');
                // $fileName = time() . '_' . $file->getClientOriginalName();
                // $path = $file->storeAs('', $fileName, 'custom_s3');
                $fileName = $validated['employee-number'] . '_' . time() . '.' . $file->getClientOriginalExtension();
                // $file->move($_SERVER['DOCUMENT_ROOT'].'/zen/assets/certificate', $fileName);

                if(in_array(mime_content_type($file->getRealPath()), ['image/jpeg', 'image/png'])){
                    $fileName = basename(reduceImageFileSizeToWebP(
                        's3',
                        $file->getRealPath(), 
                        1024, 
                        'certificates/'.$fileName
                    ));
                }else{
                    $file->storeAs('certificates', $fileName, 's3');
                }

                $validated['educcertificate-attachment'] = $fileName;
            }

            DB::transaction(function () use ($validated) {
                // Insert the employee data into the employees table
                Employee::saveEduCertificateInfo($validated);
            });

            return redirect("/employee/professional/certificate/{$validated['employee-number']}")->with('success', 'Professional certificate Info updated successfully!');
            // } catch (QueryException $e) {
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
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed to update certificate info: ' . $e->getMessage()]);
        }
    }

    public static function employmentInfo($empno)
    {
        return Employee::employmentInfo($empno);
    }

    /** Save employee employement info */
    public static function saveEmploymentInfo(Request $request)
    {
        try {
            // Validate the form data
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
                'employment-reason' => 'nullable|string'
            ]);

            DB::transaction(function () use ($validated) {
                // Insert the employee data into the employees table
                Employee::saveEmploymentInfo($validated);
            });

            return redirect("/employee/work/employment/{$validated['employee-number']}")->with('success', 'Employment Info updated successfully!');
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to update employment info: ' . $e->getMessage()]);
        }
    }

    /** Remove employee employement info */
    public static function removeEmploymentInfo($empno, $id)
    {
        try {

            Employee::removeEmploymentInfo($empno, $id);

            return response()->json(['success' => true]);
            // } catch (QueryException $e) {
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
            // Validate the form data
            $validated = $request->validate([
                'employee-number' => 'required|string|max:20', // Assuming unique employee number
                'jobinfo-date-hired' => 'required|date|before_or_equal:today',
                'jobinfo-date-regular' => 'nullable|date',
                'jobinfo-date-resigned' => 'nullable|date',
                'jobinfo-remarks' => 'required|in:Active,Inactive', // Gender
                'jobinfo-separation-type' => 'nullable|string|max:50',
                'jobinfo-remarks-description' => 'nullable|string|max:255'
            ]);

            DB::transaction(function () use ($validated) {
                // Insert the employee data into the employees table
                Employee::saveJobInfo($validated);
            });

            return redirect("/employee/work/job/{$validated['employee-number']}")->with('success', 'Job Info updated successfully!');
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            // return response()->json(['error' => 'Failed to insert employee: ' . $e->getMessage()], 500);
            return redirect()->back()->withErrors(['error' => 'Failed to update personal info: ' . $e->getMessage()]);
        }
    }

    public static function saveJobRecord(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'employee-number' => 'required|string|max:20', // Assuming unique employee number
                'jobrec-id' => 'nullable|numeric', // Assuming unique employee number
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
                'jobrec-status' => 'required|string|in:Primary,Secondary,Inactive'
            ]);

            DB::transaction(function () use ($validated) {
                // Insert the employee data into the employees table
                Employee::saveJobRecord($validated);
            });

            return redirect("/employee/work/job/{$validated['employee-number']}")->with('success', 'Job Info updated successfully!');
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            // return response()->json(['error' => 'Failed to insert employee: ' . $e->getMessage()], 500);
            return redirect()->back()->withErrors(['error' => 'Failed to update personal info: ' . $e->getMessage()]);
        }
    }

    public static function removeJobRecord($empno, $id)
    {
        try {

            Employee::removeJobRecord($empno, $id);

            return response()->json(['success' => true]);
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            // return response()->json(['error' => 'Failed to insert employee: ' . $e->getMessage()], 500);
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
            // Validate the form data
            $validated = $request->validate([
                'internalcertificate-id' => 'nullable|numeric',
                'employee-number' => 'required|string|max:20',
                'internalcertificate-title' => 'required|string',
                'internalcertificate-location' => 'required|string',
                'internalcertificate-completion-date' => 'required|date|before_or_equal:today',
                'internalcertificate-speaker' => 'nullable|string',
                'internalcertificate-attachment' => 'nullable|file',
                'internalcertificate-attachment-current' => 'nullable|string'
            ]);

            if ($request->hasFile('internalcertificate-attachment')) {
                $file = $request->file('internalcertificate-attachment');
                // $fileName = time() . '_' . $file->getClientOriginalName();
                // $path = $file->storeAs('', $fileName, 'custom_s3');
                $fileName = $validated['employee-number'] . '_' . time() . '.' . $file->getClientOriginalExtension();
                // $file->move($_SERVER['DOCUMENT_ROOT'].'/zen/assets/certificate', $fileName);

                if(in_array(mime_content_type($file->getRealPath()), ['image/jpeg', 'image/png'])){
                    $fileName = basename(reduceImageFileSizeToWebP(
                        's3',
                        $file->getRealPath(), 
                        1024, 
                        'certificates/'.$fileName
                    ));
                }else{
                    $file->storeAs('certificates', $fileName, 's3');
                }

                $validated['internalcertificate-attachment'] = $fileName;
            }

            DB::transaction(function () use ($validated) {
                // Insert the employee data into the employees table
                Employee::saveWorkCertificateInfo($validated);
            });

            return redirect("/employee/work/certificate/{$validated['employee-number']}")->with('success', 'Certificate Info updated successfully!');
            // } catch (QueryException $e) {
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
            // } catch (QueryException $e) {
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
            // Validate the form data
            $validated = $request->validate([
                'contract-id' => 'nullable|numeric',
                'employee-number' => 'required|string|max:20',
                'contract-description' => 'required|string',
                'contract-start-date' => 'required|date',
                'contract-end-date' => 'required|date',
                'contract-attachment.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
                'contract-attachment-current' => 'nullable|array',
                'contract-attachment-current.*' => 'string'
            ]);

            $data = [
                'id' => $validated['contract-id'],
                'emp' => $validated['employee-number'],
                'description' => $validated['contract-description'],
                'start-date' => $validated['contract-start-date'],
                'end-date' => $validated['contract-end-date'],
                'filenames' => null,
                'curfiles' => $validated['contract-attachment-current'] ?? []
            ];

            if ($request->hasFile('contract-attachment')) {
                $uploadedFiles = $request->file('contract-attachment');
                $data['filenames'] = [];
                foreach ($uploadedFiles as $f => $file) {
                    $fileName = $data['emp'] . '_' . time() . ($f ? '(' . $f . ')' : '') . '.' . $file->getClientOriginalExtension();
                    // $file->move($_SERVER['DOCUMENT_ROOT'].'/zen/assets/contract', $fileName);

                    if(in_array(mime_content_type($file->getRealPath()), ['image/jpeg', 'image/png'])){
                        $fileName = basename(reduceImageFileSizeToWebP(
                            's3',
                            $file->getRealPath(), 
                            1024, 
                            'contracts/'.$fileName
                        ));
                    }else{
                        $file->storeAs('contracts', $fileName, 's3');
                    }

                    $data['filenames'][] = $fileName;
                }
            }

            $data['filenames'] = json_encode(array_merge($data['filenames'] ?? [], $data['curfiles'] ?? []));

            DB::transaction(function () use ($data) {
                // Insert the employee data into the employees table
                Contract::store($data);
            });

            return redirect("/employee/work/contracts/{$validated['employee-number']}")->with('success', 'Contract Info updated successfully!');
            // } catch (QueryException $e) {
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
            // } catch (QueryException $e) {
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
            // Validate the form data
            $validated = $request->validate([
                'characterref-id' => 'nullable|numeric',
                'employee-number' => 'required|string|max:20',
                'characterref-name' => 'required|string',
                'characterref-position' => 'nullable|string',
                'characterref-company' => 'nullable|string',
                'characterref-address' => 'required|string',
                'characterref-contact' => 'required|string',
                'characterref-relationship' => 'required|string'
            ]);

            DB::transaction(function () use ($validated) {
                // Insert the employee data into the employees table
                Employee::saveCharacterrefInfo($validated);
            });

            return redirect("/employee/work/characterref/{$validated['employee-number']}")->with('success', 'Character reference Info updated successfully!');
            // } catch (QueryException $e) {
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to update character reference info: ' . $e->getMessage()]);
        }
    }

    /** Remoev employee character reference info */
    public static function removeCharacterrefInfo($empno, $id)
    {
        try {

            Employee::removeCharacterrefInfo($empno, $id);

            return response()->json(['success' => true]);
            // } catch (QueryException $e) {
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

    public static function outgoingList()
    {
        $data = Employee::outgoingList();

        $html = "<table class='table table-bordered table-sm table-hover table-striped' style='width: 100%;'>";
        $html .= "<thead>";
        $html .= "<tr>";
        $html .= "<th>Emp #</th>";
        $html .= "<th>Company</th>";
        $html .= "<th>Department</th>";
        $html .= "<th>Name</th>";
        $html .= "<th>Position</th>";
        $html .= "<th>Last Day</th>";
        $html .= "</tr>";
        $html .= "</thead>";
        $html .= "<tbody>";

        foreach ($data as $v) {
            $html .= "<tr ondblclick=\"viewInfo('" . $v->pers_empno . "')\">";
            $html .= "<td>" . $v->pers_empno . "</td>";
            $html .= "<td>" . $v->C_Name . "</td>";
            $html .= "<td>" . $v->Dept_Name . "</td>";
            $html .= "<td>" . trim(ucwords($v->pers_lastname . ', ' . $v->pers_firstname)) . "</td>";
            $html .= "<td>" . $v->jd_title . "</td>";
            $html .= "<td>" . $v->ji_resdate . "</td>";
            $html .= "</tr>";
        }

        $html .= "</tbody>";

        $html .= "<tfoot>";
        $html .= "<tr>";
        $html .= "<th></th>";
        $html .= "<th></th>";
        $html .= "<th></th>";
        $html .= "<th></th>";
        $html .= "<th></th>";
        $html .= "<th></th>";
        $html .= "</tr>";
        $html .= "</tfoot>";

        $html .= "</table>";

        return $html;
    }

    public static function retentionList($ym)
    {
        $data = collect();
        foreach (Employee::retentionList($ym) as $v) {
            // $v->duration = ($v->ji_datehired ? date('m/d/Y', strtotime($v->ji_datehired)) : 'N/A') . '-' . ($v->ji_resdate ? date('m/d/Y', strtotime($v->ji_resdate)) : 'N/A');

            if(!$data->has($v->C_Code)){
                $data->put($v->C_Code, collect());
            }
            if(!$data[$v->C_Code]->has($v->Dept_Name)){
                $data[$v->C_Code]->put($v->Dept_Name, collect());
            }
            if(date('Y-m', strtotime($v->ji_resdate)) == $ym){
                if(!$data[$v->C_Code][$v->Dept_Name]->has('separated')){
                    $data[$v->C_Code][$v->Dept_Name]->put('separated', collect());
                }
                $data[$v->C_Code][$v->Dept_Name]['separated']->push($v);
            }
            if(date('Y-m', strtotime($v->ji_datehired)) == $ym){
                if(!$data[$v->C_Code][$v->Dept_Name]->has('new')){
                    $data[$v->C_Code][$v->Dept_Name]->put('new', collect());
                }
                $data[$v->C_Code][$v->Dept_Name]['new']->push($v);
            }
            if(date('Y-m', strtotime($v->ji_datehired)) < $ym){
                if(!$data[$v->C_Code][$v->Dept_Name]->has('old')){
                    $data[$v->C_Code][$v->Dept_Name]->put('old', collect());
                }
                $data[$v->C_Code][$v->Dept_Name]['old']->push($v);
            }
        }

        // echo "<pre>";print_r($data);echo "</pre>";exit;

        $html = "<table class='table table-bordered table-sm table-hover' style='width: 100%;'>";
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

        foreach ($data as $c => $cv) {
            $old = $cv->sum(fn ($item) => ($item['old'] ?? null)?->count());
            $new = $cv->sum(fn ($item) => ($item['new'] ?? null)?->count());
            $separated = $cv->sum(fn ($item) => ($item['separated'] ?? null)?->count());
            // $separated_old = $cv->sum(fn ($item) => ($item['separated'] ?? null)?->filter(fn ($i) => date('Y-m', strtotime($i->ji_datehired)) != date('Y-m', strtotime($i->ji_resdate)))->count());
            // $average_employee = ($old + ($old + $new - $separated)) / 2;
            $remaining = ($old + $new - $separated);
            $total = ($old + $new);

            $html .= "<tr class='list1 list-" . $c . "' listsub='" . $c . "'>";
            $html .= "<td colspan=\"2\">" . $c . "</td>";
            $html .= "<td style=\"display: none;\"></td>";
            $html .= "<td>" . $old . "</td>";
            $html .= "<td>" . $new . "</td>";
            $html .= "<td>" . $total . "</td>";
            $html .= "<td>" . $separated . "</td>";
            $html .= "<td>" . $remaining . "</td>";
            $html .= "<td>" . ($total ? round(($remaining ? $separated / $remaining : 0) * 100) . '%' : '-') . "</td>";
            $html .= "<td>" . ($total ? round(($total ? $remaining / $total : 0) * 100) . '%' : '-') . "</td>";
            $html .= "</tr>";

            foreach ($cv as $d => $dv) {
                $old = ($dv['old'] ?? null)?->count();
                $new = ($dv['new'] ?? null)?->count();
                $separated = ($dv['separated'] ?? null)?->count();
                // $separated_old = ($dv['separated'] ?? null)?->filter(fn ($i) => date('Y-m', strtotime($i->ji_datehired)) != date('Y-m', strtotime($i->ji_resdate)))->count();
                // $average_employee = ($old + ($old + $new - $separated)) / 2;
                $remaining = ($old + $new - $separated);
                $total = ($old + $new);

                $html .= "<tr class='list2 list-" . $c . "' listsub='" . $c . str_replace(' ', '', $d) . "' style='display: none;'>";
                $html .= "<td></td>";
                $html .= "<td>" . $d . "</td>";
                $html .= "<td>" . $old . "</td>";
                $html .= "<td>" . $new . "</td>";
                $html .= "<td>" . $total . "</td>";
                $html .= "<td>" . $separated . "</td>";
                $html .= "<td>" . $remaining . "</td>";
                $html .= "<td>" . ($total ? round(($remaining ? $separated / $remaining : 0) * 100) . '%' : '-') . "</td>";
                $html .= "<td>" . ($total ? round(($total ? $remaining / $total : 0) * 100) . '%' : '-') . "</td>";
                $html .= "</tr>";

                $html .= "<tr class='list3 list-" . $c . " list-" . $c . str_replace(' ', '', $d) . "' style='display: none;'>";
                $html .= "<td></td>";
                $html .= "<td></td>";
                $html .= "<td>" . ($dv['old'] ?? null)?->map(fn ($i) => ucwords(trim($i->pers_lastname . ', ' . $i->pers_firstname)))->implode('<br>') . "</td>";
                $html .= "<td>" . ($dv['new'] ?? null)?->map(fn ($i) => ucwords(trim($i->pers_lastname . ', ' . $i->pers_firstname)))->implode('<br>') . "</td>";
                $html .= "<td></td>";
                $html .= "<td>" . ($dv['separated'] ?? null)?->map(fn ($i) => ucwords(trim($i->pers_lastname . ', ' . $i->pers_firstname)))->implode('<br>') . "</td>";
                $html .= "<td></td>";
                $html .= "<td></td>";
                $html .= "<td></td>";
                $html .= "</tr>";
            }
        }

        $html .= "</tbody>";

        // $html .= "<tfoot>";
        // $html .= "<tr>";
        // $html .= "<th></th>";
        // $html .= "<th></th>";
        // $html .= "<th></th>";
        // $html .= "<th></th>";
        // $html .= "<th></th>";
        // $html .= "<th></th>";
        // $html .= "</tr>";
        // $html .= "</tfoot>";

        $html .= "</table>";

        return $html;
    }
}
