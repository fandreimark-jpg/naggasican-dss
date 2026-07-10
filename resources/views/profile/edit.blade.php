@extends('layouts.app')

@section('title', 'My Profile')
@section('subtitle', 'Update your own account information')

@section('content')

<div class="max-w-xl space-y-6">

    {{-- ===================== NAME / INFO ===================== --}}
    {{-- First form: for NAME only. Kept separate from the password form
         so the user doesn't have to re-type their password just to change
         their name. Submits (POST) to profile.update (ProfileController@update). --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-sm font-semibold text-gray-800 mb-1">Profile Information</h3>
        <p class="text-xs text-gray-400 mb-5">Update your name. Your email and role are managed by the Principal.</p>

        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Last Name</label>
                    <input type="text" name="last_name" required
                           value="{{ old('last_name', $user->last_name) }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">First Name</label>
                    <input type="text" name="first_name" required
                           value="{{ old('first_name', $user->first_name) }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Middle Name</label>
                <input type="text" name="middle_name"
                       value="{{ old('middle_name', $user->middle_name) }}"
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Email</label>
                <input type="text" disabled
                       value="{{ $user->email }}"
                       class="w-full border rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-400 cursor-not-allowed">
                <p class="text-xs text-gray-400 mt-1">Contact the Principal to change your email.</p>
            </div>

            @if($errors->has('last_name') || $errors->has('first_name') || $errors->has('middle_name'))
                <div class="bg-red-100 text-red-700 text-sm p-3 rounded-lg">
                    <ul class="list-disc list-inside">
                        @foreach($errors->only(['last_name','first_name','middle_name']) as $error)
                            <li>{{ is_array($error) ? $error[0] : $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="pt-2">
                <button type="submit"
                        class="bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-blue-800">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    {{-- ===================== PASSWORD ===================== --}}
    {{-- Second form: for PASSWORD only. Submits (POST) to
         profile.password.update (ProfileController@updatePassword).
         "Current Password" is required before accepting a new one —
         this is a security check, not a typo. --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-sm font-semibold text-gray-800 mb-1">Change Password</h3>
        <p class="text-xs text-gray-400 mb-5">You'll need to enter your current password first.</p>

        <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm text-gray-600 mb-1">Current Password</label>
                <input type="password" name="current_password" required
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">New Password</label>
                    <input type="password" name="password" required minlength="8"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Confirm New Password</label>
                    <input type="password" name="password_confirmation" required minlength="8"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
            </div>

            @if($errors->has('current_password') || $errors->has('password'))
                <div class="bg-red-100 text-red-700 text-sm p-3 rounded-lg">
                    <ul class="list-disc list-inside">
                        @foreach($errors->only(['current_password','password']) as $error)
                            <li>{{ is_array($error) ? $error[0] : $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="pt-2">
                <button type="submit"
                        class="bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-blue-800">
                    Update Password
                </button>
            </div>
        </form>
    </div>

</div>

@endsection