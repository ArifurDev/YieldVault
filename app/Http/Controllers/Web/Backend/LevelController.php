<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Level;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class LevelController extends Controller
{
    /**
     * list of level
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = Level::latest();

            return DataTables::of($data)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && $request->search['value']) {

                        $search = $request->search['value'];

                        $query->where(function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('slug', 'like', "%{$search}%")
                                ->orWhere('required_xp', 'like', "%{$search}%")
                                ->orWhere('daily_roi', 'like', "%{$search}%");
                        });
                    }
                })
                ->addColumn('icon', function ($row) {
                    $icon = $row->icon
                        ? asset($row->icon)
                        : asset('backend/assets/media/icons/placeholder.svg');

                    return '
                    <div class="symbol symbol-50px">
                        <img src="'.$icon.'" class="w-100" alt="'.$row->name.'">
                    </div>
                ';
                })

                ->addColumn('name', function ($row) {
                    return '
                    <div class="d-flex flex-column">
                        <span class="fw-bold text-gray-800">'.$row->name.'</span>
                        <small class="text-muted">'.$row->slug.'</small>
                    </div>
                ';
                })

                ->addColumn('required_xp', fn ($row) =>
                    '<span class="badge badge-light-info">'.$row->required_xp.' XP</span>'
                )

                ->addColumn('daily_roi', fn ($row) =>
                    '<span class="badge badge-light-success">'.$row->daily_roi.'%</span>'
                )

                ->addColumn('status', function ($row) {
                    if ($row->is_coming_soon) {
                        return '<span class="badge badge-light-warning">Coming Soon</span>';
                    }

                    return $row->is_active
                        ? '<span class="badge badge-light-success">Active</span>'
                        : '<span class="badge badge-light-danger">Inactive</span>';
                })

                ->addColumn('actions', function ($row) {
                    return '
                    <div class="d-flex justify-content-end">
                        <a href="#" class="btn btn-light btn-sm"
                           data-kt-menu-trigger="click"
                           data-kt-menu-placement="bottom-end">
                            Actions
                            <i class="ki-duotone ki-down fs-5 ms-1"></i>
                        </a>

                        <div class="menu menu-sub menu-sub-dropdown menu-column
                                    menu-rounded menu-gray-600 menu-state-bg-light-primary
                                    fw-semibold fs-7 w-125px py-4" data-kt-menu="true">

                           <div class="menu-item px-3">
                                <a href="'.route('level.edit', $row->id).'" class="menu-link px-3">Edit</a>
                            </div>

                        </div>
                    </div>
                ';
                })

                ->rawColumns(['icon','name','required_xp','daily_roi','status','actions'])
                ->make(true);
        }

        // ONLY when not ajax
        return view('backend.layout.level.index');
    }



    /**
     * edit time get data
     */
    public function edit($id)
    {
        $level = Level::find($id);

        if (!$level) {
            return redirect()->back()->with('error', 'Data not found');
        }

        return view('backend.layout.level.edit', compact('level'));
    }


    /**
     * update level
     */
    public function update(Request $request, $id)
    {
        try {
            $level = Level::findOrFail($id);

            // Validation
            $validated = $request->validate([
                'name'            => 'required|string|max:255',
                'slug'            => 'required|string|max:255|unique:levels,slug,' . $level->id,
                'required_xp'     => 'required|integer|min:0',
                'daily_roi'       => 'required|numeric|min:0',
                'order'           => 'nullable|integer|min:0',
                'icon'            => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            ]);

            // Boolean fields
            $validated['is_active'] = $request->has('is_active') ? 1 : 0;
            $validated['is_coming_soon'] = $request->has('is_coming_soon') ? 1 : 0;

            // Update
            $level->update($validated);

            return redirect()->back()->with('success', 'Level updated successfully!');
        }catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Something went wrong. Please try again.');
        }
    }

}
