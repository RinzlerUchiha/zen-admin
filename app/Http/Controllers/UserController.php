<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function updatePassword(Request $request)
    {
        // Validate input
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();

        // Check if the current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        // Update the password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('success', 'Password updated successfully.');
    }
    public function copyUsers()
    {
        $newPassword = '123';
        $newPasswordHashed = Hash::make($newPassword);
        $users = DB::connection('hrd2')->table('tbl_user2')->select(['Emp_No', 'U_Name', 'U_Remarks'])->get()
        ->map(fn ($user) => [
            'Emp_No' => $user->Emp_No,
            'U_Name' => $user->U_Name,
            'U_Remarks' => $user->U_Remarks,
            'U_Password' => $newPassword,
            'U_Password_hashed' => $newPasswordHashed,
            'U_timestamp' => now(),
        ])
        ->toArray();

        $update = User::query()->upsert(
            $users,
            ['Emp_No'],
            ['U_Password', 'U_Password_hashed']
        );

        return response()->json(['message' => 'Users copied successfully', 'updated' => $update]);
    }
}
