function initModal() {

    // Close modal triggers
    $('#closeDialog, #cancelDialog').on('click', function () {
        closeModal();
    });

    // Close on outside click
    $('#createUserDialog').on('click', function (e) {
        if (e.target === this) closeModal();
    });

    // Avatar preview or picture preview
    $('#profileInput').on('change', function () {
        const file = this.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (e) {
            $('#avatarPreview').html(`<img src="${e.target.result}" alt="Preview">`);
        };
        reader.readAsDataURL(file);
    });
}

function openModal() {
    $('#createUserDialog').addClass('active');
}

function closeModal() {
    $('#createUserDialog').removeClass('active');
    $('.text-error').text('');
}
