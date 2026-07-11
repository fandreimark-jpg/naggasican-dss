// resources/js/modal.js
// All modal (popup) logic for the whole system.
//
// Every modal shares the same 3 helper functions below (show, hide, and
// "close on outside click") — each modal only keeps the logic that's
// actually unique to it (like filling in a student's info).
//
// showModal()/hideModal() also handle the fade + scale animation, so
// every modal in the system opens/closes smoothly with no extra work
// needed per modal — it just needs a child element with class="modal-box".

window.showModal = function (id) {
    const el = document.getElementById(id);
    if (!el) return;
    const box = el.querySelector(".modal-box");

    el.classList.remove("hidden");

    // Force the browser to register the "closed" state first, so the
    // change to "open" state below actually animates instead of
    // jumping straight to the end (a common CSS transition gotcha).
    void el.offsetWidth;

    el.classList.remove("opacity-0");
    el.classList.add("opacity-100");
    if (box) {
        box.classList.remove("scale-95", "opacity-0");
        box.classList.add("scale-100", "opacity-100");
    }
};

window.hideModal = function (id) {
    const el = document.getElementById(id);
    if (!el) return;
    const box = el.querySelector(".modal-box");

    el.classList.remove("opacity-100");
    el.classList.add("opacity-0");
    if (box) {
        box.classList.remove("scale-100", "opacity-100");
        box.classList.add("scale-95", "opacity-0");
    }

    // Wait for the fade-out transition (200ms) to finish before actually
    // hiding the element — otherwise it disappears instantly and the
    // closing animation never gets to play.
    setTimeout(function () {
        el.classList.add("hidden");
    }, 200);
};

// Makes a modal close when the user clicks the dark overlay outside it.
// extraCleanup is optional — for modals that need to reset something
// (like re-enabling a disabled field) when they close.
window.bindModalOverlayClose = function (id, extraCleanup) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.addEventListener("click", function (e) {
        if (e.target !== modal) return;
        if (extraCleanup) extraCleanup();
        window.hideModal(id);
    });
};

document.addEventListener("DOMContentLoaded", function () {
    // =============================================
    // ADVISER — EDIT STUDENT MODAL
    // =============================================
    const editStudentForm = document.getElementById("editForm");

    if (document.getElementById("editModal") && editStudentForm) {
        window.openEditModal = function (student) {
            document.getElementById("edit_last_name").value = student.last_name;
            document.getElementById("edit_first_name").value =
                student.first_name;
            document.getElementById("edit_middle_name").value =
                student.middle_name ?? "";
            document.getElementById("edit_gender").value = student.gender;
            document.getElementById("edit_birthdate").value =
                student.birthdate ?? "";
            editStudentForm.action = `/adviser/students/${student.id}`;
            window.showModal("editModal");
        };

        window.closeEditModal = () => window.hideModal("editModal");
        window.bindModalOverlayClose("editModal");
    }

    // =============================================
    // ADVISER — ADD STUDENT MODAL
    // =============================================
    if (document.getElementById("adviserAddStudentModal")) {
        window.openAdviserAddStudent = () => window.showModal("adviserAddStudentModal");
        window.closeAdviserAddStudent = () => window.hideModal("adviserAddStudentModal");
        window.bindModalOverlayClose("adviserAddStudentModal");
    }

    // =============================================
    // PROFILE MODAL (used on every page, adviser or principal)
    // =============================================
    if (document.getElementById("profileModal")) {
        window.openProfileModal = () => window.showModal("profileModal");
        window.closeProfileModal = () => window.hideModal("profileModal");
        window.bindModalOverlayClose("profileModal");

        // Shows/hides the "Current Password" field depending on whether
        // the email was actually changed from what's saved in the database.
        window.toggleCurrentPasswordField = function () {
            const emailInput = document.getElementById("profile_email");
            const wrapper = document.getElementById("currentPasswordWrapper");
            const pwField = document.getElementById("current_password_field");
            if (!emailInput || !wrapper || !pwField) return;

            const emailWasChanged =
                emailInput.value !== emailInput.dataset.originalEmail;

            if (emailWasChanged) {
                wrapper.classList.remove("hidden");
                pwField.required = true;
            } else {
                wrapper.classList.add("hidden");
                pwField.required = false;
                pwField.value = "";
            }
        };

        // Run once on load too, in case the form re-rendered after a
        // validation error with a changed email already typed in.
        window.toggleCurrentPasswordField();
    }

    // =============================================
    // PRINCIPAL — USER MANAGEMENT MODAL
    // =============================================
    const userForm = document.getElementById("userForm");
    const userTitle = document.getElementById("modalTitle");
    const userMethod = document.getElementById("formMethod");
    const userSubmit = document.getElementById("submitBtn");
    const passwordNote = document.getElementById("passwordNote");

    if (document.getElementById("userModal") && userForm) {
        const storeUrl = userForm.dataset.storeUrl;

        window.openAddModal = function () {
            userForm.reset();
            userTitle.textContent = "Add User";
            userSubmit.textContent = "Save User";
            userForm.action = storeUrl;
            userMethod.value = "POST";
            document.getElementById("field_password").required = true;
            if (passwordNote) passwordNote.classList.add("hidden");
            window.showModal("userModal");
        };

        window.openUserEditModal = function (user) {
            userForm.reset();
            userTitle.textContent = "Edit User";
            userSubmit.textContent = "Update User";
            userForm.action = `/principal/users/${user.id}`;
            userMethod.value = "PUT";

            document.getElementById("field_last_name").value = user.last_name ?? "";
            document.getElementById("field_first_name").value = user.first_name ?? "";
            document.getElementById("field_middle_name").value = user.middle_name ?? "";

            const username = user.email
                ? user.email.replace("@naggasican.edu.ph", "")
                : "";
            document.getElementById("field_username").value = username;
            document.getElementById("field_role").value = user.role;
            document.getElementById("field_password").required = false;
            if (passwordNote) passwordNote.classList.remove("hidden");

            window.showModal("userModal");
        };

        window.closeUserModal = () => window.hideModal("userModal");
        window.bindModalOverlayClose("userModal");
    }

    // =============================================
    // PRINCIPAL — SECTION MANAGEMENT MODAL
    // (moved here from the old standalone public/js/principal/sections.js,
    // which duplicated this same logic in a separate, non-bundled file)
    // =============================================
    const sectionForm = document.getElementById("sectionForm");
    const sectionTitle = document.getElementById("modalTitle");
    const sectionMethod = document.getElementById("sectionMethod");

    if (document.getElementById("sectionModal") && sectionForm) {
        const sectionStoreUrl = sectionForm.dataset.storeUrl;
        const specByTrackUrl = sectionForm.dataset.specUrl;
        const adviserSelect = document.getElementById("sectionAdviser");

        // Loads the specialization dropdown for whichever track is picked.
        // Used by both Add and Edit — attached to window since the
        // <select onchange="loadSpecializations(this.value)"> in the
        // blade file calls it directly.
        window.loadSpecializations = function (trackId, selectedSpecId = null) {
            const specSelect = document.getElementById("sectionSpec");
            specSelect.innerHTML = '<option value="">— Loading... —</option>';

            if (!trackId) {
                specSelect.innerHTML = '<option value="">— Select Track First —</option>';
                return;
            }

            fetch(`${specByTrackUrl}/${trackId}`)
                .then((response) => response.json())
                .then((data) => {
                    specSelect.innerHTML = '<option value="">— Select Specialization —</option>';

                    if (data.length === 0) {
                        specSelect.innerHTML = '<option value="">— No specializations for this track —</option>';
                        return;
                    }

                    data.forEach((spec) => {
                        const option = document.createElement("option");
                        option.value = spec.id;
                        option.textContent = spec.name;
                        if (selectedSpecId && spec.id == selectedSpecId) {
                            option.selected = true;
                        }
                        specSelect.appendChild(option);
                    });
                })
                .catch(() => {
                    specSelect.innerHTML = '<option value="">— Error loading specializations —</option>';
                });
        };

        window.openAddSectionModal = function () {
            sectionForm.reset();
            sectionTitle.textContent = "Add Section";
            sectionMethod.value = "POST";
            sectionForm.action = sectionStoreUrl;
            document.getElementById("sectionSchoolYear").value = "2026-2027";
            document.getElementById("sectionSpec").innerHTML = '<option value="">— Select Track First —</option>';

            // Only hide advisers who already have a DIFFERENT section.
            // (BUG FIX: the old sections.js version referenced a
            // "section" variable that didn't exist in this function,
            // which threw a JS error every time Add Section was clicked.)
            Array.from(adviserSelect.options).forEach((option) => {
                const hasOtherSection = option.value !== "" && option.dataset.sectionId !== "";
                option.style.display = hasOtherSection ? "none" : "";
            });
            adviserSelect.value = "";

            window.showModal("sectionModal");
        };

        window.openEditSectionModal = function (section) {
            sectionForm.reset();
            sectionTitle.textContent = "Edit Section";
            sectionMethod.value = "PUT";
            sectionForm.action = `/principal/sections/${section.id}`;

            document.getElementById("sectionName").value = section.name;
            document.getElementById("sectionGrade").value = section.grade_level;
            document.getElementById("sectionTrack").value = section.track_id ?? "";
            document.getElementById("sectionSchoolYear").value = section.school_year;

            // Show every adviser EXCEPT those already on a different section —
            // the adviser currently on THIS section should still be visible.
            Array.from(adviserSelect.options).forEach((option) => {
                const belongsToAnotherSection =
                    option.value !== "" &&
                    option.dataset.sectionId !== "" &&
                    option.dataset.sectionId != section.id;
                option.style.display = belongsToAnotherSection ? "none" : "";
            });
            adviserSelect.value = section.adviser_id ?? "";

            if (section.track_id) {
                window.loadSpecializations(section.track_id, section.specialization_id);
            }

            window.showModal("sectionModal");
        };

        window.closeSectionModal = () => window.hideModal("sectionModal");
        window.bindModalOverlayClose("sectionModal");
    }

    // =============================================
    // PRINCIPAL — STUDENT MANAGEMENT MODAL
    // (Edit-only — adding new students moved to the Adviser side)
    // =============================================
    const principalStudentForm = document.getElementById("principalStudentForm");
    const studentModalTitle = document.getElementById("studentModalTitle");
    const studentMethod = document.getElementById("studentMethod");
    const studentSubmitBtn = document.getElementById("studentSubmitBtn");

    if (document.getElementById("principalStudentModal") && principalStudentForm) {
        const lrnField = () => document.getElementById("ps_lrn");

        window.openEditStudentModal = function (student) {
            principalStudentForm.reset();
            studentModalTitle.textContent = "Edit Student";
            studentSubmitBtn.textContent = "Update Student";
            principalStudentForm.action = `/principal/students/${student.id}`;
            studentMethod.value = "PUT";

            document.getElementById("ps_lrn").value = student.lrn;
            document.getElementById("ps_last_name").value = student.last_name;
            document.getElementById("ps_first_name").value = student.first_name;
            document.getElementById("ps_middle_name").value = student.middle_name ?? "";
            document.getElementById("ps_gender").value = student.gender;
            document.getElementById("ps_birthdate").value = student.birthdate ?? "";
            document.getElementById("ps_section_id").value = student.section_id;

            // LRN should not be changed while editing
            lrnField().readOnly = true;
            lrnField().classList.add("bg-gray-50", "text-gray-400");

            window.showModal("principalStudentModal");
        };

        const resetLrnField = function () {
            lrnField().readOnly = false;
            lrnField().classList.remove("bg-gray-50", "text-gray-400");
        };

        window.closeStudentModal = function () {
            resetLrnField();
            window.hideModal("principalStudentModal");
        };

        window.bindModalOverlayClose("principalStudentModal", resetLrnField);
    }
});