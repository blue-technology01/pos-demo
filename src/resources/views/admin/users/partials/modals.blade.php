{{-- User Modal Partial --}}
<div class="modal-overlay" id="userModal">
    <div class="modal-box">

        <form id="userForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="formMethod" value="POST">

            <h3 id="modalTitle">Create User</h3>

            <div class="avatar-upload">

                <img id="avatarPreview"
                    src="{{ asset('assets/img/default-avatar.png') }}"
                    class="avatar-preview">

                <div class="avatar-upload-info">

                    <p>Profile Photo</p>
                    <span>JPG, PNG up to 2MB</span>

                    <label for="avatarInput" class="avatar-upload-btn">
                        Choose Photo
                    </label>

                    <input type="file" name="avatar" id="avatarInput" accept="image/*">
                </div>
            </div>

                <script>
                    (function(){
                        const userModal = document.getElementById('userModal');
                        const deleteModal = document.getElementById('deleteModal');
                        const closeBtn = document.getElementById('closeModal');
                        const cancelDeleteBtn = document.getElementById('cancelDelete');
                        const avatarInput = document.getElementById('avatarInput');
                        const avatarPreview = document.getElementById('avatarPreview');

                        function close(modal){
                            if(!modal) return;
                            modal.classList.remove('active');
                        }

                        function open(modal){
                            if(!modal) return;
                            modal.classList.add('active');
                        }

                        // Close buttons
                        if(closeBtn) closeBtn.addEventListener('click', ()=> close(userModal));
                        if(cancelDeleteBtn) cancelDeleteBtn.addEventListener('click', ()=> close(deleteModal));

                        // Close when clicking overlay background
                        [userModal, deleteModal].forEach(m => {
                            if(!m) return;
                            m.addEventListener('click', (e)=>{
                                if(e.target === m) close(m);
                            });
                        });

                        // Close on Escape
                        document.addEventListener('keydown', (e)=>{
                            if(e.key === 'Escape'){
                                close(userModal);
                                close(deleteModal);
                            }
                        });

                        // Avatar preview
                        if(avatarInput && avatarPreview){
                            avatarInput.addEventListener('change', (e)=>{
                                const file = e.target.files && e.target.files[0];
                                if(!file) return;
                                const url = URL.createObjectURL(file);
                                avatarPreview.src = url;
                            });
                        }

                        // Expose open/close for other scripts (optional)
                        window.userModalControls = { open: ()=> open(userModal), close: ()=> close(userModal), openDelete: ()=> open(deleteModal), closeDelete: ()=> close(deleteModal) };
                    })();
                </script>

            <input type="text" name="name" id="fieldName" placeholder="Name">
            <input type="email" name="email" id="fieldEmail" placeholder="Email">
            <input type="text" name="phone" id="fieldPhone" placeholder="Phone">

            <select name="role" id="fieldRole">
                <option value="">Select Role</option>
                @foreach($roles as $roleOption)
                    @php $roleName = data_get($roleOption, 'name', $roleOption); @endphp
                    <option value="{{ is_string($roleName) ? $roleName : '' }}">{{ is_string($roleName) ? $roleName : '' }}</option>
                @endforeach
            </select>

            <input type="password" name="password" id="fieldPassword" placeholder="Password">
            <input type="password" name="password_confirmation" id="fieldPasswordConfirm" placeholder="Confirm">

            <button type="submit">Save</button>
            <button type="button" id="closeModal">Cancel</button>
        </form>

    </div>
</div>

{{-- Delete Modal Partial --}}
<div class="modal-overlay" id="deleteModal">
    <div class="modal-box">

        <p>Delete <strong id="deleteUserName"></strong>?</p>

        <form id="deleteForm">
            @csrf
            @method('DELETE')

            <button type="submit">Yes Delete</button>
            <button type="button" id="cancelDelete">Cancel</button>
        </form>

    </div>
</div>
