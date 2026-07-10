// resources/js/modal.js
// Lahat ng modal functions para sa buong system

document.addEventListener("DOMContentLoaded", function () {
    // =============================================
    // ADVISER — EDIT STUDENT MODAL
    // =============================================
    const editStudentModal = document.getElementById("editModal");
    const editStudentForm = document.getElementById("editForm");

    if (editStudentModal && editStudentForm) {
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
            editStudentModal.classList.remove("hidden");
        };

        window.closeEditModal = function () {
            editStudentModal.classList.add("hidden");
        };

        editStudentModal.addEventListener("click", function (e) {
            if (e.target === editStudentModal) window.closeEditModal();
        });
    }

    // =============================================
    // PRINCIPAL — USER MANAGEMENT MODAL
    // =============================================
    const userModal = document.getElementById("userModal");
    const userForm = document.getElementById("userForm");
    const userTitle = document.getElementById("modalTitle");
    const userMethod = document.getElementById("formMethod");
    const userSubmit = document.getElementById("submitBtn");
    const passwordNote = document.getElementById("passwordNote");

    if (userModal && userForm) {
        const storeUrl = userForm.dataset.storeUrl;

        window.openAddModal = function () {
            userForm.reset();
            userTitle.textContent = "Add User";
            userSubmit.textContent = "Save User";
            userForm.action = storeUrl;
            userMethod.value = "POST";
            document.getElementById("field_password").required = true;
            if (passwordNote) passwordNote.classList.add("hidden");
            userModal.classList.remove("hidden");
        };

        window.openUserEditModal = function (user) {
            userForm.reset();
            userTitle.textContent = "Edit User";
            userSubmit.textContent = "Update User";
            userForm.action = `/principal/users/${user.id}`;
            userMethod.value = "PUT";

            document.getElementById("field_last_name").value =
                user.last_name ?? "";
            document.getElementById("field_first_name").value =
                user.first_name ?? "";
            document.getElementById("field_middle_name").value =
                user.middle_name ?? "";

            const username = user.email
                ? user.email.replace("@naggasican.edu.ph", "")
                : "";
            document.getElementById("field_username").value = username;
            document.getElementById("field_role").value = user.role;
            document.getElementById("field_password").required = false;
            if (passwordNote) passwordNote.classList.remove("hidden");

            userModal.classList.remove("hidden");
        };

        window.closeUserModal = function () {
            userModal.classList.add("hidden");
        };

        userModal.addEventListener("click", function (e) {
            if (e.target === userModal) window.closeUserModal();
        });
    }

    // =============================================
    // PRINCIPAL — SECTION MANAGEMENT MODAL
    // =============================================
    const sectionModal = document.getElementById("sectionModal");
    const sectionForm = document.getElementById("sectionForm");
    const sectionTitle = document.getElementById("sectionModalTitle");
    const sectionMethod = document.getElementById("sectionMethod");
    const sectionSubmit = document.getElementById("sectionSubmitBtn");

    if (sectionModal && sectionForm) {
        const sectionStoreUrl = sectionForm.dataset.storeUrl;

        window.openAddSectionModal = function () {
            sectionForm.reset();
            sectionTitle.textContent = "Add Section";
            sectionSubmit.textContent = "Save Section";
            sectionForm.action = sectionStoreUrl;
            sectionMethod.value = "POST";

            // I-restore ang "available advisers only" para sa Add
            const mainSelect = document.getElementById("section_adviser");
            const availableSelect = document.getElementById(
                "section_adviser_available",
            );
            if (mainSelect && availableSelect) {
                mainSelect.innerHTML = availableSelect.innerHTML;
            }

            sectionModal.classList.remove("hidden");
        };

        window.openEditSectionModal = function (section) {
            sectionForm.reset();
            sectionTitle.textContent = "Edit Section";
            sectionSubmit.textContent = "Update Section";
            sectionForm.action = `/principal/sections/${section.id}`;
            sectionMethod.value = "PUT";

            document.getElementById("section_name").value = section.name;
            document.getElementById("section_grade").value =
                section.grade_level;
            document.getElementById("section_school_year").value =
                section.school_year;

            const mainSelect = document.getElementById("section_adviser");
            const allSelect = document.getElementById("section_adviser_all");

            if (mainSelect && allSelect) {
                // I-clear muna ang main select
                mainSelect.innerHTML =
                    '<option value="">— No Adviser —</option>';

                // I-loop ang lahat ng options mula sa hidden select
                // Ipakita lang ang:
                // 1. Adviser na naka-assign sa section na ine-edit (current adviser)
                // 2. Advisers na walang section pa (data-section-id ay empty)
                Array.from(allSelect.options).forEach(function (option) {
                    if (option.value === "") return; // Skip ang "No Adviser" option, naidagdag na

                    const assignedSectionId =
                        option.getAttribute("data-section-id");
                    const isCurrentAdviser =
                        String(option.value) === String(section.adviser_id);
                    const isAvailable =
                        assignedSectionId === "" || assignedSectionId === null;

                    // Ipakita kung current adviser NG section na ine-edit OR walang section pa
                    if (isCurrentAdviser || isAvailable) {
                        mainSelect.appendChild(option.cloneNode(true));
                    }
                });

                // I-set ang current adviser
                mainSelect.value = section.adviser_id ?? "";
            }

            sectionModal.classList.remove("hidden");
        };

        window.closeSectionModal = function () {
            sectionModal.classList.add("hidden");
        };

        sectionModal.addEventListener("click", function (e) {
            if (e.target === sectionModal) window.closeSectionModal();
        });
    }

    // =============================================
    // PRINCIPAL — STUDENT MANAGEMENT MODAL
    // (Edit-only — adding new students moved to the Adviser side)
    // =============================================
    const principalStudentModal = document.getElementById(
        "principalStudentModal",
    );
    const principalStudentForm = document.getElementById(
        "principalStudentForm",
    );
    const studentModalTitle = document.getElementById("studentModalTitle");
    const studentMethod = document.getElementById("studentMethod");
    const studentSubmitBtn = document.getElementById("studentSubmitBtn");

    if (principalStudentModal && principalStudentForm) {
        window.openEditStudentModal = function (student) {
            principalStudentForm.reset();
            studentModalTitle.textContent = "Edit Student";
            studentSubmitBtn.textContent = "Update Student";
            principalStudentForm.action = `/principal/students/${student.id}`;
            studentMethod.value = "PUT";

            document.getElementById("ps_lrn").value = student.lrn;
            document.getElementById("ps_last_name").value = student.last_name;
            document.getElementById("ps_first_name").value = student.first_name;
            document.getElementById("ps_middle_name").value =
                student.middle_name ?? "";
            document.getElementById("ps_gender").value = student.gender;
            document.getElementById("ps_birthdate").value =
                student.birthdate ?? "";
            document.getElementById("ps_section_id").value = student.section_id;

            // Disable the LRN field while editing — it should not be changed here
            document.getElementById("ps_lrn").readOnly = true;
            document
                .getElementById("ps_lrn")
                .classList.add("bg-gray-50", "text-gray-400");

            principalStudentModal.classList.remove("hidden");
        };

        window.closeStudentModal = function () {
            // Re-enable the LRN field for the next time the modal opens
            document.getElementById("ps_lrn").readOnly = false;
            document
                .getElementById("ps_lrn")
                .classList.remove("bg-gray-50", "text-gray-400");
            principalStudentModal.classList.add("hidden");
        };

        principalStudentModal.addEventListener("click", function (e) {
            if (e.target === principalStudentModal) window.closeStudentModal();
        });
    }
});