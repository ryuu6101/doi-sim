<!-- Page header -->
<div class="page-header page-header-light">
    <div class="page-header-content header-elements-lg-inline">
        <div class="page-header-content header-elements-lg-inline">
            <div class="page-title d-flex pb-2">
                <div class="row">
                    <div class="col-lg-auto col-12 mb-lg-0 mb-2">
                        <div class="row align-items-center justify-content-center login mb-2">
                            <div class="col-auto col-form-label d-sm-block d-none">
                                <strong>Cookie:</strong>
                            </div>
                            <div class="col-sm-auto col">
                                <input type="text" class="form-control form-control-sm border-dark" name="cookies" value="{{ $cookies_ccos }}">
                            </div>
                            <div class="col-sm-auto col-auto">
                                <button type="button" class="btn btn-sm btn-outline-primary btn-login">
                                    <i class="fa-solid fa-floppy-disk mr-1"></i>
                                    Lưu
                                </button>
                            </div>
                            <div class="col-sm-auto col-auto d-none d-lg-block">
                                <h5 class="cursor-pointer text-muted" data-toggle="modal" data-target="#huongDanLayCookie">
                                    <i class="fa-regular fa-circle-question"></i>
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /page header -->

@push('modals')
<div id="huongDanLayCookie" class="modal fade" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-full">
        <div class="modal-content">
            <div class="modal-body">
                <img src="{{ asset('images/Screenshot 2026-01-15 120454.png') }}" alt="" class="img-fluid">
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    let cookies = "{{ $cookies_ccos }}";

    $(document).ready(function() {
        $(document).on('click', '.btn-login', function() {
            let cookies_input = $('.login input[name="cookies"]').val();
            
            if (cookies_input == "") {
                noty('Vui lòng nhập Cookie!', 'error');
                return;
            }

            $('.btn-login').prop('disabled', true);

            $.ajax({
                type: 'POST',
                url: "{{ route('save-cookie.post') }}",
                data: {'cookies': cookies_input},
                success: function(data) {
                    cookies = cookies_input;
                    noty('Đã lưu Cookie', 'success');
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