document.addEventListener('DOMContentLoaded', function() {
    var deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var url = button.getAttribute('data-url');
            var confirmBtn = deleteModal.querySelector('#confirmDeleteBtn');
            confirmBtn.setAttribute('href', url);
        });
    }
});