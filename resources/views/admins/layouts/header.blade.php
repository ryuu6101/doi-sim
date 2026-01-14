<!-- Page header -->
<div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex pb-2">
                <div class="row">
                    <div class="col-lg-auto col-12 mb-lg-0 mb-2">
                        <div class="row align-items-center justify-content-center login mb-2">
                            <div class="col-auto col-form-label d-sm-block d-none">
                                <strong>Tài khoản:</strong>
                            </div>
                            <div class="col-sm-auto col">
                                <input type="text" class="form-control form-control-sm border-dark" name="username" value="{{ $username }}">
                            </div>
                            <div class="col-auto col-form-label d-sm-block d-none">
                                <strong>#</strong>
                            </div>
                            <div class="col-sm-auto col">
                                <input type="text" class="form-control form-control-sm border-dark" name="password" value="{{ $password }}">
                            </div>
                            <div class="col-sm-auto col-auto">
                                <button type="button" class="btn btn-sm btn-outline-primary btn-login">Đăng nhập</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- /page header -->

@push('scripts')
<script>
    let cookies = "{{ $cookies }}";

    $(document).ready(function() {
        $(document).on('click', '.btn-login', function() {
            let username = $('.login input[name="username"]').val();
            let password = $('.login input[name="password"]').val();

            if (username == "" || password == "") {
                noty('Nhập đầy đủ thông tin để tiếp tục đăng nhập!', 'error');
                return;
            }

            $('.btn-login').prop('disabled', true);

            $.ajax({
                type: 'POST',
                url: "{{ route('ccbs-login.post') }}",
                data: {
                    'username': username,
                    'password': password,
                },
                success: function(data) {
                    cookies = data['cookies'];
                    noty(data['message'], data['status'] == 0 ? 'error' : 'success');
                },
                error: function(xhr, status, error) {
                    noty('Đã xảy ra lỗi!', 'error');
                },
                complete: function() {
                    $('.btn-login').prop('disabled', false);                    
                }
            });
        });
    });
</script>
@endpush