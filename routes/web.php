<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\Applicant\ApplicantProfileController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClearanceController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EEIController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ExitInterviewController;
use App\Http\Controllers\Grievance13AController;
use App\Http\Controllers\Grievance13BController;
use App\Http\Controllers\GrievanceIRController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\KamustahanController;
use App\Http\Controllers\ManpowerRequestController;
use App\Http\Controllers\MemoController;
use App\Http\Controllers\PAController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\Recruitment\JobPostingController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Requests\SubmitRequest;
use App\Models\ExitInterview;
use App\Models\GrievanceIR;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/hash-all-pw', function () {
//     set_time_limit(300);
//     $users = User::where('U_Password_hashed', '')
//     ->whereNull('U_Password_hashed')
//     ->get();

//     foreach ($users as $user) {
//         // Hash the existing password and store it in pw_hashed
//         $user->U_Password_hashed = Hash::make($user->U_Password);
//         $user->save();
//         echo "Hashed password for user: {$user->U_ID} <br>";
//     }

//     return $users->count().' changed';
// });

// User::insertOrIgnore([
//     ['Emp_No' => '045-2001-001', 
//     'U_Password' => '123', 
//     'U_Password_hashed' => Hash::make('123'), 
//     'U_Name' => 'Arnold Infante', 
//     'U_Remarks' => 'Active'],
// ]);

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return view('pages.dashboard3', ['main_link' => 'dashboard', 'sub_link' => 'dashboard']);
    })->name('dashboard');
    Route::get('/dashboard/ir', [DashboardController::class, 'getIR']);
    Route::get('/dashboard/13a', [DashboardController::class, 'get13A']);
    Route::get('/dashboard/13b', [DashboardController::class, 'get13B']);

    Route::get('/dashboard/counters', [DashboardController::class, 'getCounters']);
    Route::get('/dashboard/pa/{ym}', [DashboardController::class, 'getPA']);
    Route::get('/dashboard/manpower', [DashboardController::class, 'getManpowerRequest']);
    Route::get('/dashboard/clearance', [DashboardController::class, 'getClearance']);
    Route::get('/dashboard/exit-interview', [DashboardController::class, 'getExitInterview']);
    Route::get('/dashboard/timeoff', [DashboardController::class, 'getTimeoff']);
    Route::get('/dashboard/travel', [DashboardController::class, 'getTravel']);
    Route::get('/dashboard/memo', [DashboardController::class, 'getMemo']);
    Route::get('/dashboard/retention', [DashboardController::class, 'getRetention6Months']);
    Route::get('/dashboard/probationary', [DashboardController::class, 'getProbationary']);
    Route::get('/dashboard/academy', [DashboardController::class, 'getAcademy']);

    Route::get('/dashboard2', function () {
        return view('pages.dashboard2', ['main_link' => 'dashboard', 'sub_link' => 'dashboard']);
    });

    Route::get('/dashboard3', function () {
        return view('pages.dashboard3', ['main_link' => 'dashboard', 'sub_link' => 'dashboard']);
    });


    Route::get('/new-empno', [EmployeeController::class, 'generateEmpNo'])->name('generateEmpNo');

    Route::get('/employee/new', [EmployeeController::class, 'newEmployee']);
    Route::get('/employee', [EmployeeController::class, 'showInfo']);
    Route::get('/employee/{maincat}/{subcat}/{empno?}', [EmployeeController::class, 'showInfo']);

    Route::get('/employee/personality/get-enneagram-result/{id}', function ($id) {
        return $id;
    });



    Route::get('/report/{tab?}', function ($tab = 'eei') {
        $params = ['main_link' => 'report', 'sub_link' => '', 'maincat' => $tab, 'page' => "pages.report.{$tab}"];
        return view('pages.report', $params);
    });

    Route::get('/report/retention/list/{ym}', [EmployeeController::class, 'retentionList']);
    Route::get('/report/pa/list/{year}', [PAController::class, 'loadList']);
    Route::get('/report/eei/list/{ym}', [EEIController::class, 'loadList']);
    Route::get('/report/outgoing/list', [EmployeeController::class, 'outgoingList']);
    Route::get('/exit-interview/list/{empno}', [ExitInterviewController::class, 'showListByEmpNo']);
    Route::get('/exit-interview/info/{id}', [ExitInterviewController::class, 'showInfo']);
    Route::get('/exit-interview/new/{empno}', [ExitInterviewController::class, 'newInterview']);
    Route::post('/exit-interview/save', [ExitInterviewController::class, 'store']);
    Route::post('/exit-interview/sign', [ExitInterviewController::class, 'sign']);



    Route::get('/kamustahan', [KamustahanController::class, 'loadList']);
    Route::prefix('kamustahan')->group(function () {
        Route::get('list/{empno}', [KamustahanController::class, 'loadList']);
        Route::get('info/{id?}', [KamustahanController::class, 'show']);
        Route::post('save', [KamustahanController::class, 'store']);
        Route::post('remark/save', [KamustahanController::class, 'storeRemark']);
    });



    Route::get('/applicant', function () {
        return redirect()->route('applicant.index', [], 302);
    });
    Route::prefix('applicant')
        ->name('applicant.')
        ->group(function () {
            Route::get('list', [ApplicantProfileController::class, 'index'])->name('index');
            Route::get('info/{id}/{tab?}', [ApplicantProfileController::class, 'show'])->name('show');
            Route::get('form/hire/{id}', [ApplicantProfileController::class, 'showFormHireContent'])->name('form.hire');
            Route::post('hire/{id}', [ApplicantProfileController::class, 'hire'])->name('hire');
            Route::post('interview-details/save/{id}', [ApplicantProfileController::class, 'saveInterviewDetails'])->name('interview.save');
        });



    Route::prefix('grievance')->group(function () {
        Route::get('{tab?}', function ($tab = 'ir') {
            return view('pages.grievance', ['main_link' => 'grievance', 'sub_link' => '', 'maincat' => $tab, 'page' => "pages.grievance.{$tab}-list"]);
        }); // transfer to GrievanceIRController@index

        // ir
        Route::get('ir/print/{id}', [GrievanceIRController::class, 'printIR']);

        Route::get('ir/list/{stat}', [GrievanceIRController::class, 'loadList']);
        Route::get('ir/view/{id?}', [GrievanceIRController::class, 'show']);
        Route::get('ir/notifications', [GrievanceIRController::class, 'getNotification']);

        Route::post('ir/save', [GrievanceIRController::class, 'saveIR']);
        Route::post('ir/witness/save', [GrievanceIRController::class, 'saveIRWitness']);
        Route::post('ir/attachment/save', [GrievanceIRController::class, 'saveIRAttachment']);
        Route::post('ir/explanation/save', [GrievanceIRController::class, 'saveIRExplanation']);
        Route::post('ir/meeting/save', [GrievanceIRController::class, 'saveIRMeeting']);
        Route::post('ir/forward/save', [GrievanceIRController::class, 'saveIRForward']);
        Route::post('ir/resolve/save', [GrievanceIRController::class, 'saveIRResolve']);
        Route::post('ir/sign', [GrievanceIRController::class, 'saveIRSign']);

        Route::delete('ir/attachment/delete/{ir}/{id}', [GrievanceIRController::class, 'deleteIRAttachment']);
        Route::delete('ir/delete/{id}', [GrievanceIRController::class, 'deleteIR']);

        // 13a
        Route::get('13a/list/{stat}', [Grievance13AController::class, 'loadList']);
        Route::get('13a/view/{id?}', [Grievance13AController::class, 'show']);
        Route::get('13a/notifications', [Grievance13AController::class, 'getNotification']);

        Route::post('13a/save', [Grievance13AController::class, 'save13A']);
        Route::post('13a/set/notedby', [Grievance13AController::class, 'save13ANotedBy']);
        Route::post('13a/set/issuedby', [Grievance13AController::class, 'save13AIssuedBy']);
        Route::post('13a/set/witness', [Grievance13AController::class, 'save13AWitness']);
        Route::post('13a/set/hearing', [Grievance13AController::class, 'save13AHearing']);
        Route::post('13a/set/ir', [Grievance13AController::class, 'save13AIR']);
        Route::post('13a/check', [Grievance13AController::class, 'check13A']);
        Route::post('13a/sign', [Grievance13AController::class, 'sign13A']);
        Route::post('13a/explanation', [Grievance13AController::class, 'explain13A']);
        Route::post('13a/issue', [Grievance13AController::class, 'issue13A']);
        Route::post('13a/refuse', [Grievance13AController::class, 'refuse13A']);
        Route::post('13a/cancel', [Grievance13AController::class, 'cancel13A']);

        Route::delete('13a/delete/ir/{id}/{ir}', [Grievance13AController::class, 'delete13AIR']);
        Route::delete('13a/delete/{id}', [Grievance13AController::class, 'delete13A']);

        Route::get('transcript/view/{id?}', [Grievance13AController::class, 'showTranscript']);
        Route::post('transcript/save', [Grievance13AController::class, 'saveTranscript']);
        Route::post('transcript/sign', [Grievance13AController::class, 'signTranscript']);
        Route::delete('transcript/delete/{id13a}', [Grievance13AController::class, 'deleteTranscript']);

        Route::get('commitment/view/{id13a}', [Grievance13AController::class, 'showCommitmentPlan']);
        Route::post('commitment/save', [Grievance13AController::class, 'saveCommitmentPlan']);
        Route::post('commitment/sign', [Grievance13AController::class, 'signCommitmentPlan']);

        Route::get('reply/view/{id13a}', [Grievance13AController::class, 'showLetterOfReply']);
        Route::post('reply/save', [Grievance13AController::class, 'saveLetterOfReply']);



        // 13b
        Route::get('13b/list/{stat}', [Grievance13BController::class, 'loadList']);
        Route::get('13b/view/{id?}', [Grievance13BController::class, 'show']);
        Route::get('13b/notifications', [Grievance13BController::class, 'getNotification']);

        Route::post('13b/save', [Grievance13BController::class, 'save13B']);
        Route::post('13b/set/witness', [Grievance13BController::class, 'save13BWitness']);
        Route::post('13b/cancel', [Grievance13BController::class, 'cancel13B']);
        Route::post('13b/sign', [Grievance13BController::class, 'sign13B']);
        Route::post('13b/issue', [Grievance13BController::class, 'issue13B']);
        Route::post('13b/refuse', [Grievance13BController::class, 'refuse13B']);

        Route::delete('13b/delete/{id}', [Grievance13BController::class, 'delete13B']);
    });



    Route::get('/contracts', [ContractController::class, 'loadList']);
    Route::post('/contracts/save', [ContractController::class, 'store']);
    Route::get('/contracts/file/{filename}', [ContractController::class, 'serveAttachment']);
    Route::delete('/contracts/{id}', [ContractController::class, 'delete']);



    Route::get('/announcement/{type?}', [AnnouncementController::class, 'index']);
    Route::get('/announcement/list/{type}/{offset}', [AnnouncementController::class, 'showList']);
    Route::post('/announcement/save', [AnnouncementController::class, 'store']);
    Route::post('/announcement/comment/save', [AnnouncementController::class, 'storeComment']);
    Route::post('/announcement/report/deny/{id}', [AnnouncementController::class, 'denyReport']);
    Route::post('/announcement/reaction', [AnnouncementController::class, 'storeReaction']);
    Route::delete('/announcement/delete/{id}/{type}', [AnnouncementController::class, 'delete']);


    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/company', [EventController::class, 'index']);
    Route::get('/events/company/list', [EventController::class, 'eventList']);
    Route::post('/events/company/save', [EventController::class, 'store']);
    Route::delete('/events/company/delete/{id}', [EventController::class, 'delete']);

    Route::get('/events/holiday', [HolidayController::class, 'index']);
    Route::get('/events/holiday/list', [HolidayController::class, 'list']);
    Route::post('/events/holiday/save', [HolidayController::class, 'store']);
    Route::delete('/events/holiday/delete/{id}', [HolidayController::class, 'delete']);



    Route::get('/memo', [MemoController::class, 'index']);
    Route::get('/memo/list', [MemoController::class, 'list']);
    Route::post('/memo/save', [MemoController::class, 'store']);
    Route::post('/memo/read/{id}', [MemoController::class, 'readMemo']);
    Route::delete('/memo/delete/{id}', [MemoController::class, 'delete']);



    Route::get('/recruitment', function () {
        return redirect()->route('recruitment.manpower.index');
    });

    Route::prefix('recruitment/manpower')->name('recruitment.manpower.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Recruitment\ManpowerController::class, 'index'])->name('index');
        Route::get('/list/{stat}', [\App\Http\Controllers\Recruitment\ManpowerController::class, 'list'])->name('list');
        Route::get('/counts', [\App\Http\Controllers\Recruitment\ManpowerController::class, 'counts'])->name('counts');
        Route::get('/{id}', [\App\Http\Controllers\Recruitment\ManpowerController::class, 'show'])->name('show');
    });

    Route::prefix('recruitment/job-postings')->name('recruitment.job-postings.')->group(function () {
        Route::get('/', [JobPostingController::class, 'index'])->name('index');
        Route::get('/draft/{position}', [JobPostingController::class, 'draft'])->name('draft');
        Route::get('/{jobPosting}', [JobPostingController::class, 'show'])->name('show');
        Route::post('/', [JobPostingController::class, 'store'])->name('store');
        Route::patch('/{jobPosting}/status', [JobPostingController::class, 'updateStatus'])->name('update-status');
    });

    Route::prefix('recruitment/applicant-intake')->name('recruitment.applicant-intake.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Recruitment\ApplicantIntakeController::class, 'index'])->name('index');
        Route::get('/data', [\App\Http\Controllers\Recruitment\ApplicantIntakeController::class, 'data'])->name('data');
        Route::get('/counts', [\App\Http\Controllers\Recruitment\ApplicantIntakeController::class, 'counts'])->name('counts');
    });

    Route::get('/manpower', [ManpowerRequestController::class, 'index']);
    Route::get('/manpower/jobspec/{pos}', [ManpowerRequestController::class, 'viewSpec']);
    Route::get('/manpower/applicant/{id}/interviews', [ManpowerRequestController::class, 'applicantInterviews']);
    Route::get('/manpower/list/{stat}', [ManpowerRequestController::class, 'showList']);
    Route::get('/manpower/counts', [ManpowerRequestController::class, 'counts']);
    Route::post('/manpower/save', [ManpowerRequestController::class, 'store']);
    Route::post('/manpower/fill', [ManpowerRequestController::class, 'fillRequest']);
    Route::post('/manpower/stat', [ManpowerRequestController::class, 'updateStat']);
    Route::post('/manpower/update', [ManpowerRequestController::class, 'updateRequest']);
    Route::post('/manpower/update/approve/{id}', [ManpowerRequestController::class, 'approveUpdate']);
    Route::post('/manpower/update/decline/{id}', [ManpowerRequestController::class, 'declineUpdate']);
    Route::delete('/manpower/delete/{id}', [ManpowerRequestController::class, 'delete']);
    Route::post('/manpower/jobspec/save', [ManpowerRequestController::class, 'saveSpec']);
    Route::get('/manpower/jobspec/{id}', [ManpowerRequestController::class, 'viewSpec']);



    Route::get('/clearance/{page?}', [ClearanceController::class, 'index']);
    Route::get('/clearance/info/{id}', [ClearanceController::class, 'showInfoById']);
    Route::get('/clearance/list/{stat}', [ClearanceController::class, 'showList']);
    Route::get('/clearance/cat/{company}/{id?}', [ClearanceController::class, 'getCat']);
    Route::get('/clearance/cat-details/{id}', [ClearanceController::class, 'getCatDetailsByRequestId']);
    Route::post('/clearance/save', [ClearanceController::class, 'store']);
    Route::post('/clearance/requirements', [ClearanceController::class, 'checkRequirements']);
    Route::get('/clearance/settings/{company}', [ClearanceController::class, 'getCatWithRequirements']);
    Route::post('/clearance/set/cat', [ClearanceController::class, 'setCategory']);
    Route::post('/clearance/set/req', [ClearanceController::class, 'setRequirement']);
    Route::get('/clearance/print/{id}/{type}', [ClearanceController::class, 'printClearance']);
    Route::get('/clearance/attachment/list/{id}', [ClearanceController::class, 'getAttachments']);
    Route::post('/clearance/attachment/save', [ClearanceController::class, 'storeAttachment']);
    Route::delete('/clearance/attachment/{id}', [ClearanceController::class, 'removeAttachment']);



    Route::get('/admin', function () {
        return view('pages.admin', ['main_link' => 'admin', 'sub_link' => '', 'maincat' => 'admin']);
    });


    Route::get('/maintenance/{type?}', [SettingController::class, 'index']);
    Route::get('/maintenance/{type}/list/{extra?}', [SettingController::class, 'showList']);
    Route::post('/maintenance/{type}/save', [SettingController::class, 'saveSetting']);
    Route::delete('/maintenance/{type}/{id}', [SettingController::class, 'delSetting']);


    // Route::post('/new/employee/submit', function (Request $request) {
    //     return $request;
    // });

    // *** might need to add 'put' as a standard for updating record instead of using only 'post'
    Route::post('/new/employee/submit', [EmployeeController::class, 'createEmployee'])->name('save_new_employee');
    Route::post('/save/profile/personal', [EmployeeController::class, 'savePersonalInfo']);
    Route::post('/save/profile/img', [EmployeeController::class, 'savePersonalImg']);
    Route::post('/save/work/jobinfo', [EmployeeController::class, 'saveJobInfo']);
    Route::post('/save/work/jobrec', [EmployeeController::class, 'saveJobRecord']);
    Route::post('/save/profile/family', [EmployeeController::class, 'saveFamilyInfo']);
    Route::post('/save/profile/skills', [EmployeeController::class, 'saveSkillsInfo']);
    Route::post('/save/profile/education', [EmployeeController::class, 'saveEducationInfo']);
    Route::post('/save/professional/license', [EmployeeController::class, 'saveLicenseInfo']);
    Route::post('/save/professional/certificate', [EmployeeController::class, 'saveEduCertificateInfo'])->name('save_professional_cert');
    Route::post('/save/work/employment', [EmployeeController::class, 'saveEmploymentInfo']);
    Route::post('/save/work/certificate', [EmployeeController::class, 'saveWorkCertificateInfo']);
    Route::post('/save/work/characterref', [EmployeeController::class, 'saveCharacterrefInfo']);
    Route::post('/save/work/contract', [EmployeeController::class, 'saveContractInfo']);


    Route::delete('/remove/profile/family/{empno}/{id}', [EmployeeController::class, 'removeFamilyInfo']);
    Route::delete('/remove/profile/skills/{empno}/{id}', [EmployeeController::class, 'removeSkillsInfo']);
    Route::delete('/remove/profile/education/{empno}/{id}', [EmployeeController::class, 'removeEducationInfo']);
    Route::delete('/remove/professional/license/{empno}/{id}', [EmployeeController::class, 'removeLicenseInfo']);
    Route::delete('/remove/professional/certificate/{empno}/{id}', [EmployeeController::class, 'removeEduCertificateInfo']);
    Route::delete('/remove/work/jobrec/{empno}/{id}', [EmployeeController::class, 'removeJobRecord']);
    Route::delete('/remove/work/employment/{empno}/{id}', [EmployeeController::class, 'removeEmploymentInfo']);
    Route::delete('/remove/work/certificate/{empno}/{id}', [EmployeeController::class, 'removeWorkCertificateInfo']);
    Route::delete('/remove/work/characterref/{empno}/{id}', [EmployeeController::class, 'removeCharacterrefInfo']);
    Route::delete('/remove/work/contract/{id}', [EmployeeController::class, 'removeContractInfo']);


    // Route::get('/private-images/{filename}', [FileController::class, 'servePrivateFile'])->name('private.image');
    // Route::get('/grievance/ir/file/{filename}', [GrievanceIRController::class, 'serveAttachment']);
    Route::get('/grievance/ir/file/{filename}', function ($filename) {
        return FileController::serveFileFromS3('ir', $filename);
    });

    Route::get('/profile/img/{filename}', function ($filename) {
        return FileController::serveFileFromS3('emp-img', $filename);
    });

    Route::get('/file/get/{src}/{filename}', [FileController::class, 'serveFileFromS3']);
    Route::get('/file/get/applicant/{src}/{filename}', [FileController::class, 'serveFileFromS3ForApplicant'])->name('applicant.file');
});

// Route::get('/employee/{maincat}/{subcat}', function ($maincat, $subcat) {
//     return view('pages/'.$maincat.'/'.$subcat, [
//         'maincat' => $maincat, 
//         'subcat' => $subcat,
//         'page' => 'pages/'.$maincat.'/'.$subcat
//     ]);
// });