@extends('admins.layouts.master')

@section('header')
<div class="page-header page-header-light">
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
                    <div class="row align-items-center justify-content-center">
                        <div class="col-sm-auto col-12 text-center">
                            <div class="custom-control custom-checkbox custom-control-inline">
                                <input type="checkbox" class="custom-control-input" name="doi_sim" id="doi_sim" checked>
                                <label class="custom-control-label" for="doi_sim">Đổi SIM</label>
                            </div>

                            <div class="custom-control custom-checkbox custom-control-inline">
                                <input type="checkbox" class="custom-control-input" name="lay_qr" id="lay_qr" checked>
                                <label class="custom-control-label" for="lay_qr">Lấy mã QR ESIM</label>
                            </div>
                        </div>
                        <div class="col-sm-auto col">
                            <div class="row align-items-center justify-content-center">
                                <div class="col-form-label col-auto">
                                    <strong class="ml-2">Chờ (s)</strong>
                                </div>
                                <div class="col-auto">
                                    <input type="number" name="delay" class="form-control form-control-sm" value=1 style="width:4rem">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-auto col-12">
                    <div class="row justify-content-center">
                        <div class="col-sm-auto col-6">
                            <button class="btn btn-outline-success btn-block h-100 btn-run">
                                <h2><i class="fa-solid fa-play mr-2"></i>CHẠY</h2>
                            </button>
                        </div>
                        <div class="col-sm-auto col-6">
                            <button class="btn btn-outline-secondary btn-block h-100 btn-stop">
                                <h2><i class="fa-solid fa-pause mr-2"></i>DỪNG</h2>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="breadcrumb-line breadcrumb-line-light header-elements-lg-inline">
        <div class="d-flex">
            <div class="breadcrumb">
                <span class="breadcrumb-item active">Danh sách</span>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-auto col-12 mb-2">
        <textarea name="list" cols="30" rows="5" class="form-control border-dark rounded-0 bg-white w-lg-auto w-100"></textarea>
    </div>
    <div class="col">
        <div class="table-responsive">
            <table class="table table-bordered table-xs bg-white">
                <thead class="text-nowrap">
                    <tr>
                        <th class="text-center">STT</th>
                        <th class="text-center">Số TB</th>
                        <th class="text-center">SIM</th>
                        <th class="text-center">Ghi chú</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-center">Link QR</th>
                        <th class="text-center">QR Code</th>
                    </tr>
                </thead>
                <tbody id="progress_list">
                    
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var cookies = "{{ $cookies }}";

    $(document).ready(function() {
        $(document).on('click', '.btn-login', function() {
            let username = $('.login input[name="username"]').val();
            let password = $('.login input[name="password"]').val();

            if (username == "" || password == "") {
                noty('Nhập đầy đủ thông tin để tiếp tục đăng nhập!', 'error');
                return;
            }

            $.ajax({
                type: 'POST',
                url: "{{ route('login.auth') }}",
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
                }
            });
        });

        $(document).on('input', 'input[name="delay"]', function() {
            if ($(this).val() < 0) $(this).val(0);
        });

        $(document).on('click', '.btn-run', function() {
            let list = $('textarea[name="list"]').val();
            let doi_sim = $('input[name="doi_sim"]').is(":checked");
            let lay_qr = $('input[name="lay_qr"]').is(":checked");
            let delay = $('input[name="delay"]').val();

            if (cookies == '') {
                noty('Không có Cookie, đăng nhập lại để tiếp tục!', 'error');
                return;
            }

            if (list == '') {
                noty('Vui lòng nhập dữ liệu!', 'error');
                return;
            }
            
            if (!doi_sim && !lay_qr) {
                noty('Chọn chức năng để tiếp tục!', 'error');
                return;
            }

            let lines = list.split("\n");
            let stt = 1;
            $('#progress_list').html('');
            lines.forEach(line => {
                if (line == "") return;

                let boline = line.split("\t");
                let row = $('<tr></tr>');
                row.append($('<td>' + stt++ + '</td>'));
                row.append($('<td>' + boline[0] + '</td>'));
                row.append($('<td>' + boline[1] + '</td>'));
                row.append($('<td>' + boline[2] + '</td>'));
                row.append($('<td></td>'));
                row.append($('<td></td>'));
                row.append($('<td></td>'));

                $('#progress_list').append(row);

                if (doi_sim) doisim(row, boline);
            });
        });

        function doisim(row, boline) {
            let sdt = boline[0];
            let esim = boline[1];
            let ghichu = boline[2];
    
            if (esim == '') {
                row.children().eq(3).text('Không có SIM!');
                return;
            }
            row.children().eq(4).text('Bắt đầu đổi sim ...');
    
            $.ajax({
                type: 'POST',
                url: "{{ route('doi-sim.post') }}",
                data: {
                    'sdt': sdt,
                    'esim': esim,
                    'ghichu': ghichu,
                },
                success: function(data) {
                    
                },
                error: function(xhr, status, error) {
                    row.children().eq(4).text('Đã xảy ra lỗi!');
                }
            });
        }
    });
</script>
@endpush