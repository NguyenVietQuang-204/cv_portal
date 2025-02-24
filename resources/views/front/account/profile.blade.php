@extends('front.layouts.app')

@section('main')

<section class="section-5 bg-2">
    <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class=" rounded-3 p-3 mb-4">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="#">Trang chủ</a></li>
                        <li class="breadcrumb-item active">Cài đặt tài khoản</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3">
                @include('front.account.sidebar')
            </div>
            <div class="col-lg-9">
                @include('front.message')
                <div class="card border-0 shadow mb-4">
                    <form action="" method="post" id="userForm" name="userForm">
                        <div class="card-body  p-4">
                            <h3 class="fs-4 mb-1">Hồ sơ của tôi</h3>
                            <div class="mb-4">
                                <label for="" class="mb-2">Tên*</label>
                                <input type="text" name="name" id="name" placeholder="Nhập Name" class="form-control" value="{{ $user->name }}">
                                <p></p>
                            </div>
                            <div class="mb-4">
                                <label for="" class="mb-2">Email*</label>
                                <input type="text" name="email" id="email" placeholder="Nhập Email" class="form-control" value="{{ $user->email }}">
                                <p></p>
                            </div>
                            <div class="mb-4">
                                <label for="" class="mb-2">Chức vụ & vị trí</label>
                                <input type="text" name="designation" id="designation" placeholder="Designation" class="form-control" value="{{ $user->designation }}">
                            </div>
                            <div class="mb-4">
                                <label for="" class="mb-2">Mobile</label>
                                <input type="text" name="mobile" id="mobile" placeholder="Mobile" class="form-control" value="{{ $user->mobile }}">
                            </div>                        
                        </div>
                        <div class="card-footer  p-4">
                            <button type="submit" class="btn btn-primary">Cập nhật</button>
                        </div>
                    </form>
                </div>

                <div class="card border-0 shadow mb-4">
                    <form action="" method="post" id="changePasswordForm" name="changePasswordForm">
                        <div class="card-body p-4">
                            <h3 class="fs-4 mb-1">Thay đổi mật khẩu</h3>
                            <div class="mb-4">
                                <label for="" class="mb-2">Mật khẩu cũ*</label>
                                <input type="password" name="old_password" id="old_password" placeholder="Mật khẩu cũ" class="form-control">
                                <p></p>
                            </div>
                            <div class="mb-4">
                                <label for="" class="mb-2">Mật khẩu mới*</label>
                                <input type="password" name="new_password" id="new_password" placeholder="Mật khẩu mới" class="form-control">
                                <p></p>
                            </div>
                            <div class="mb-4">
                                <label for="" class="mb-2">Xác nhận mật khẩu*</label>
                                <input type="password" name="confirm_password" id="confirm_password" placeholder="Xác nhận mật khẩu" class="form-control">
                                <p></p>
                            </div>                        
                        </div>
                        <div class="card-footer  p-4">
                            <button type="submit" class="btn btn-primary">Cập nhật</button>
                        </div>
                    </form>
                </div>   

            </div>
        </div>
    </div>
</section>

@endsection


@section('customJs')

<script type="text/javascript">

$("#userForm").submit(function(e){
    e.preventDefault();

    $.ajax({
        url:'{{ route("account.updateProfile") }}',
        type: 'put',
        dataType: 'json',
        data: $("#userForm").serializeArray(),
        success:function(response) {

            if(response.status == true) {

                $("#name").removeClass('is-invalid')
                    .siblings('p')
                    .removeClass('invalid-feedback')
                    .html('')
                
                $("#email").removeClass('is-invalid')
                    .siblings('p')
                    .removeClass('invalid-feedback')
                    .html('')

                window.location.href="{{ route('account.profile') }}";

            } else {
                var errors = response.errors;

                if(errors.name){
                    $("#name").addClass('is-invalid')
                    .siblings('p')
                    .addClass('invalid-feedback')
                    .html(errors.name) 
                }else{
                    $("#name").removeClass('is-invalid')
                    .siblings('p')
                    .removeClass('invalid-feedback')
                    .html('')
                }
                if(errors.email){
                    $("#email").addClass('is-invalid')
                    .siblings('p')
                    .addClass('invalid-feedback')
                    .html(errors.email) 
                }else{
                    $("#email").removeClass('is-invalid')
                    .siblings('p')
                    .removeClass('invalid-feedback')
                    .html('')
                }
            }
        }
    });
});


$("#changePasswordForm").submit(function(e){
    e.preventDefault();

    $.ajax({
        url:'{{ route("account.updatePassword") }}',
        type: 'post',
        dataType: 'json',
        data: $("#changePasswordForm").serializeArray(),
        success:function(response) {

            if(response.status == true) {

                $("#name").removeClass('is-invalid')
                    .siblings('p')
                    .removeClass('invalid-feedback')
                    .html('')
                
                $("#email").removeClass('is-invalid')
                    .siblings('p')
                    .removeClass('invalid-feedback')
                    .html('')

                window.location.href="{{ route('account.profile') }}";

            } else {
                var errors = response.errors;

                if(errors.old_password){
                    $("#old_password").addClass('is-invalid')
                    .siblings('p')
                    .addClass('invalid-feedback')
                    .html(errors.old_password) 
                }else{
                    $("#old_password").removeClass('is-invalid')
                    .siblings('p')
                    .removeClass('invalid-feedback')
                    .html('')
                }
                if(errors.new_password){
                    $("#new_password").addClass('is-invalid')
                    .siblings('p')
                    .addClass('invalid-feedback')
                    .html(errors.new_password) 
                }else{
                    $("#new_password").removeClass('is-invalid')
                    .siblings('p')
                    .removeClass('invalid-feedback')
                    .html('')
                }
                if(errors.confirm_password){
                    $("#confirm_password").addClass('is-invalid')
                    .siblings('p')
                    .addClass('invalid-feedback')
                    .html(errors.confirm_password) 
                }else{
                    $("#confirm_password").removeClass('is-invalid')
                    .siblings('p')
                    .removeClass('invalid-feedback')
                    .html('')
                }
            }
        }
    });
});

</script>
@endsection


