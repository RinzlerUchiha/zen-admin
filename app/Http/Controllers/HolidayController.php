<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HolidayController extends Controller
{
    public static function index()
    {
        // $user = Auth::user();
        return view('pages.events', [
            'main_link' => 'events',
            'sub_link' => '',
            'user_empno' => Auth::user()->Emp_No,
            'maincat' => 'holiday',
            'page' => 'pages.event.holiday',
            'area' => Setting::areaList()
        ]);
    }

    public static function list()
    {
        // $user = Auth::user();
        $html = '<table class="table table-sm table-bordered table-hover table-striped" style="width: 100%;">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>Holiday</th>';
        $html .= '<th class="text-start">Date</th>';
        $html .= '<th>Type</th>';
        $html .= '<th>Scope</th>';
        $html .= '<th>Image</th>';
        $html .= '<th>Action</th>';
        $html .= '</tr>';
        $html .= '</thead>';

        $html .= '<tbody>';
        foreach (Holiday::all()->sortBy([
            ['date', 'desc'],
            ['holiday', 'asc']
        ]) as $v) {
            $html .= '<td>' . $v->holiday . '</td>';
            $html .= '<td class="text-start text-nowrap">' . $v->date . '</td>';
            $html .= '<td>' . $v->holiday_type . '</td>';
            $html .= '<td>' . str_replace(',', ', ', $v->holiday_scope) . '</td>';
            $html .= '<td>'.($v->holiday_img ? '<img src="' . $v->holiday_img . '" alt="Preview" style="max-width: 100%; max-height: 150px; border: 1px solid #ccc; border-radius: 5px;">' : '').'</td>';
            $html .= '<td class="text-nowrap">';
            $html .= '<button class="mx-1 btn btn-sm btn-outline-secondary"
                        data-bs-toggle="modal" 
                        data-bs-target="#modal-holiday"
                        data-id="'.$v->id.'"
                        data-holiday="'.$v->holiday.'"
                        data-date="'.$v->date.'"
                        data-type="'.$v->holiday_type.'"
                        data-scope="'.$v->holiday_scope.'"><i class="fa fa-edit"></i></button>';

            $html .= '<button class="mx-1 btn btn-sm btn-outline-danger" onclick="remove_holiday('.$v->id.')"><i class="fa fa-times"></i></button>';
            $html .= '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        $html .= '</table>';

        return $html;
    }

    public static function store(Request $request)
    {
        try {
            // Validate the form data
            $validated = $request->validate([
                'id' => 'nullable|numeric',
                'holiday' => 'required|string',
                'date' => 'required|date',
                'type' => 'required|string',
                'scope' => 'required|string',
                'file' => 'nullable|file|mimetypes:image/*',
            ]);

            $validated['filename'] = '';
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = time() . '_' . str_replace(',', ' ', $file->getClientOriginalName());
                // $file->storeAs('announcement', $fileName, 'custom_s3');
                // $file->move($_SERVER['DOCUMENT_ROOT'].'/zen/assets/events', $fileName);
                // $file->storeAs('events', $fileName, 's3');

                if(in_array(mime_content_type($file->getRealPath()), ['image/jpeg', 'image/png'])){
                    $fileName = basename(reduceImageFileSizeToWebP(
                        's3',
                        $file->getRealPath(), 
                        1024, 
                        'events/'.$fileName
                    ));
                }else{
                    $file->storeAs('events', $fileName, 's3');
                }

                // $validated['filename'] = '/zen/assets/events/'.$fileName;
                $validated['filename'] = $fileName;
            }

            $data = [
                'holiday' => $validated['holiday'],
                'date' => $validated['date'],
                'holiday_type' => $validated['type'],
                'holiday_scope' => $validated['scope'],
            ];

            if($validated['filename']){
                $data['holiday_img'] = $validated['filename'];
            }

            if($validated['id']){
                Holiday::where('id', $validated['id'])->update($data);
            }else{
                Holiday::insert($data);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function delete($id)
    {
        try {
            Holiday::where('id', $id)->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }
}
