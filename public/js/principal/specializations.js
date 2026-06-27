function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Specialization';
    document.getElementById('formMethod').value       = 'POST';
    document.getElementById('specForm').action        = SPEC_STORE_URL;
    document.getElementById('specTrack').value        = '';
    document.getElementById('specName').value         = '';
    document.getElementById('specCode').value         = '';
    document.getElementById('specModal').classList.remove('hidden');
}

function openEditModal(spec) {
    document.getElementById('modalTitle').textContent = 'Edit Specialization';
    document.getElementById('formMethod').value       = 'PUT';
    document.getElementById('specForm').action        = `/principal/specializations/${spec.id}`;
    document.getElementById('specTrack').value        = spec.track_id;
    document.getElementById('specName').value         = spec.name;
    document.getElementById('specCode').value         = spec.code;
    document.getElementById('specModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('specModal').classList.add('hidden');
}