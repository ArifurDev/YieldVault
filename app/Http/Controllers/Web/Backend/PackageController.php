<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PackageController extends Controller
{
    /**
     * list of all package
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $query = Package::with('level')->latest();

            return DataTables::of($query)

                ->filter(function ($q) use ($request) {
                    if ($search = $request->search['value'] ?? null) {
                        $q->where(function ($qq) use ($search) {
                            $qq->where('name', 'like', "%{$search}%")
                                ->orWhere('price', 'like', "%{$search}%")
                                ->orWhere('duration_days', 'like', "%{$search}%")
                                ->orWhereHas('level', function ($l) use ($search) {
                                    $l->where('name', 'like', "%{$search}%")
                                        ->orWhere('slug', 'like', "%{$search}%");
                                });
                        });
                    }
                })

                ->addColumn('level', fn ($row) =>
                    '<span class="badge badge-light-primary">'.$row->level?->name.'</span>'
                )

                ->addColumn('price', fn ($row) =>
                    '<span class="fw-bold">$'.$row->price.'</span>'
                )

                ->addColumn('duration', fn ($row) =>
                    '<span class="badge badge-light-info">'.$row->duration_days.' Days</span>'
                )

                ->addColumn('status', fn ($row) =>
                $row->is_active
                    ? '<span class="badge badge-light-success">Active</span>'
                    : '<span class="badge badge-light-danger">Inactive</span>'
                )

                ->addColumn('actions', fn ($row) => '
                <button class="btn btn-sm btn-light edit-package"
                    data-id="'.$row->id.'">
                    Edit
                </button>
            ')

                ->rawColumns(['level','price','duration','status','actions'])
                ->make(true);
        }
        return view('backend.layout.package.index');
    }
}
