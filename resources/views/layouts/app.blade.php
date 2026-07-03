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
                    <a href="{{ route('principal.activity.logs') }}"
                    class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-blue-700 {{ request()->routeIs('principal.activity*') ? 'bg-blue-700' : '' }}">
                        <i class="bi bi-clock-history"></i> Activity Logs
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

            <div class="p-4 border-t border-blue-700">
                <p class="text-sm font-medium">{{ auth()->user()->name }}</p>
                <p class="text-xs text-blue-300 capitalize">{{ auth()->user()->role }}</p>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full text-left text-xs text-blue-300 hover:text-white">
                        Logout →
                    </button>
                </form>
            </div>
        </aside>

        <main class="flex-1 p-6 overflow-y-auto">
            <div class="max-w-6xl mx-auto">
                <div class="mb-4">
                    <h2 class="text-xl font-bold text-gray-800">@yield('title')</h2>
                    <p class="text-sm text-gray-500">@yield('subtitle')</p>
                </div>
                @yield('content')
            </div>
        </main>
    </div>

    {{-- ===================== TOAST NOTIFICATION ===================== --}}
    @if(session('success') || session('error') || session('warning'))
    <div id="flashToast"
         class="fixed top-6 right-6 z-[9999] flex items-start gap-3 px-5 py-4 rounded-xl shadow-2xl border w-80
                opacity-0 translate-y-3 transition-all duration-500
                {{ session('success') ? 'bg-white border-green-300' : '' }}
                {{ session('error')   ? 'bg-white border-red-300'   : '' }}
                {{ session('warning') ? 'bg-white border-yellow-300': '' }}">
        <div class="absolute left-0 top-0 bottom-0 w-1 rounded-l-xl
            {{ session('success') ? 'bg-green-500' : '' }}
            {{ session('error')   ? 'bg-red-500'   : '' }}
            {{ session('warning') ? 'bg-yellow-400': '' }}">
        </div>
        <div class="ml-2 mt-0.5 text-lg shrink-0
            {{ session('success') ? 'text-green-500' : '' }}
            {{ session('error')   ? 'text-red-500'   : '' }}
            {{ session('warning') ? 'text-yellow-500': '' }}">
            @if(session('success')) <i class="bi bi-check-circle-fill"></i> @endif
            @if(session('error'))   <i class="bi bi-x-circle-fill"></i>     @endif
            @if(session('warning')) <i class="bi bi-exclamation-circle-fill"></i> @endif
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-800">
                @if(session('success')) Success @endif
                @if(session('error'))   Error   @endif
                @if(session('warning')) Warning @endif
            </p>
            <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">
                {{ session('success') ?? session('error') ?? session('warning') }}
            </p>
        </div>
        <button onclick="dismissToast()" class="shrink-0 text-gray-300 hover:text-gray-500 text-lg leading-none mt-0.5">
            <i class="bi bi-x"></i>
        </button>
        <div id="toastProgress"
             class="absolute bottom-0 left-0 right-0 h-0.5 rounded-b-xl
                {{ session('success') ? 'bg-green-400' : '' }}
                {{ session('error')   ? 'bg-red-400'   : '' }}
                {{ session('warning') ? 'bg-yellow-400': '' }}"
             style="animation: toastShrink 4s linear forwards;">
        </div>
    </div>
    <style>
        @keyframes toastShrink { from { width: 100%; } to { width: 0%; } }
    </style>
    <script>
        (function () {
            const toast = document.getElementById('flashToast');
            if (!toast) return;
            requestAnimationFrame(() => {
                setTimeout(() => {
                    toast.classList.remove('opacity-0', 'translate-y-3');
                    toast.classList.add('opacity-100', 'translate-y-0');
                }, 80);
            });
            let autoDismiss = setTimeout(dismissToast, 4000);
            toast.addEventListener('mouseenter', () => clearTimeout(autoDismiss));
            toast.addEventListener('mouseleave', () => { autoDismiss = setTimeout(dismissToast, 1500); });
        })();
        function dismissToast() {
            const toast = document.getElementById('flashToast');
            if (!toast) return;
            toast.classList.add('opacity-0', 'translate-y-3');
            setTimeout(() => toast.remove(), 500);
        }
    </script>
    @endif
    {{-- ============================================================= --}}

    {{-- ===================== CONFIRM DELETE MODAL ================== --}}
    <div id="confirmDeleteModal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-[9998]">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden transform transition-all duration-200 scale-95 opacity-0" id="confirmDeleteBox">
            <div class="h-1.5 bg-red-500 w-full"></div>
            <div class="p-6">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                        <i class="bi bi-trash text-red-600 text-lg"></i>
                    </div>
                    <h3 class="text-base font-semibold text-gray-800">Confirm Delete</h3>
                </div>
                <p id="confirmDeleteMessage" class="text-sm text-gray-600 mb-1"></p>
                <p class="text-xs text-red-400 mb-5">
                    <i class="bi bi-exclamation-triangle-fill"></i> This action cannot be undone.
                </p>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeConfirmDelete()"
                            class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        <i class="bi bi-x-lg mr-1"></i> Cancel
                    </button>
                    <button type="button" id="confirmDeleteBtn" onclick="proceedDelete()"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                        <i class="bi bi-trash mr-1"></i> Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- ============================================================= --}}

    {{-- ===================== CONFIRM RESUBMIT MODAL ================ --}}
    <div id="confirmResubmitModal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-[9998]">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden transform transition-all duration-200 scale-95 opacity-0" id="confirmResubmitBox">
            <div class="h-1.5 bg-yellow-400 w-full"></div>
            <div class="p-6">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center shrink-0">
                        <i class="bi bi-arrow-repeat text-yellow-600 text-lg"></i>
                    </div>
                    <h3 class="text-base font-semibold text-gray-800">Confirm Re-submit</h3>
                </div>
                <p id="confirmResubmitMessage" class="text-sm text-gray-600 mb-1"></p>
                <p class="text-xs text-yellow-600 mb-5">
                    <i class="bi bi-exclamation-triangle-fill"></i> Risk classification will be updated.
                </p>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeConfirmResubmit()"
                            class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        <i class="bi bi-x-lg mr-1"></i> Cancel
                    </button>
                    <button type="button" id="confirmResubmitBtn" onclick="proceedResubmit()"
                            class="px-4 py-2 text-sm font-medium text-white bg-yellow-500 hover:bg-yellow-600 rounded-lg transition-colors">
                        <i class="bi bi-arrow-repeat mr-1"></i> Yes, Re-submit
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- ============================================================= --}}

    <script>
        let _pendingDeleteForm    = null;
        let _pendingResubmitForm  = null;

        // ===== DELETE CONFIRM =====
        document.addEventListener('submit', function (e) {
            const form = e.target;

            // Delete confirm
            if (form.dataset.confirm) {
                e.preventDefault();
                showConfirmDelete(form, form.dataset.confirm);
                return;
            }

            // Resubmit confirm
            if (form.dataset.resubmit) {
                e.preventDefault();
                showConfirmResubmit(form, form.dataset.resubmit);
                return;
            }
        });

        function showConfirmDelete(form, message) {
            _pendingDeleteForm = form;
            document.getElementById('confirmDeleteMessage').textContent = message;
            const modal = document.getElementById('confirmDeleteModal');
            const box   = document.getElementById('confirmDeleteBox');
            modal.classList.remove('hidden');
            requestAnimationFrame(() => setTimeout(() => {
                box.classList.remove('scale-95', 'opacity-0');
                box.classList.add('scale-100', 'opacity-100');
            }, 20));
        }

        function closeConfirmDelete() {
            const modal = document.getElementById('confirmDeleteModal');
            const box   = document.getElementById('confirmDeleteBox');
            box.classList.remove('scale-100', 'opacity-100');
            box.classList.add('scale-95', 'opacity-0');
            setTimeout(() => { modal.classList.add('hidden'); _pendingDeleteForm = null; }, 200);
        }

        function proceedDelete() {
            if (_pendingDeleteForm) {
                const btn = document.getElementById('confirmDeleteBtn');
                btn.disabled  = true;
                btn.innerHTML = '<i class="bi bi-hourglass-split mr-1"></i> Deleting...';
                _pendingDeleteForm.submit();
            }
        }

        // ===== RESUBMIT CONFIRM =====
        function showConfirmResubmit(form, message) {
            _pendingResubmitForm = form;
            document.getElementById('confirmResubmitMessage').textContent = message;
            const modal = document.getElementById('confirmResubmitModal');
            const box   = document.getElementById('confirmResubmitBox');
            modal.classList.remove('hidden');
            requestAnimationFrame(() => setTimeout(() => {
                box.classList.remove('scale-95', 'opacity-0');
                box.classList.add('scale-100', 'opacity-100');
            }, 20));
        }

        function closeConfirmResubmit() {
            const modal = document.getElementById('confirmResubmitModal');
            const box   = document.getElementById('confirmResubmitBox');
            box.classList.remove('scale-100', 'opacity-100');
            box.classList.add('scale-95', 'opacity-0');
            setTimeout(() => { modal.classList.add('hidden'); _pendingResubmitForm = null; }, 200);
        }

        function proceedResubmit() {
            if (_pendingResubmitForm) {
                const btn = document.getElementById('confirmResubmitBtn');
                btn.disabled  = true;
                btn.innerHTML = '<i class="bi bi-hourglass-split mr-1"></i> Submitting...';
                _pendingResubmitForm.submit();
            }
        }

        // Close on backdrop click
        document.getElementById('confirmDeleteModal').addEventListener('click', function (e) {
            if (e.target === this) closeConfirmDelete();
        });
        document.getElementById('confirmResubmitModal').addEventListener('click', function (e) {
            if (e.target === this) closeConfirmResubmit();
        });

        // Close on Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeConfirmDelete();
                closeConfirmResubmit();
            }
        });
    </script>

    @stack('scripts')
</body>
</html>