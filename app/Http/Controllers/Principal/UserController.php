<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Section;
use App\Models\Grade;
use App\Helpers\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('id', '!=', auth()->id())
            ->with('section')
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(10);

        return view('principal.users', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'last_name'   => 'required|string|max:255',
            'first_name'  => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'username'    => 'required|string|max:255|alpha_dash',
            'password'    => 'required|string|min:8',
            'role'        => 'required|in:adviser,principal',
        ]);

        $email = $request->username . '@naggasican.edu.ph';

        if (User::where('email', $email)->exists()) {
            return back()->withErrors(['username' => 'This username is already taken.'])->withInput();
        }

        $fullName = $request->last_name . ', ' . $request->first_name;

        User::create([
            'name'        => $fullName,
            'last_name'   => $request->last_name,
            'first_name'  => $request->first_name,
            'middle_name' => $request->middle_name,
            'email'       => $email,
            'password'    => Hash::make($request->password),
            'role'        => $request->role,
        ]);

        LogActivity::log(
            'create_user',
            'Created ' . $request->role . ' account: ' . $request->last_name . ', ' . $request->first_name,
            'users',
            null
        );

        return redirect()->route('principal.users')
            ->with('success', ucfirst($request->role) . ' account created successfully!');
    }

    public function edit($id)
    {
        $user     = User::where('id', $id)->where('role', 'adviser')->firstOrFail();
        $sections = Section::all();
        return view('principal.users-edit', compact('user', 'sections'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'last_name'   => 'required|string|max:255',
            'first_name'  => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'username'    => 'required|string|max:255|alpha_dash',
            'password'    => 'nullable|string|min:8',
            'role'        => 'required|in:adviser,principal',
        ]);

        $email = $request->username . '@naggasican.edu.ph';

        if (User::where('email', $email)->where('id', '!=', $user->id)->exists()) {
            return back()->withErrors(['username' => 'This username is already taken.'])->withInput();
        }

        $user->update([
            'name'        => $request->last_name . ', ' . $request->first_name,
            'last_name'   => $request->last_name,
            'first_name'  => $request->first_name,
            'middle_name' => $request->middle_name,
            'email'       => $email,
            'password'    => $request->password
                ? Hash::make($request->password)
                : $user->password,
            'role'        => $request->role,
        ]);

        return redirect()->route('principal.users')
            ->with('success', 'User updated successfully!');
    }

    public function destroy($id)
    {
        $user = User::where('id', $id)->firstOrFail();

        if ($user->id === auth()->id()) {
            return redirect()->route('principal.users')
                ->with('error', 'You cannot delete your own account.');
        }

        Section::where('adviser_id', $user->id)->update(['adviser_id' => null]);
        Grade::where('encoded_by', $user->id)->update(['encoded_by' => null]);
        $user->delete();

        return redirect()->route('principal.users')
            ->with('success', 'User removed successfully!');
    }
}