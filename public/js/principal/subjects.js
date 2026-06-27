// =============================================
// MODAL OPEN / CLOSE
// =============================================
function openAddModal() {
    document.getElementById('modalTitle').textContent   = 'Add Subject';
    document.getElementById('formMethod').value         = 'POST';
    document.getElementById('subjectForm').action       = SUBJECT_STORE_URL;
    document.getElementById('subjectName').value        = '';
    document.getElementById('subjectType').value        = '';
    document.getElementById('subjectGrade').value       = '';
    document.getElementById('subjectTrack').value       = '';
    document.getElementById('subjectSpec').innerHTML    = '<option value="">— All specializations in track —</option>';
    document.getElementById('trackFields').classList.add('hidden');
    document.getElementById('subjectModal').classList.remove('hidden');
}

function openEditModal(subject) {
    document.getElementById('modalTitle').textContent   = 'Edit Subject';
    document.getElementById('formMethod').value         = 'PUT';
    document.getElementById('subjectForm').action       = `/principal/subjects/${subject.id}`;
    document.getElementById('subjectName').value        = subject.name;
    document.getElementById('subjectType').value        = subject.type;
    document.getElementById('subjectGrade').value       = subject.grade_level;

    // Kung elective, ipakita ang track fields
    if (subject.type === 'elective') {
        document.getElementById('trackFields').classList.remove('hidden');
        document.getElementById('subjectTrack').value = subject.track_id ?? '';

        // I-load ang specializations ng track
        if (subject.track_id) {
            loadSpecializations(subject.track_id, subject.specialization_id);
        }
    } else {
        document.getElementById('trackFields').classList.add('hidden');
    }

    document.getElementById('subjectModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('subjectModal').classList.add('hidden');
}

// =============================================
// TOGGLE TRACK FIELDS
// Kapag Core ang pinili — itago ang track fields
// Kapag Elective ang pinili — ipakita ang track fields
// =============================================
function toggleTrackFields() {
    const type        = document.getElementById('subjectType').value;
    const trackFields = document.getElementById('trackFields');

    if (type === 'elective') {
        trackFields.classList.remove('hidden');
    } else {
        trackFields.classList.add('hidden');
        document.getElementById('subjectTrack').value    = '';
        document.getElementById('subjectSpec').innerHTML = '<option value="">— All specializations in track —</option>';
    }
}

// =============================================
// LOAD SPECIALIZATIONS VIA AJAX
// Kapag nag-select ng track, mag-load ng
// specializations na belong sa track na iyon
// =============================================
function loadSpecializations(trackId, selectedSpecId = null) {
    const specSelect = document.getElementById('subjectSpec');
    specSelect.innerHTML = '<option value="">— Loading... —</option>';

    if (!trackId) {
        specSelect.innerHTML = '<option value="">— All specializations in track —</option>';
        return;
    }

    fetch(`${SPEC_BY_TRACK_URL}/${trackId}`)
        .then(response => response.json())
        .then(data => {
            specSelect.innerHTML = '<option value="">— All specializations in track —</option>';
            data.forEach(spec => {
                const option    = document.createElement('option');
                option.value    = spec.id;
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