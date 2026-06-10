<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ContractController extends Controller
{
    public static function loadList()
    {
        $user = Auth::user();
        return view('pages.contracts', [
            'main_link' => 'contracts',
            'sub_link' => '',
            'list' => Contract::getList(),
            'user_empno' => $user->Emp_No,
            'employees' => collect(json_decode(json_encode(Employee::employeeList()->toArray()), true))
                ->mapWithKeys(function ($item) {
                    return [$item['pers_empno'] => $item];
                })
        ]);
    }

    public static function store(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'nullable|numeric',
                'emp' => 'required|string',
                'description' => 'required|string',
                'start-date' => 'required|date',
                'end-date' => 'required|date',
                'files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
                'curfiles' => 'required|string'
            ]);

            $validated['curfiles'] = json_decode($validated['curfiles'], true);

            if ($request->hasFile('files')) {
                $uploadedFiles = $request->file('files');
                $validated['filenames'] = [];
                foreach ($uploadedFiles as $f => $file) {
                    // $fileName = time() . '_' . $file->getClientOriginalName();
                    // $file->storeAs('contracts', $fileName, 'custom_s3');
                    $fileName = $validated['emp'] . '_' . time() . ($f ? '(' . $f . ')' : '') . '.' . $file->getClientOriginalExtension();
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

                    $validated['filenames'][] = $fileName;
                }
            }

            $validated['filenames'] = json_encode(array_merge($validated['filenames'] ?? [], $validated['curfiles'] ?? []));
            $validated['id'] = Contract::store($validated);

            return response()->json(['success' => true, 'data' => $validated]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function serveAttachment($filename)
    {
        // Check if the user is authorized to access the file
        if (Auth::check()) {
            // Check if the file exists in the private disk
            if (Storage::disk('s3')->exists('contracts/' . $filename)) {
                // Get the file contents
                $file = Storage::disk('s3')->get('contracts/' . $filename);

                return response($file, 200)
                    // ->header('Content-Type', 'image/jpeg')  // Adjust the MIME type based on your file
                    ->header('Content-Type', 'application/octet-stream')
                    ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
            }
        }

        // If not authorized or the file doesn't exist, return a 404 response
        abort(404, 'File not found or unauthorized');
    }

    public static function delete($id)
    {
        try {
            Contract::destroy($id);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }
}
