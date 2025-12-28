<?php

namespace App\Http\Controllers\Web\Backend;

use App\Enum\Role;
use App\Helpers\helpers;
use App\Http\Controllers\Controller;
use App\Mail\Api\V1\SendOtpMail;
use App\Mail\NotifyUser;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    /**
     * list of user
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = User::where('role', '!=', 'admin')->latest();

            return DataTables::of($data)
//                ->addIndexColumn()
                ->addColumn('user', function ($row) {
                    $fullName = $row->first_name . ' ' . $row->last_name;
                    $avatarUrl = $row->avatar ? Helpers::generateTempURL($row->avatar,config('app.file_system')) : asset('backend/assets/media/avatars/300-1.jpg');

                    return '
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                            <img src="' . $avatarUrl . '" class="w-100" alt="' . $fullName . '">
                        </div>
                        <div class="d-flex flex-column">
                            <span class="text-gray-800 mb-1">' . $fullName . '</span>
                            <span>' . $row->email . '</span>
                        </div>
                    </div>';
                })
                ->addColumn('joined_date', fn($row) => $row->created_at ? $row->created_at->format('d M Y, h:i a') : '')
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
                                        <a href="#" data-id="' . $row->id . '" class="menu-link edit-user-btn px-3">Edit</a>
                                    </div>
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3 delete-user-btn" data-id="' . $row->id . '">Delete</a>
                                    </div>
                                </div>
                            </div>
                            ';
                })
                ->rawColumns(['user', 'actions'])
                ->make(true);
        }

        // ONLY when not ajax
        return view('backend.layout.User.index');

    }
    /**
     * store user data
     */
    public function store(Request $request)
    {
       $validData = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'email'      => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users'
            ],
            'password'    => ['required', 'string', 'min:8'],
            'avatar'      => ['nullable', 'file', 'max:2048', 'mimes:jpeg,jpg,png'],
            'role'        => ['required', new Enum(Role::class)],
           // USER only
           'manager_id' => 'required_if:role,user|nullable|exists:users,id',

           // MANAGER only
           'manager_account' => 'required_if:role,manager|nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            // upload avatar
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $mediaFile = time() . Str::random(10) . Str::uuid() . '_' . date('Ymd_His') . '.' . $file->getClientOriginalExtension();
                $validData['avatar'] = Helpers::uploadFile('avatar',$file,$mediaFile,config('app.file_system')); // store url
            }

            // create user
            $user = User::create([
                'first_name' => $validData['first_name'],
                'last_name'  => $validData['last_name'],
                'email'      => $validData['email'],
                'password'   => Hash::make($validData['password']),
                'avatar'     => $validData['avatar'] ?? null,
                'role'       => $validData['role'],
                // assign manager only if USER
                'manager_id '     => $validData['role'] === Role::USER->value
                    ? $validData['manager_id']
                    : null,

                // store account only if MANAGER
                'account_number' => $validData['role'] === Role::MANAGER->value
                    ? $validData['manager_account']
                    : null,
            ]);

            // create wallet
            Wallet::create([
                'user_id' => $user->id,
                'balance' => 0
            ]);

//            $title = "Welcome to " . config('app.name') . "! Your Account Has Been Created";
//
//            $message = "Dear " . $user->first_name . ",\n\n"
//                . "We’re thrilled to welcome you to " . config('app.name') . "! Your account has been successfully created with the following details:\n\n"
//                . "- Email: " . $user->email . "\n"
//                . "- Name: " . $user->first_name . " " . $user->last_name . "\n\n"
//                . "You can now log in and start exploring all the features we have to offer. If you have any questions or need assistance, feel free to contact our support team.\n\n"
//                . "Best regards,\n"
//                . "The " . config('app.name') . " Team";
//
//            // send mail to this user mail
//            Mail::to($user->email)->send(new NotifyUser($title,$message));
            DB::commit();

            return redirect()->back()->with('success', 'User Created Successfully');
        }catch (\Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('error', $exception->getMessage());
        }
    }

    /**
     * show edit data
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        if ($user)
        {
            $user->avatar = $user->avatar ? Helpers::generateTempURL($user->avatar,config('app.file_system')) : null; ;
        }
        return response()->json(['user' => $user]);
    }


    /**
     * user data update
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = User::with('wallet')->findOrFail($id);

            $validated = $request->validate([
                'first_name'    => ['required', 'string', 'max:255'],
                'last_name'     => ['required', 'string', 'max:255'],
                'email'         => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $id],
                'password'      => ['nullable', 'string', 'min:8'],
                'avatar'        => ['nullable', 'file', 'max:2048', 'mimes:jpeg,jpg,png'],
                 'role'         => ['required', new Enum(Role::class)],
                'avatar_remove' => ['nullable', 'boolean'],
                //  ROLE BASED
                'manager_id'      => ['required_if:role,user', 'nullable', 'exists:users,id'],
                'manager_account' => ['required_if:role,manager', 'nullable', 'string', 'max:50'],
            ]);

            // Update user fields
            $user->first_name = $validated['first_name'];
            $user->last_name  = $validated['last_name'];
            $user->email      = $validated['email'];
            $user->role       = $validated['role'];

            // USER → assign manager
            if ($validated['role'] === Role::USER->value) {
                $user->manager_id = $validated['manager_id'];
                $user->account_number = null; // cleanup
            }

            // MANAGER → account number
            if ($validated['role'] === Role::MANAGER->value) {
                $user->manager_id = null; // cleanup
                $user->account_number = $validated['manager_account'];
            }

            // Handle avatar
            if ($request->hasFile('avatar')) {
                // Delete existing avatar if present
                if ($user->avatar) {
                    Helpers::deleteFile($user->avatar, config('app.file_system'));
                }
                $file = $request->file('avatar');
                $mediaFile = time() . Str::random(10) . Str::uuid() . '_' . date('Ymd_His') . '.' . $file->getClientOriginalExtension();
                $user->avatar = Helpers::uploadFile('avatar', $file, $mediaFile, config('app.file_system'));
            } elseif ($request->input('avatar_remove')) {
                if ($user->avatar) {
                    Helpers::deleteFile($user->avatar, config('app.file_system'));
                }
                $user->avatar = null;
            }

            // Update password only if provided
            if ($request->filled('password')) {
                $user->password = Hash::make($validated['password']);
            }

            // if not found
            if(!$user->wallet)
            {
                Wallet::create([
                    'user_id' => $user->id,
                    'balance' => 0
                ]);
            }

            $user->save();

            DB::commit();
            return response()->json(['message' => 'User updated successfully'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation failed: ' . $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update user: ' . $e->getMessage()], 500);
        }
    }

    /**
     * delete user
     */
    public function delete($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'User not found.'], 404);
            }

            if ($user->role === 'admin') {
                return response()->json(['status' => 'error', 'message' => 'You cannot delete admin account']);
            }

            if ($user->avatar) {
                Helpers::deleteFile($user->avatar, config('app.file_system'));
            }

            $user->update([
                'email' => null,
                'avatar' => null,
            ]);

            // Soft delete user
            $user->delete();

            return response()->json(['status' => 'success', 'message' => 'User soft-deleted successfully.']);
        } catch (\Exception $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()]);
        }
    }

    public function managers()
    {
        $managers = User::where('role', Role::MANAGER->value)
            ->select('id', 'first_name', 'last_name')
            ->orderBy('first_name')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $managers
        ]);
    }

}
