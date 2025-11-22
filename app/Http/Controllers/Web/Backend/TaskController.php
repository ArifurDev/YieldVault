<?php

namespace App\Http\Controllers\Web\Backend;

use App\Enum\Task\MediaType;
use App\Enum\Task\Type;
use App\Helpers\helpers;
use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class TaskController extends Controller
{
    /**
     * list of all task
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Task::latest();

            return DataTables::of($data)
//                ->addIndexColumn()
                // TITLE
                ->addColumn('title', function ($row) {
                    return '<span class="fw-bold text-gray-800">'.e($row->title).'</span>';
                })
                // TASK TYPE
                ->addColumn('type', function ($row) {
                    return '<span class="badge badge-light-primary">'
                        . ucfirst(str_replace('_',' ', $row->type->value)) .
                        '</span>';
                })

                // DURATION
                ->addColumn('duration', function ($row) {
                    return $row->duration ? $row->duration . ' sec' : '-';
                })
                // XP
                ->addColumn('xp', function ($row) {
                    return '<span class="badge badge-light-info">'.$row->xp.' XP</span>';
                })
                ->addColumn('status', function ($row) {
                    $status = (int) $row->is_active; // cast to int (0/1)

                    $badgeClass = match($status) {
                        1 => 'badge-light-success',
                        0 => 'badge-light-danger',
                        default => 'badge-light-warning',
                    };
                    $label = $status ? 'Active' : 'Inactive';
                    return '<span class="badge ' . $badgeClass . '">' . $label . '</span>';
                })

                ->addColumn('actions', function ($row) {
                    $editUrl = route('tasks.edit', $row->id);
                    return '
                            <div class="d-flex justify-content-end">
                                <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm"
                                   data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                    Actions
                                    <i class="ki-duotone ki-down fs-5 ms-1"></i>
                                </a>
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4"
                                     data-kt-menu="true">
                                    <div class="menu-item px-3">
                                       <a href="' . $editUrl . '"  class="menu-link px-3">Verify KYC</a>
                                    </div>
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3 delete-kyc-btn" data-id="' . $row->id . '">Delete</a>
                                    </div>
                                </div>
                            </div>
                            ';
                })
                ->rawColumns(['title','type','duration','xp','actions','status'])
                ->make(true);
        }

        // ONLY when not ajax
        return view('backend.layout.task.index');

    }

    /**
     * show task create page
     */
    public function create()
    {
        return view('backend.layout.task.create',[
            'taskTypes'  => Type::cases(),
            'mediaTypes' => MediaType::cases(),
        ]);
    }

    /**
     * store data in database
     */

    public function store(Request $request)
    {
        // VALIDATION
        $validation = $request->validate([
            'title'        => 'required|string|max:255',
            'type'         => 'required|in:watch_ad,upload,click,share,invite,custom',
            'xp'           => 'required|integer|min:0',

            // duration only for watch_ad
            'duration'     => 'required_if:type,watch_ad|nullable|integer|min:1',

            // media handling
            'media_type'   => 'required|in:video,image,url,none',
            'media'        => 'nullable|file|max:50000', // 50 MB
            'media_url'    => 'nullable|url',
        ]);

        /*  MEDIA HANDLING LOGIC */
        $mediaPath = null;
        $mediaType = $validation['media_type'];

        switch ($validation['media_type']) {

            case 'video':
            case 'image':
                if ($request->hasFile('media')) {
                    [$mediaPath, $mediaType] = $this->uploadMedia($request->file('media'));
                }
                break;

            case 'url':
                $mediaPath = $validation['media_url'];
                $mediaType = $validation['media_type'];
                break;

            case 'none':
            default:
                $mediaPath = null;
                $mediaType = $validation['media_type'];
                break;
        }

        /* ------------------------------- STORE DATA IN DATABASE -------------------------------- */
        Task::create([
            'title'       => $validation['title'],
            'type'        => $validation['type'],
            'duration'    => $validation['type'] === 'watch_ad' ? $validation['duration'] : null,
            'xp'          => $validation['xp'],
            'media_type'  => $mediaType,
            'media_path'  => $mediaPath,
            'is_active'   => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully.',
        ]);
    }

    /**
     * Show edit task page
     */
    public function edit($id)
    {
        $task = Task::findOrFail($id);

        // Because media_type is enum, use ->value
        if ($task->media_type->value === 'video' || $task->media_type->value === 'image') {

            $task->media_path = $task->media_path
                ? helpers::generateTempURL($task->media_path, config('app.file_system'))
                : null;
        }

        return view('backend.layout.task.edit', [
            'task'       => $task,
            'taskTypes'  => Type::cases(),
            'mediaTypes' => MediaType::cases(),
        ]);
    }

    /**
     * Update Task
     */
    public function update(Request $request, $id)
    {
            $task = Task::findOrFail($id);

            // VALIDATION
            $validation = $request->validate([
                'title'        => 'required|string|max:255',
                'type'         => 'required|in:watch_ad,upload,click,share,invite,custom',
                'xp'           => 'required|integer|min:0',

                'duration'     => 'required_if:type,watch_ad|nullable|integer|min:1',

                'media_type'   => 'required|in:video,image,url,none',
                'media'        => 'nullable|file|max:50000', // 50MB
                'media_url'    => 'nullable|url',
            ]);

            $mediaPath = $task->media_path;  // old path
            $mediaType = $validation['media_type'];

            /* ---------------- MEDIA HANDLING ---------------- */
            switch ($validation['media_type']) {

                case 'video':
                case 'image':
                    if ($request->hasFile('media')) {
                        // delete old media if exists
                        if ($task->media_path) {
                            helpers::deleteFile($task->media_path, config('app.file_system'));
                        }

                        // upload new file
                        [$mediaPath, $mediaType] = $this->uploadMedia($request->file('media'));
                    }
                    break;

                case 'url':
                    $mediaPath = $validation['media_url'];
                    $mediaType = 'url';
                    break;

                case 'none':
                default:
                    // remove old media
                    if ($task->media_path) {
                        Helpers::deleteFile($task->media_path, config('app.file_system'));
                    }
                    $mediaPath = null;
                    $mediaType = 'none';
                    break;
            }

            /* ---------------- UPDATE DATABASE ---------------- */
            $task->update([
                'title'       => $validation['title'],
                'type'        => $validation['type'],
                'duration'    => $validation['type'] === 'watch_ad' ? $validation['duration'] : null,
                'xp'          => $validation['xp'],
                'media_type'  => $mediaType,
                'media_path'  => $mediaPath,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully.',
            ]);
    }




    /**
     * upload Media
     */
    private function uploadMedia($file)
    {
        if (!$file || !$file->isValid()) {
            return [null,null];
        }

        // Determine MIME by extension
        $ext       = strtolower($file->getClientOriginalExtension());
        $mediaType = in_array($ext, ['jpg','jpeg','png','webp','gif']) ? 'image' : 'video';

        $mediaFile = time() . Str::random(10) . Str::uuid() . '_' . date('Ymd_His') . '.' . $file->getClientOriginalExtension();
        Log::info($mediaFile);
        $mediaPath = Helpers::uploadFile('task',$file,$mediaFile,config('app.file_system')); // store url

        Log::info($mediaPath);
        return [$mediaPath, $mediaType];
    }
}
