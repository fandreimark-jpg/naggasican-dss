@extends('layouts.app')

@section('title', 'Edit Student')
@section('subtitle', 'Section ' . $section->name)

@section('content')

<div class="bg-white rounded-xl shadow-sm p-6 max-w-lg">
    <form method="POST" action="{{ route('adviser.students.update', $student->id) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm text-gray-600 mb-1">LRN</label>
            <input type="text" name="lrn" maxlength="12" required
                   class="w-full border rounded-lg px-3 py-2 text-sm"
                   value="{{ old('lrn', $student->lrn) }}">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-600 mb-1">Last Name</label>
                <input type="text" name="last_name" required
                       class="w-full border rounded-lg px-3 py-2 text-sm"
                       value="{{ old('last_name', $student->last_name) }}">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">First Name</label>
                <input type="text" name="first_name" required
                       class="w-full border rounded-lg px-3 py-2 text-sm"
                       value="{{ old('first_name', $student->first_name) }}">
            </div>
        </div>

        <div>
            <label class="block text-sm text-gray-600 mb-1">Middle Name</label>
            <input type="text" name="middle_name"
                   class="w-full border rounded-lg px-3 py-2 text-sm"
                   value="{{ old('middle_name', $student->middle_name) }}">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-600 mb-1">Gender</label>
                <select name="gender" required class="w-full border rounded-lg px-3 py-2 text-sm">
                    <option value="male" {{ $student->gender == 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ $student->gender == 'female' ? 'selected' : '' }}>Female</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Birthdate</label>
                <input type="date" name="birthdate"
                       value="{{ $student->birthdate }}"
                       class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>@extends('layouts.app')

@section('title', 'Edit Student')
@section('subtitle', 'Section ' . $section->name)

@section('content')

<div class="bg-white rounded-xl shadow-sm p-6 max-w-lg">
    <form method="POST" action="{{ route('adviser.students.update', $student->id) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm text-gray-600 mb-1">LRN</label>
            <input type="text" name="lrn" maxlength="12" required
                   class="w-full border rounded-lg px-3 py-2 text-sm"
                   value="{{ old('lrn', $student->lrn) }}">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-600 mb-1">Last Name</label>
                <input type="text" name="last_name" required
                       class="w-full border rounded-lg px-3 py-2 text-sm"
                       value="{{ old('last_name', $student->last_name) }}">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">First Name</label>
                <input type="text" name="first_name" required
                       class="w-full border rounded-lg px-3 py-2 text-sm"
                       value="{{ old('first_name', $student->first_name) }}">
            </div>
        </div>

        <div>
            <label class="block text-sm text-gray-600 mb-1">Middle Name</label>
            <input type="text" name="middle_name"
                   class="w-full border rounded-lg px-3 py-2 text-sm"
                   value="{{ old('middle_name', $student->middle_name) }}">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-600 mb-1">Gender</label>
                <select name="gender" required class="w-full border rounded-lg px-3 py-2 text-sm">
                    <option value="male" {{ $student->gender == 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ $student->gender == 'female' ? 'selected' : '' }}>Female</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Birthdate</label>
                <input type="date" name="birthdate"
                       value="{{ $student->birthdate }}"
                       class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('adviser.students') }}" class="px-4 py-2 text-sm text-gray-500">Cancel</a>
            <button type="submit" class="bg-brand-700 text-white px-6 py-2 rounded-lg text-sm font-medium">
                Update Student
            </button>
        </div>
    </form>
</div>

@endsection
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('adviser.students') }}" class="px-4 py-2 text-sm text-gray-500">Cancel</a>
            <button type="submit" class="bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium">
                Update Student
            </button>
        </div>
    </form>
</div>

@endsection