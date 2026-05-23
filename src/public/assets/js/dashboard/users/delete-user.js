    function initDeleteUser() {

        $(document).on('click', '.btn-delete', function () {
            const id = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: 'This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff0000',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: window.userRoutes.destroy.replace(':id', id),
                    type: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                    },

                    success: function () {
                        // Remove from cache instantly — no re-fetch needed
                        window.usersCache = window.usersCache.filter(u => u.id != id);
                        renderUsers(window.usersCache);

                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            timer: 1200,
                            showConfirmButton: false,
                        });
                    },

                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Failed to delete user.',
                        });
                    },
                });
            });
        });
    }
