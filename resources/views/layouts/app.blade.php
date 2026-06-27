<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Naggasican NHS DSS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans">

    {{-- SIDEBAR --}}
    <div class="flex min-h-screen">
        <aside class="w-64 bg-blue-900 text-white flex flex-col">
            <div class="p-6 border-b border-blue-700">
                <h1 class="text-lg font-bold leading-tight">Naggasican NHS</h1>
                <p class="text-xs text-blue-300 mt-1">Decision Support System</p>
            </div>

            <nav class="flex-1 p-4 space-y-1">
                @if(auth()->user()->role === 'principal')
                    <a href="{{ route('principal.dashboard') }}"
                    class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-blue-700 {{ request()->routeIs('principal.dashboard') ? 'bg-blue-700' : '' }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a href="{{ route('principal.users') }}"
                    class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-blue-700 {{ request()->routeIs('principal.users*') ? 'bg-blue-700' : '' }}">
                        <i class="bi bi-people"></i> Users
                    </a>
                    <a href="{{ route('principal.tracks') }}"
                    class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-blue-700 {{ request()->routeIs('principal.tracks*') ? 'bg-blue-700' : '' }}">
                        <i class="bi bi-diagram-3"></i> Tracks
                    </a>
                    <a href="{{ route('principal.specializations') }}"
                    class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-blue-700 {{ request()->routeIs('principal.specializations*') ? 'bg-blue-700' : '' }}">
                        <i class="bi bi-collection"></i> Specializations
                    </a>
                    <a href="{{ route('principal.subjects') }}"
                    class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-blue-700 {{ request()->routeIs('principal.subjects*') ? 'bg-blue-700' : '' }}">
                        <i class="bi bi-book"></i> Subjects
                    </a>
                    <a href="{{ route('principal.sections') }}"
                    class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-blue-700 {{ request()->routeIs('principal.sections*') ? 'bg-blue-700' : '' }}">
                        <i class="bi bi-grid"></i> Sections
                    </a>
                    <a href="{{ route('principal.students') }}"
                    class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-blue-700 {{ request()->routeIs('principal.students*') ? 'bg-blue-700' : '' }}">
                        <i class="bi bi-mortarboard"></i> Students
                    </a>
                    <a href="{{ route('principal.reports') }}"
                    class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-blue-700 {{ request()->routeIs('principal.reports*') ? 'bg-blue-700' : '' }}">
                        <i class="bi bi-file-earmark-text"></i> Reports
                    </a>
                @else
                    <a href="{{ route('adviser.dashboard') }}"
                    class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-blue-700 {{ request()->routeIs('adviser.dashboard') ? 'bg-blue-700' : '' }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a href="{{ route('adviser.students') }}"
                    class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-blue-700 {{ request()->routeIs('adviser.students*') ? 'bg-blue-700' : '' }}">
                        <i class="bi bi-mortarboard"></i> My Students
                    </a>
                    <a href="{{ route('adviser.grades') }}"
                    class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-blue-700 {{ request()->routeIs('adviser.grades*') ? 'bg-blue-700' : '' }}">
                        <i class="bi bi-pencil-square"></i> Encode Grades
                    </a>
                    <a href="{{ route('adviser.submit.report') }}"
                    class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-blue-700 {{ request()->routeIs('adviser.submit*') ? 'bg-blue-700' : '' }}">
                        <i class="bi bi-file-earmark-arrow-up"></i> Submit Report
                    </a>
                @endif
            </nav>

            {{-- User Info + Logout --}}
            <div class="p-4 border-t border-blue-700">
                <p class="text-sm font-medium">{{ auth()->user()->name }}</p>
                <p class="text-xs text-blue-300 capitalize">{{ auth()->user()->role }}</p>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit"
                        class="w-full text-left text-xs text-blue-300 hover:text-white">
                        Logout →
                    </button>
                </form>
            </div>
        </aside>

        {{-- MAIN CONTENT --}}
        <main class="flex-1 p-6 overflow-y-auto">
            <div class="max-w-6xl mx-auto">

                {{-- Page Header --}}
                <div class="mb-4">
                    <h2 class="text-xl font-bold text-gray-800">@yield('title')</h2>
                    <p class="text-sm text-gray-500">@yield('subtitle')</p>
                </div>

                {{-- Flash Messages --}}
                @if(session('success'))
                    <div class="mb-3 p-3 bg-green-100 text-green-700 rounded-lg text-sm">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-3 p-3 bg-red-100 text-red-700 rounded-lg text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')

            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>