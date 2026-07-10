{{--
    PROFILE MODAL (partial view)
    -----------------------------
    This is included ONCE inside layouts/app.blade.php, so it is
    available on every page (dashboard, students, etc.) — not just
    a dedicated "My Profile" page anymore.

    It opens/closes as a popup, controlled by the openProfileModal()
    and closeProfileModal() JavaScript functions at the bottom.

    If there are validation errors from a previous submit (wrong current
    password, blank name, etc.), the modal automatically stays open so
    the user can see what went wrong — see the class="{{ $errors->any() ... }}" line below.
--}}
<div id="profileModal"
     class="{{ $errors->any() ? '' : 'hidden' }} fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">

        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">My Profile</h3>
            <button type="button" onclick="closeProfileModal()"
                    class="text-gray-400 hover:text-gray-600">✕</button>
        </div>

        {{-- ===================== NAME FORM ===================== --}}
        <div class="mb-6">
            <h4 class="text-sm font-semibold text-gray-700 mb-1">Profile Information</h4>
            <p class="text-xs text-gray-400 mb-4">Update your name and login email. Your account role is managed by the Principal.</p>

            <form method="POST" action="{{ route('profile.update') }}" class="space-y-3">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Last Name</label>
                        <input type="text" name="last_name" required
                               value="{{ old('last_name', auth()->user()->last_name) }}"
                               class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">First Name</label>
                        <input type="text" name="first_name" required
                               value="{{ old('first_name', auth()->user()->first_name) }}"
                               class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-gray-600 mb-1">Middle Name</label>
                    <input type="text" name="middle_name"
                           value="{{ old('middle_name', auth()->user()->middle_name) }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>

                <div>
                    <label class="block text-sm text-gray-600 mb-1">Username / Email</label>
                    {{-- data-original-email stores the email as it currently is in the
                         database. Every time the user types in this field, our JS
                         (further below) compares the new value against this original
                         to decide whether the Current Password field should appear. --}}
                    <input type="email" name="email" id="profile_email" required
                           data-original-email="{{ auth()->user()->email }}"
                           value="{{ old('email', auth()->user()->email) }}"
                           oninput="toggleCurrentPasswordField()"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>

                {{-- This field starts HIDDEN. It only appears (via JavaScript)
                     when the email above is actually changed to a different value.
                     Editing just the name will never show this. --}}
                <div id="currentPasswordWrapper" class="hidden">
                    <label class="block text-sm text-gray-600 mb-1">Current Password</label>
                    <input type="password" name="current_password" id="current_password_field"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <p class="text-xs text-gray-400 mt-1">Required to confirm changes to your login email.</p>
                </div>

                @if($errors->has('last_name') || $errors->has('first_name') || $errors->has('middle_name') || $errors->has('email') || $errors->has('current_password'))
                    <div class="bg-red-100 text-red-700 text-sm p-3 rounded-lg">
                        <ul class="list-disc list-inside">
                            @foreach($errors->only(['last_name','first_name','middle_name','email','current_password']) as $error)
                                <li>{{ is_array($error) ? $error[0] : $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="pt-1">
                    <button type="submit"
                            class="bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-blue-800">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <hr class="my-4">

        {{-- ===================== PASSWORD FORM ===================== --}}
        <div>
            <h4 class="text-sm font-semibold text-gray-700 mb-1">Change Password</h4>
            <p class="text-xs text-gray-400 mb-4">You'll need to enter your current password first.</p>

            <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-3">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm text-gray-600 mb-1">Current Password</label>
                    <input type="password" name="current_password" required
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>

                <div class="grid grid-cols-2 gap-3">
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

                <div class="pt-1">
                    <button type="submit"
                            class="bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-blue-800">
                        Update Password
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

@push('scripts')
<script>
    // Same simple show/hide pattern used by the Add Student modal.
    function openProfileModal() {
        document.getElementById('profileModal').classList.remove('hidden');
    }
    function closeProfileModal() {
        document.getElementById('profileModal').classList.add('hidden');
    }
    // Clicking the dark overlay outside the modal box closes it
    document.getElementById('profileModal').addEventListener('click', function (e) {
        if (e.target === this) closeProfileModal();
    });

    // Shows/hides the "Current Password" field on the Profile Information
    // form, depending on whether the email was actually changed.
    //
    // How it decides: it reads the email input's CURRENT typed value,
    // and compares it against data-original-email (the email as saved
    // in the database, set when the page rendered). If they're different,
    // the user is changing their email, so we show the field and mark it
    // required. If they match, we hide it and clear/un-require it.
    function toggleCurrentPasswordField() {
        const emailInput = document.getElementById('profile_email');
        const wrapper     = document.getElementById('currentPasswordWrapper');
        const pwField     = document.getElementById('current_password_field');

        const emailWasChanged = emailInput.value !== emailInput.dataset.originalEmail;

        if (emailWasChanged) {
            wrapper.classList.remove('hidden');
            pwField.required = true;
        } else {
            wrapper.classList.add('hidden');
            pwField.required = false;
            pwField.value = ''; // clear it so nothing gets submitted by accident
        }
    }

    // Run this once as soon as the page loads. This matters when the
    // form re-renders after a validation error — for example, if the
    // user already typed a new email but forgot the current password,
    // the field needs to show up again automatically (not stay hidden).
    document.addEventListener('DOMContentLoaded', toggleCurrentPasswordField);
</script>
@endpush