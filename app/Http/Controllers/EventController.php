<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public static function index()
    {
        // $user = Auth::user();
        return view('pages.events', [
            'main_link' => 'events',
            'sub_link' => '',
            'user_empno' => Auth::user()->Emp_No,
            'maincat' => 'company',
            'page' => 'pages.event.company-event'
        ]);
    }

    public static function eventList()
    {
        // $user = Auth::user();

        $html = '<table class="table table-sm table-bordered table-hover table-striped">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>Title</th>';
        $html .= '<th class="text-start">Event Date</th>';
        $html .= '<th class="text-start">Event Post Start</th>';
        $html .= '<th class="text-start">Event Post End</th>';
        $html .= '<th>Image</th>';
        $html .= '<th>Timestamp</th>';
        $html .= '<th>Action</th>';
        $html .= '</tr>';
        $html .= '</thead>';

        $html .= '<tbody>';
        foreach (Event::all()->sortBy([
            ['event_date', 'desc'],
            ['event_title', 'asc'],
            ['event_datestart', 'desc'],
            ['event_dateend', 'desc']
        ]) as $v) {
            $html .= '<td>' . $v->event_title . '</td>';
            $html .= '<td class="text-start">' . $v->event_date . '</td>';
            $html .= '<td class="text-start">' . $v->event_datestart . '</td>';
            $html .= '<td class="text-start">' . $v->event_dateend . '</td>';
            $html .= '<td><img src="' . $v->event_file . '" alt="Preview" style="max-width: 100%; max-height: 100px; border: 1px solid #ccc; border-radius: 5px;"></td>';
            $html .= '<td>' . $v->dateposted . '</td>';
            $html .= '<td>';
            $html .= '<button class="btn btn-sm btn-outline-secondary"
                        data-bs-toggle="modal" 
                        data-bs-target="#modal-company-event"
                        data-id="'.$v->event_id.'"
                        data-title="'.$v->event_title.'"
                        data-event-date="'.$v->event_date.'"
                        data-post-start="'.$v->event_datestart.'"
                        data-post-end="'.$v->event_dateend.'"><i class="fa fa-edit"></i></button>';

            $html .= '<button class="btn btn-sm btn-outline-danger" onclick="remove_event('.$v->event_id.')"><i class="fa fa-times"></i></button>';
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
                'title' => 'required|string',
                'event-date' => 'required|date',
                'post-start-date' => 'required|date',
                'post-end-date' => 'required|date',
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
                
                $validated['filename'] = $fileName;
            }

            $data = [
                'event_postby' => Auth::user()->Emp_No,
                'event_title' => $validated['title'],
                'event_date' => $validated['event-date'],
                'event_datestart' => $validated['post-start-date'],
                'event_dateend' => $validated['post-end-date'],
            ];

            if($validated['filename']){
                $data['event_file'] = $validated['filename'];
            }

            if($validated['id']){
                Event::where('event_id', $validated['id'])->update($data);
            }else{
                Event::insert($data);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }

    public static function delete($id)
    {
        try {
            Event::where('event_id', $id)->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed: ' . $e->getMessage()]);
        }
    }
}