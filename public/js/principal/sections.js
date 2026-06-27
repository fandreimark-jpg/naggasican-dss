// =============================================
// MODAL OPEN / CLOSE
// =============================================
function openAddModal() {
    document.getElementById('modalTitle').textContent  = 'Add Section';
    document.getElementById('sectionMethod').value     = 'POST';
    document.getElementById('sectionForm').action      = SECTION_STORE_URL;
    document.getElementById('sectionName').value       = '';
    document.getElementById('sectionGrade').value      = '';
    document.getElementById('sectionTrack').value      = '';
    document.getElementById('sectionSchoolYear').value = '2026-2027';
    document.getElementById('sectionSpec').innerHTML   = '<option value="">— Select Track First —</option>';
    document.getElementById('sectionModal').classList.remove('hidden');

    // ✅ FIX — Sa Add modal, itago ang advisers na may section na
    const adviserSelect = document.getElementById('sectionAdviser');

    // I-show muna lahat bago mag-filter
    Array.from(adviserSelect.options).forEach(option => {
        option.style.display = '';
    });

    // Itago lang ang advisers na assigned sa IBANG section
    Array.from(adviserSelect.options).forEach(option => {
        if (
            option.value !== '' &&
            option.dataset.sectionId !== '' &&
            parseInt(option.dataset.sectionId) !== parseInt(section.id)
        ) {
            option.style.display = 'none';
        }
    });

    // I-set ang current adviser
    adviserSelect.value = section.adviser_id ?? '';
}

function openEditModal(section) {
    document.getElementById('modalTitle').textContent  = 'Edit Section';
    document.getElementById('sectionMethod').value     = 'PUT';
    document.getElementById('sectionForm').action      = `/principal/sections/${section.id}`;
    document.getElementById('sectionName').value       = section.name;
    document.getElementById('sectionGrade').value      = section.grade_level;
    document.getElementById('sectionTrack').value      = section.track_id ?? '';
    document.getElementById('sectionSchoolYear').value = section.school_year;
    document.getElementById('sectionModal').classList.remove('hidden');

    // ✅ FIX — Sa Edit modal, ipakita lahat ng advisers
    // (kasama ang current adviser ng section kahit may section na)
    const adviserSelect = document.getElementById('sectionAdviser');
    Array.from(adviserSelect.options).forEach(option => {
        // Ipakita lahat MALIBAN sa advisers na may ibang section
        // (ang adviser ng current section ay dapat makita pa rin)
        if (option.value !== '' &&
            option.dataset.sectionId !== '' &&
            option.dataset.sectionId != section.id) {
            option.style.display = 'none';
        } else {
            option.style.display = '';
        }
    });

    adviserSelect.value = section.adviser_id ?? '';

    if (section.track_id) {
        loadSpecializations(section.track_id, section.specialization_id);
    }
}

function closeModal() {
    document.getElementById('sectionModal').classList.add('hidden');
}

// =============================================
// LOAD SPECIALIZATIONS VIA AJAX
// =============================================
function loadSpecializations(trackId, selectedSpecId = null) {
    const specSelect = document.getElementById('sectionSpec');
    specSelect.innerHTML = '<option value="">— Loading... —</option>';

    if (!trackId) {
        specSelect.innerHTML = '<option value="">— Select Track First —</option>';
        return;
    }

    fetch(`${SPEC_BY_TRACK_URL}/${trackId}`)
        .then(response => response.json())
        .then(data => {
            specSelect.innerHTML = '<option value="">— Select Specialization —</option>';

            if (data.length === 0) {
                specSelect.innerHTML = '<option value="">— No specializations for this track —</option>';
                return;
            }

            data.forEach(spec => {
                const option       = document.createElement('option');
                option.value       = spec.id;
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
}