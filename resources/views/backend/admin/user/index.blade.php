@extends('backend.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-3">
        <h4>Users List</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal" data-action="add">Add User</button>
    </div>

    <div class="card">
        <div class="card-body">
            <table id="usersTable" class="table table-bordered table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal --}}
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form id="userForm" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="formMethod" value="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 text-center">
                        <img id="preview" src="https://ui-avatars.com/api/?name=User" class="rounded-circle" width="100">
                        <input type="file" name="avatar" id="avatarInput" class="form-control mt-2">
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label>First Name</label>
                            <input type="text" name="first_name" id="firstName" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Last Name</label>
                            <input type="text" name="last_name" id="lastName" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label>Email</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
                        <div class="col-12 password-field">
                            <label>Password</label>
                            <input type="password" name="password" id="password" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let usersTable = $('#usersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.users.data') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable:false, searchable:false },
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable:false, searchable:false }
        ]
    });

    // Avatar preview
    $('#avatarInput').on('change', function () {
        let file = this.files[0];
        if(file) $('#preview').attr('src', URL.createObjectURL(file));
    });

    // Open modal
    $('#userModal').on('show.bs.modal', function(e){
        let btn = $(e.relatedTarget);
        let action = btn.data('action');
        let form = $('#userForm');
        form.trigger('reset');
        $('#preview').attr('src','https://ui-avatars.com/api/?name=User');
        $('.password-field').show();
        form.find('input[name="_method"]').remove();

        if(action === 'add'){
            $('#userModalLabel').text('Add User');
            form.attr('action', '{{ route("admin.users.store") }}');
            $('#formMethod').val('POST');
        }

        if(action === 'edit'){
            let id = btn.data('id');
            $('#userModalLabel').text('Edit User');
            form.attr('action', '{{ route("admin.users.update", ":id") }}'.replace(':id', id));
            $('#formMethod').val('POST'); // Use POST + _method=PUT
            form.append('<input type="hidden" name="_method" value="PUT">');
            $('#firstName').val(btn.data('first_name'));
            $('#lastName').val(btn.data('last_name'));
            $('#email').val(btn.data('email'));
            $('.password-field').hide();
            let avatar = btn.data('avatar') || 'https://ui-avatars.com/api/?name=User';
            $('#preview').attr('src', avatar);
        }
    });

    // Submit form
    $('#userForm').submit(function(e){
        e.preventDefault();
        let formData = new FormData(this);
        $.ajax({
            url: this.action,
            type: $('#formMethod').val(),
            data: formData,
            processData: false,
            contentType: false,
            success: function(res){
                $('#userModal').modal('hide');
                usersTable.ajax.reload(null,false);
                Swal.fire('Success', res.success, 'success');
            },
            error: function(xhr){
                let msg = '';
                if(xhr.responseJSON && xhr.responseJSON.errors){
                    $.each(xhr.responseJSON.errors, (_, v) => msg += v[0]+'<br>');
                } else { msg = 'Something went wrong!'; }
                Swal.fire('Error', msg, 'error');
            }
        });
    });

    // Delete
    $(document).on('click','.delete-user', function(){
        let id = $(this).data('id');
        Swal.fire({
            title: 'Delete user?',
            icon: 'warning',
            showCancelButton: true
        }).then(res => {
            if(res.isConfirmed){
                $.ajax({
                    url: '{{ route("admin.users.destroy", ":id") }}'.replace(':id', id),
                    type: 'DELETE',
                    data: {_token:'{{ csrf_token() }}'},
                    success: () => {
                        usersTable.ajax.reload(null,false);
                        Swal.fire('Deleted', 'User removed', 'success');
                    }
                });
            }
        });
    });
});
</script>
@endsection
