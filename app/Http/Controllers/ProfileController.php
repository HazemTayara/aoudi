<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\ChangePasswordRequest;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show()
    {
        $user = auth()->user();
        return view('profile.show', compact('user'));
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'current_password' => ['Current password is incorrect.']
                    ]
                ], 422);
            }
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully.'
            ]);
        }

        return back()->with('success', 'تم تغيير كلمة المرور بنجاح');
    }
}