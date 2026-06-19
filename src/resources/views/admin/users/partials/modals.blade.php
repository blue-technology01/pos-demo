{{-- ══ User Modal ══ --}}
<div class="modal-overlay" id="userModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal-box">

        <form id="userForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="formMethod" value="POST">

            {{-- Modal header --}}
            <div class="modal-box__header">
                <h3 class="modal-box__title" id="modalTitle">
                    <span class="material-symbols-outlined">person_add</span>
                    Create user
                </h3>
                <button type="button" class="modal-box__close" id="closeModal" aria-label="Close">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            {{-- Modal body --}}
            <div class="modal-box__body">

                {{-- Avatar upload --}}
                <div class="avatar-upload">
                    <img
                        id="avatarPreview"
                        src="{{ asset('assets/img/default-avatar.png') }}"
                        class="avatar-preview"
                        alt="Avatar preview"
                    >
                    <div class="avatar-upload-info">
                        <p class="avatar-upload-info__title">Profile photo</p>
                        <span class="avatar-upload-info__hint">JPG or PNG, max 2MB</span>
                        <label for="avatarInput" class="avatar-upload-btn">
                            <span class="material-symbols-outlined">upload</span>
                            Choose photo
                        </label>
                        <input type="file" name="avatar" id="avatarInput" accept="image/*" style="display:none">
                    </div>
                </div>

                {{-- Fields --}}
                <div class="modal-form-group">
                    <label class="modal-form-label" for="fieldName">
                        Full name <span class="modal-req">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="fieldName"
                        class="modal-form-control"
                        placeholder="Enter full name"
                        autocomplete="off"
                        required
                    >
                </div>

                <div class="modal-form-group">
                    <label class="modal-form-label" for="fieldEmail">
                        Email address <span class="modal-req">*</span>
                    </label>
                    <input
                        type="email"
                        name="email"
                        id="fieldEmail"
                        class="modal-form-control"
                        placeholder="Enter email address"
                        autocomplete="off"
                        required
                    >
                </div>

                <div class="modal-form-row">
                    <div class="modal-form-group">
                        <label class="modal-form-label" for="fieldPhone">Phone number</label>
                        <input
                            type="text"
                            name="phone"
                            id="fieldPhone"
                            class="modal-form-control"
                            placeholder="Enter phone"
                            autocomplete="off"
                        >
                    </div>
                    <div class="modal-form-group">
                        <label class="modal-form-label" for="fieldRole">
                            Role <span class="modal-req">*</span>
                        </label>
                        <select name="role" id="fieldRole" class="modal-form-control" required>
                            <option value="">Select role</option>
                            @foreach($roles as $roleOption)
                                @php $roleName = data_get($roleOption, 'name', $roleOption); @endphp
                                <option value="{{ is_string($roleName) ? $roleName : '' }}">
                                    {{ is_string($roleName) ? ucfirst($roleName) : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="modal-form-row">
                    <div class="modal-form-group">
                        <label class="modal-form-label" for="fieldPassword">
                            Password <span class="modal-req">*</span>
                        </label>
                        <input
                            type="password"
                            name="password"
                            id="fieldPassword"
                            class="modal-form-control"
                            placeholder="Enter password"
                        >
                    </div>
                    <div class="modal-form-group">
                        <label class="modal-form-label" for="fieldPasswordConfirm">
                            Confirm password <span class="modal-req">*</span>
                        </label>
                        <input
                            type="password"
                            name="password_confirmation"
                            id="fieldPasswordConfirm"
                            class="modal-form-control"
                            placeholder="Confirm password"
                        >
                    </div>
                </div>

            </div>

            {{-- Modal footer --}}
            <div class="modal-box__footer">
                <button type="button" class="modal-btn-cancel" id="closeModalFooter">
                    <span class="material-symbols-outlined">close</span> Cancel
                </button>
                <button type="submit" class="modal-btn-save">
                    <span class="material-symbols-outlined">save</span> Save user
                </button>
            </div>

        </form>
    </div>
</div>

{{-- ══ Delete Confirm Modal ══ --}}
<div class="modal-overlay" id="deleteModal" role="dialog" aria-modal="true">
    <div class="modal-box modal-box--sm">

        <div class="modal-box__header">
            <h3 class="modal-box__title modal-box__title--danger">
                <span class="material-symbols-outlined">delete_forever</span>
                Delete user
            </h3>
            <button type="button" class="modal-box__close" id="cancelDelete" aria-label="Close">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <div class="modal-box__body">
            <div class="delete-confirm-wrap">
                <div class="delete-confirm-icon">
                    <span class="material-symbols-outlined">warning</span>
                </div>
                <p class="delete-confirm-text">
                    Are you sure you want to delete
                    <strong id="deleteUserName"></strong>?
                    <br>
                    <span>This action cannot be undone.</span>
                </p>
            </div>
        </div>

        <form id="deleteForm">
            @csrf
            @method('DELETE')
            <div class="modal-box__footer">
                <button type="button" class="modal-btn-cancel" id="cancelDeleteFooter">
                    <span class="material-symbols-outlined">close</span> Cancel
                </button>
                <button type="submit" class="modal-btn-delete">
                    <span class="material-symbols-outlined">delete</span> Yes, delete
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══ Modal scripts ══ --}}
<script>
(function () {

    const userModal   = document.getElementById('userModal');
    const deleteModal = document.getElementById('deleteModal');

    function openModal(modal) {
        if (!modal) return;
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modal) {
        if (!modal) return;
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    // Close buttons
    ['closeModal', 'closeModalFooter'].forEach(id => {
        document.getElementById(id)?.addEventListener('click', () => closeModal(userModal));
    });

    ['cancelDelete', 'cancelDeleteFooter'].forEach(id => {
        document.getElementById(id)?.addEventListener('click', () => closeModal(deleteModal));
    });

    // Backdrop click
    [userModal, deleteModal].forEach(m => {
        m?.addEventListener('click', e => {
            if (e.target === m) closeModal(m);
        });
    });

    // Escape key
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closeModal(userModal);
            closeModal(deleteModal);
        }
    });

    // Avatar preview
    const avatarInput   = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');

    avatarInput?.addEventListener('change', e => {
        const file = e.target.files?.[0];
        if (!file) return;
        avatarPreview.src = URL.createObjectURL(file);
    });

    // Expose controls globally
    window.userModalControls = {
        open       : () => openModal(userModal),
        close      : () => closeModal(userModal),
        openDelete : () => openModal(deleteModal),
        closeDelete: () => closeModal(deleteModal),
    };

})();
</script>
