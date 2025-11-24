<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\DailyTask;
use App\Models\Task;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DailyTaskController extends Controller
{
    /**
     * list of all task
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = DailyTask::with('task')->orderBy('date')->latest();

            return DataTables::of($data)
//                ->addIndexColumn()
                // TITLE (from related task)
                ->addColumn('title', function ($row) {
                    return '<span class="fw-bold text-gray-800">' . e($row->task->title ?? '-') . '</span>';
                })
                // TASK TYPE (task enum ->value)
                ->addColumn('type', function ($row) {
                    $type = $row->task->type ?? null;
                    $typeLabel = $type && is_object($type) ? $type->value : ($type ?? '-');
                    return '<span class="badge badge-light-primary">' . ucfirst(str_replace('_', ' ', $typeLabel)) . '</span>';
                })
                ->addColumn('date', function ($row) {
                    return $row->date ? $row->date->format('Y-m-d') : '-';
                })
                ->addColumn('sort_order', function ($row) {
                    return (int) $row->sort_order;
                })
                // STATUS (daily_task active/inactive)
                ->addColumn('status', function ($row) {
                    $status = (int) $row->is_active;
                    $badgeClass = match($status) {
                        1 => 'badge-light-success',
                        0 => 'badge-light-danger',
                        default => 'badge-light-warning',
                    };
                    $label = $status ? 'Active' : 'Inactive';
                    return '<span class="badge ' . $badgeClass . '">' . $label . '</span>';
                })

                ->addColumn('actions', function ($row) {
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
                                       <a href="#"  class="menu-link edit-row px-3" data-id="'.$row->id.'">Edit</a>
                                    </div>
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3 delete-kyc-btn" data-id="' . $row->id . '">Delete</a>
                                    </div>
                                </div>
                            </div>
                            ';
                })
                ->rawColumns(['title','type','date','sort_order','actions','status'])
                ->make(true);
        }

        // ONLY when not ajax
        return view('backend.layout.daily_task.index');

    }

    /**
     * Show assign page (all tasks + assign option)
     */
    public function assign(Request $request)
    {
        if ($request->ajax()) {

            $data = Task::orderBy('title')->get();

            return DataTables::of($data)

                ->addColumn('title', fn($row) =>
                    '<span class="fw-bold text-gray-800">'.e($row->title).'</span>'
                )

                ->addColumn('type', fn($row) =>
                    '<span class="badge badge-light-primary">'.
                    ucfirst(str_replace('_',' ', $row->type->value)).
                    '</span>'
                )
                ->addColumn('status', function ($row) {

                    $assigned = DailyTask::where('task_id', $row->id)
                        ->where('date', now()->toDateString())
                        ->first();

                    if (!$assigned) {
                        return '<span class="badge badge-light-warning">Not Assigned</span>';
                    }

                    $status = (int) $assigned->is_active;

                    $badgeClass = $status ? 'badge-light-success' : 'badge-light-danger';
                    $label = $status ? 'Active' : 'Inactive';

                    return '<span class="badge '.$badgeClass.'">'.$label.'</span>';
                })
                ->addColumn('date', fn($row) =>
                    '<input type="date" class="form-control assign-date" data-id="'.$row->id.'">'
                )

                ->addColumn('actions', fn($row) =>
                    '<button class="btn btn-primary assign-btn" data-id="'.$row->id.'">
                    Assign
                </button>'
                )

                ->rawColumns(['title','type','date','actions','status'])
                ->make(true);
        }

        return view('backend.layout.daily_task.assign');
    }


    /**
     * assign selected task for selected date
     */
    public function assignStore(Request $request)
    {
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'date'    => 'required|date',
        ]);

        DailyTask::updateOrCreate(
            [
                'task_id' => $request->task_id,
                'date'    => $request->date,
            ],
            [
                'sort_order' => 1,
                'is_active'  => true,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Task assigned successfully!',
        ]);
    }
    /**
     * update daily assign
     */
    public function assignUpdate(Request $request, $id)
    {
        $validated = $request->validate([
            'date'       => 'required|date',
            'is_active'  => 'required|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $dailyTask = DailyTask::findOrFail($id);
        $dailyTask->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Daily task updated successfully.'
        ]);
    }
    /**
     * delete daily task
     */
    public function assignRemove($id)
    {
        try {
            DailyTask::where('id', $id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Daily Task removed successfully.',
            ]);
        }catch (\Exception $exception){
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ]);
        }
    }


}
