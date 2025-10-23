<?php

namespace App\Http\Controllers\Web\Backend;

use App\Helpers\helpers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /**
     * get current auth data
     */
    public function index()
    {
        $user = auth()->user();
        $imageUrl = helpers::generateTempURL($user->avatar,config('app.file_system'));
        return view('backend.layout.Profile.index',compact('user','imageUrl'));
    }
    /**
     * update current auth data
     */
    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'fname'             => 'nullable|string|max:255',
            'lname'             => 'nullable|string|max:255',
            'email'             => 'sometimes|nullable|email|max:255',
            'avatar'            => 'nullable|mimes:jpeg,jpg,png,gif|max:4800'

        ]);

        $auth = auth()->user();

        try {
            $imagePaths = $auth->avatar;
            // Update avatar if provided
            if ($request->hasFile('avatar')) {
                if (!empty($auth->avatar)) {
                    Helpers::deleteFile($auth->avatar,config('app.file_system'));
                }

                $file = $request->file('avatar');
                $mediaFile = time() . Str::random(10) . Str::uuid() . '_' . date('Ymd_His') . '.' . $file->getClientOriginalExtension();
                $imagePaths = Helpers::uploadFile('avatar',$file,$mediaFile,config('app.file_system')); // store url
            }
            $auth->avatar     = $imagePaths;

            if (isset($validatedData['fname'])) {
                $auth->first_name = $validatedData['fname'];
            }
            if (isset($validatedData['lname'])) {
                $auth->last_name = $validatedData['lname'];
            }
            if (isset($validatedData['email'])) {
                $auth->email = $validatedData['email'];
            }

            $auth->save();

            return redirect()->route('profile.edit')->with('success','Profile updated successfully');
        }catch (\Exception $exception){
            return redirect()->back()->with('error',$exception->getMessage());
        }
    }


    /**
     * update email
     */
    public function updateEmail(Request $request)
    {
        // Validate the request
        $request->validate([
            'emailaddress' => 'required|email|unique:users,email,' . auth()->id(),
            'confirmemailpassword' => 'required'
        ],[
            'emailaddress.required' => 'Email is required.',
            'emailaddress.email' => 'Email is invalid.',
            'confirmemailpassword.required' => 'Email update time Password is required.',
        ]);

        // Get the authenticated user
        $user = auth()->user();

        try {
            // Verify the password
            if (!\Hash::check($request->confirmemailpassword, $user->password)) {
                return back()->withErrors(['confirmemailpassword' => 'The password is incorrect.'])->withInput();
            }

            // Update the email
            $user->update(['email' => $request->emailaddress]);

            return redirect()->back()->with('success', 'Email updated successfully.');
        }catch (\Exception $exception){
            return redirect()->back()->with('error',$exception->getMessage());
        }
    }

    /**
     * change password
     */
    public function passwordChange(Request $request)
    {
        $request->validate([
            'currentpassword' => ['required', 'min:6'],
            'password' => ['required', 'min:6', 'confirmed'], // confirms with password_confirmation
        ]);
        $auth = auth()->user();

        try {
            // Log out other devices for security
            Auth::logoutOtherDevices($request->currentpassword);

            // Update password
            $auth->password = Hash::make($request->password);
            $auth->save();

            return redirect()->route('profile.edit')->with('success', 'Password updated successfully.');
        }catch (\Exception $exception){
            return redirect()->back()->with('error',$exception->getMessage());
        }
    }

}
