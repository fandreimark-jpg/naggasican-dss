function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Track';
    document.getElementById('formMethod').value       = 'POST';
    document.getElementById('trackForm').action       = TRACK_STORE_URL;
    document.getElementById('trackName').value        = '';
    document.getElementById('trackCode').value        = '';
    document.getElementById('trackModal').classList.remove('hidden');
}

function openEditModal(track) {
    document.getElementById('modalTitle').textContent = 'Edit Track';
    document.getElementById('formMethod').value       = 'PUT';
    document.getElementById('trackForm').action       = `/principal/tracks/${track.id}`;
    document.getElementById('trackName').value        = track.name;
    document.getElementById('trackCode').value        = track.code;
    document.getElementById('trackModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('trackModal').classList.add('hidden');
}