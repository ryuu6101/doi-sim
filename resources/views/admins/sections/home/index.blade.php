@extends('admins.layouts.master')

@section('content')
<div class="row flex-lg-nowrap">

    <div class="col-lg-auto col-12 mb-2">
        <div class="card">
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col">
                        <strong>Chức năng:</strong>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col">
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input type="checkbox" class="custom-control-input" name="doi_sim" id="doi_sim" checked>
                            <label class="custom-control-label" for="doi_sim">Đổi SIM</label>
                        </div>

                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input type="checkbox" class="custom-control-input" name="lay_qr" id="lay_qr" checked>
                            <label class="custom-control-label" for="lay_qr">Lấy mã QR ESIM</label>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row mb-2">
                    <div class="col-6">
                        <button class="btn btn-outline-success btn-block btn-run">
                            <i class="fa-solid fa-play mr-2"></i>CHẠY
                        </button>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-outline-secondary btn-block btn-stop">
                            <i class="fa-solid fa-pause mr-2"></i>DỪNG
                        </button>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col">
                        <textarea name="list" cols="30" rows="5" class="form-control border-dark rounded-0 bg-light w-lg-auto w-100"></textarea>
                    </div>
                </div>
            </div>
        </div>
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
                <tfoot>
                    <tr>
                        <td colspan="100%" class="text-center" id="tb_footer">(Chưa có dữ liệu)</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var thongbaos = {
        "1" : "Đã đặt lệnh đổi SIM cho số thuê bao", 
        "2" : "Đã đặt lệnh đổi SIM cho số thuê bao (có tạo AC cho SIM mới)", 
        "-1000" : "Lỗi khi đổi SIM cho thuê bao  (do khác tỉnh quản lý!!!)", 
        "-1002" : "Lỗi khi đổi SIM thuê bao  TB Blacklist!", 
        "-3010" : "Thuê bao không có trên hệ thống IN-Eric", 
        "4006" : " Thuê bao không có trên hệ thống IN-Comv ", 
    };

    var doi_sim = true;
    var lay_qr = true;
    var delay = 1;
    var lines = [];
    var index = 0;
    var total = 0;

    $(document).ready(function() {
        $(document).on('input', 'input[name="delay"]', function() {
            if ($(this).val() < 0) $(this).val(0);
        });

        $(document).on('click', '.btn-run', function() {
            let list = $('textarea[name="list"]').val();

            doi_sim = $('input[name="doi_sim"]').is(":checked");
            lay_qr = $('input[name="lay_qr"]').is(":checked");
            delay = $('input[name="delay"]').val() ?? 1;

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

            lines = list.split("\n").filter((line) => {return line != ""});
            index = 0;
            total = lines.length;

            $('#progress_list').html('');
            $('#tb_footer').removeClass('d-none');
            $('#tb_footer').html('<i class="fa-solid fa-spinner spinner ml-2"></i>Vui lòng không đóng hoặc tải lại trang');
            $('.btn-run').prop('disabled', true);

            chay();
        });

        async function chay() {
            let boline = lines[index++].split(/[\t|]/);
            let row = $('<tr></tr>');

            row.append($('<td class="text-center">' + (index) + '</td>'));
            row.append($('<td>' + (boline[0] ?? '') + '</td>'));
            row.append($('<td>' + (boline[1] ?? '') + '</td>'));
            row.append($('<td>' + (boline[2] ?? '') + '</td>'));
            row.append($('<td></td>'));
            row.append($('<td></td>'));
            row.append($('<td class="text-nowrap"></td>'));

            $('#progress_list').append(row);

            if (doi_sim) await doisim(row, boline) && lay_qr && await layqr(row, boline);
            else if (lay_qr) await layqr(row, boline);

            if (index < total) setTimeout(chay, delay * 1000);
            else stop();
        }

        async function doisim(row, boline) {
            let sdt = boline[0];
            let esim = boline[1];
            let ghichu = boline[2];
    
            if (esim == '') {
                row.children().eq(3).text('Không có SIM!');
                return false;
            }

            let cell = row.children().eq(4);
            cell.text('Bắt đầu đổi sim ...');

            try {
                let result = await $.ajax({
                    type: 'POST',
                    url: "{{ route('doi-sim.post') }}",
                    data: {
                        'sdt': sdt,
                        'esim': esim,
                        'ghichu': ghichu,
                    },
                });

                if (result.includes("|vl")) {
                    cell.text(thongbaos[result.replace("|vl", "")] ?? "Lỗi khi đổi SIM cho thuê bao #404");
                } else {
                    cell.text(result);
                }

                if (result != "1|vl" && result != "2|vl") return false;

                return true;
            } catch (error) {
                cell.text('Lỗi ngoại biên!');
                return false;
            }
        }

        async function layqr(row, boline) {
            let sdt = boline[0];

            let cell = row.children().eq(5);
            cell.text('Lấy QR Esim ...');

            try {
                let result1 = await $.ajax({
                    type: 'POST',
                    url: "{{ route('lay-ma-sim.post') }}",
                    data: {'sdt': sdt},
                });

                cell.text(result1);

                let tach = result1.split('|');

                if (tach.length <= 1) return false;

                if (tach[0] == "" || tach[1] == "") {
                    cell.text("Thiếu mã QR hoặc BarCode");
                    return false;
                }

                cell.text("Tải ảnh QR ...");

                let result2 = await $.ajax({
                    type: 'POST',
                    url: "{{ route('tai-anh.post') }}",
                    data: {
                        'ma': tach[0],
                        'bar': tach[1],
                        'sdt': sdt,
                    },
                });

                if (!result2) {
                    cell.text("Tải ảnh thất bại!");
                    return false;
                }

                row.children().eq(6).html(`
                    <a href="${result2}" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                    <a href="${result2}" target="_blank" class="btn btn-outline-success btn-sm" download>
                        <i class="fa-solid fa-cloud-arrow-down"></i>
                    </a>
                `);

                cell.text(result1);

                return true;
            } catch (error) {
                cell.text('Lỗi ngoại biên!');
                return false;
            }
        }

        function stop() {
            $('.btn-run').prop('disabled', false);
            $('#tb_footer').addClass('d-none');
            $('#tb_footer').html('‎');
        }
    });
</script>
@endpush