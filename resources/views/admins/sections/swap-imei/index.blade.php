@extends('admins.layouts.master')

@section('content')
<div class="row align-items-start justify-content-start">

    <div class="col-8 mb-2">
        <div class="card">
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col">
                        <input type="text" name="ghichu" class="form-control border-dark" placeholder="Nhập ghi chú">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-outline-success btn-block btn-run text-nowrap">
                            <i class="fa-solid fa-play mr-1"></i>CHẠY
                        </button>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-outline-secondary btn-block btn-stop text-nowrap" disabled>
                            <i class="fa-solid fa-pause mr-1"></i>DỪNG
                        </button>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-outline-danger btn-block btn-reset text-nowrap">
                            <i class="fa-solid fa-trash mr-1"></i>XÓA
                        </button>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col">
                        <textarea name="list" rows="5" class="form-control border-dark rounded-0 bg-light w-100"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 table-responsive">
        <table class="table table-bordered table-xs bg-white">
            <thead class="text-nowrap">
                <tr>
                    <th class="text-center" style="width:5rem">STT</th>
                    <th class="text-center">Số TB</th>
                    <th class="text-center">IMEI cũ</th>
                    <th class="text-center">SIM</th>
                    <th class="text-center">Ghi chú</th>
                    <th class="text-center">Kết quả</th>
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
@endsection

@push('scripts')
<script>
    let thongbaos = {
        "1" : "Đã đặt lệnh đổi SIM cho số thuê bao", 
        "2" : "Đã đặt lệnh đổi SIM cho số thuê bao (có tạo AC cho SIM mới)", 
        "-1000" : "Lỗi khi đổi SIM cho thuê bao  (do khác tỉnh quản lý!!!)", 
        "-1002" : "Lỗi khi đổi SIM thuê bao  TB Blacklist!", 
        "-3010" : "Thuê bao không có trên hệ thống IN-Eric", 
        "4006" : " Thuê bao không có trên hệ thống IN-Comv ", 
    };

    let delay = {{ $delay ?? 1 }};
    let timeout;
    let lines = [];
    let index = 0;
    let total = 0;

    $(document).ready(function() {
        // $('.sidebar.sidebar-main').addClass("sidebar-main-resized");

        $(document).on('click', '.btn-run', function() {
            let list = $('textarea[name="list"]').val();

            if (cookies == '') {
                noty('Không có Cookie, đăng nhập lại để tiếp tục!', 'error');
                return;
            }

            if (list == '') {
                noty('Vui lòng nhập dữ liệu!', 'error');
                return;
            }

            lines = list.split("\n").filter((line) => {return line != ""});
            index = 0;
            total = lines.length;

            $('#progress_list').html('');
            $('#tb_footer').removeClass('d-none');
            $('#tb_footer').html('<span class="spinner spinner-border spinner-border-sm mr-1"></span>Vui lòng không đóng hoặc tải lại trang');
            $('.btn-run').prop('disabled', true);
            $('.btn-stop').prop('disabled', false);

            chay();
        });

        $(document).on('click', '.btn-stop', function() {
            clearTimeout(timeout);
            stop();
        });

        $(document).on('click', '.btn-reset', function() {
            $('textarea[name="list"]').val("");
        });

        async function chay() {
            let boline = xulyChuoi(lines[index++]);
            let row = $('<tr></tr>');

            row.append($('<td class="text-center">' + (index) + '</td>'));
            row.append($('<td>' + (boline[0] ?? '') + '</td>'));
            row.append($('<td>' + (boline[1] ?? '') + '</td>'));
            row.append($('<td>' + (boline[2] ?? '') + '</td>'));
            row.append($('<td>' + (boline[3] ?? $('input[name="ghichu"]').val() ?? '') + '</td>'));
            row.append($('<td></td>'));

            $('#progress_list').append(row);

            await doisim(row, boline);

            if (index < total) timeout = setTimeout(chay, delay * 1000);
            else stop();
        }

        function xulyChuoi(string) {
            let tach = string.match(/\d{7,20}/g);
            let boline = [];
            let imei_index = 1;

            tach.forEach(value => {
                if (value.length == 7) boline[0] = '84'+value;
                else if (value.length == 9) boline[0] = value;
                else if (value.length == 10) boline[imei_index++] = value;
                else if (value.length == 20) boline[imei_index++] = value.slice(9, 19);
            });

            let regex = new RegExp(tach.join("|"), "gi");
            let ghichu = string.replace(regex, '').trim();

            if (ghichu != '' && !ghichu.includes("母卡")) boline[3] = ghichu;

            return boline;
        }

        async function doisim(row, boline) {
            let sdt = boline[0] ?? '';
            let old_esim = boline[1] ?? '';
            let new_esim = boline[2] ?? '';
            let ghichu = boline[3] ?? $('input[name="ghichu"]').val() ?? '';

            let note = row.children().eq(4);
            let kqua = row.children().eq(5);

            if (sdt == '') {
                note.text('Không có SĐT!');
                return;
            }

            if (old_esim == '') {
                note.text('Không có IMEI!');
                return;
            }

            if (new_esim == '') {
                note.text('Không có SIM!');
                return;
            }

            try {
                kqua.text('Kiểm tra IMEI ...');

                let lay_imei = await $.ajax({
                    type: 'POST',
                    url: "{{ route('lay-imei.post') }}",
                    data: {'sdt': '84'+sdt},
                });

                let tach = lay_imei.split("|");

                if (tach.length < 2) {
                    kqua.text(tach[0]);
                    return;
                }

                let imei = tach[0];
                let matinh = tach[1];

                if (imei != old_esim) {
                    note.text('IMEI hiện tại không trùng khớp');
                    kqua.text(sdt + ' - ' + imei);
                    return;
                }

                kqua.text('Kiểm tra thông tin thuê bao ...');

                let lay_tttbao = await $.ajax({
                    type: 'POST',
                    url: "{{ route('lay-tttb.post') }}",
                    data: {
                        'sdt': '84'+sdt,
                        'matinh': matinh,
                    },
                });

                if (lay_tttbao == "Vui lòng đăng nhập lại!") {
                    kqua.text(lay_tttbao);
                    return;
                }

                if (lay_tttbao.toUpperCase() != "CÔNG TY CỔ PHẦN CÔNG NGHỆ CNPT") {
                    note.text('Thuê bao không hợp lệ');
                    kqua.text(lay_tttbao);
                    return;
                }

                kqua.text('Bắt đầu đổi sim ...');

                let doi_sim = await $.ajax({
                    type: 'POST',
                    url: "{{ route('doi-sim.post') }}",
                    data: {
                        'sdt': sdt,
                        'esim': new_esim,
                        'ghichu': ghichu,
                    },
                });

                if (doi_sim.includes("|vl")) {
                    kqua.text(thongbaos[doi_sim.replace("|vl", "")] ?? "Lỗi khi đổi SIM cho thuê bao #404");
                } else {
                    kqua.text(doi_sim);
                }

            } catch (error) {
                kqua.text('Lỗi ngoại biên!');
            }
        }

        function stop() {
            $('.btn-run').prop('disabled', false);
            $('.btn-stop').prop('disabled', true);
            $('#tb_footer').addClass('d-none');
            $('#tb_footer').html('‎');
        }
    });
</script>
@endpush