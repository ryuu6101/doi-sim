@extends('admins.layouts.master')

@section('content')
<div class="row flex-lg-nowrap align-items-start">

    <div class="col-lg-3 col-12 mb-2">
        <div class="card">
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col">
                        <strong>Chức năng:</strong>
                    </div>
                </div>
                <div class="row mb-2" id="chucNang">
                    <div class="col-auto mb-2">
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input type="checkbox" class="custom-control-input" name="doi_sim" id="doi_sim">
                            <label class="custom-control-label" for="doi_sim">Đổi SIM</label>
                        </div>
                    </div>
                    <div class="col-auto mb-2">
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input type="checkbox" class="custom-control-input" name="lay_qr" id="lay_qr">
                            <label class="custom-control-label" for="lay_qr">Lấy mã QR ESIM</label>
                        </div>
                    </div>
                    <div class="col-auto mb-2">
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input type="checkbox" class="custom-control-input" name="gui_sms" id="gui_sms">
                            <label class="custom-control-label" for="gui_sms">Gửi SMS</label>
                        </div>
                    </div>
                    <div class="col-auto mb-2">
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input type="checkbox" class="custom-control-input" name="cho_phep_dao_lai" id="cho_phep_dao_lai">
                            <label class="custom-control-label" for="cho_phep_dao_lai">Cho phép đảo lại</label>
                        </div>
                    </div>
                    <div class="col-auto mb-2">
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input type="checkbox" class="custom-control-input" name="kich_hoat_gprs" id="kich_hoat_gprs">
                            <label class="custom-control-label" for="kich_hoat_gprs">Kích hoạt GPRS</label>
                        </div>
                    </div>
                    <div class="col-auto mb-2">
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input type="checkbox" class="custom-control-input" name="check_user" id="check_user">
                            <label class="custom-control-label" for="check_user">Check user</label>
                        </div>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col">
                        <input type="text" name="ghichu" class="form-control border-dark" placeholder="Nhập ghi chú">
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col">
                        <input type="text" name="hotline" class="form-control border-dark" placeholder="Nhập hotline">
                    </div>
                </div>

                <hr>

                <div class="row mb-2">
                    <div class="col-4">
                        <button class="btn btn-outline-success btn-block btn-run text-nowrap">
                            <i class="fa-solid fa-play mr-1"></i>CHẠY
                        </button>
                    </div>
                    <div class="col-4">
                        <button class="btn btn-outline-secondary btn-block btn-stop text-nowrap" disabled>
                            <i class="fa-solid fa-pause mr-1"></i>DỪNG
                        </button>
                    </div>
                    <div class="col-4">
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

    <div class="col table-responsive">
        <table class="table table-bordered table-xs bg-white">
            <thead class="text-nowrap">
                <tr>
                    <th class="text-center">STT</th>
                    <th class="text-center">Số TB</th>
                    <th class="text-center">SIM</th>
                    <th class="text-center">Ghi chú</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="text-center">GPRS</th>
                    <th class="text-center">SMS</th>
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

    let doi_sim = false;
    let lay_qr = false;
    let send_sms = false;
    let ignore_overlap = false;
    let toggle_gprs = false;
    let check_user = false;
    let delay = {{ $delay ?? 1 }};
    let timeout;
    let lines = [];
    let index = 0;
    let total = 0;

    $(document).ready(function() {
        const saved_checkboxes = localStorage.getItem('checkboxDoiSim');
        const saved_hotline = localStorage.getItem('hotline');

        if (saved_checkboxes) {
            const states = JSON.parse(saved_checkboxes);
            $.each(states, function(id, checked) {
                $('#' + id).prop('checked', checked);
            });
        } else {
            $('#chucNang input[type="checkbox"]').prop('checked', true);
        }

        if (saved_hotline) $('input[name="hotline"]').val(saved_hotline);

        $('#chucNang input[type="checkbox"]').on('change', function() {
            const states = {};

            $('#chucNang input[type="checkbox"]').each(function() {
                states[$(this).attr('id')] = $(this).is(':checked');
            });

            localStorage.setItem('checkboxDoiSim', JSON.stringify(states));
        });

        $('input[name="hotline"]').on('input', function(e) {
            localStorage.setItem('hotline', $(this).val());
        });
    });

    $(document).ready(function() {
        $(document).on('click', '.btn-run', function() {
            let list = $('textarea[name="list"]').val();

            doi_sim = $('input[name="doi_sim"]').is(":checked");
            lay_qr = $('input[name="lay_qr"]').is(":checked");
            send_sms = $('input[name="gui_sms"]').is(":checked");
            ignore_overlap = $('input[name="cho_phep_dao_lai"]').is(":checked");
            toggle_gprs = $('input[name="kich_hoat_gprs"]').is(":checked");
            check_user = $('input[name="check_user"]').is(":checked");

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

        function xulyChuoi(string) {
            let tach = string.match(/\d{9,20}/g);
            let boline = [];
            let slice_pos = 0;

            boline = tach.map((value, index) => (value.length == 20) ? value.slice(9, 19) : value);

            let last_string = boline[1] ?? boline[0] ?? '';
            slice_pos = string.indexOf(last_string) + last_string.length;
            let ghichu = string.slice(slice_pos).trim();
            if (ghichu != '') boline[2] = ghichu;

            return boline;
        }

        async function chay() {
            let boline = xulyChuoi(lines[index++]);
            let row = $('<tr></tr>');

            row.append($('<td class="text-center">' + (index) + '</td>'));
            row.append($('<td>' + (boline[0] ?? '') + '</td>'));
            row.append($('<td>' + (boline[1] ?? '') + '</td>'));
            row.append($('<td>' + (boline[2] ?? $('input[name="ghichu"]').val() ?? '') + '</td>'));
            row.append($('<td></td>'));
            row.append($('<td></td>'));
            row.append($('<td></td>'));
            row.append($('<td></td>'));
            row.append($('<td class="text-nowrap"></td>'));

            $('#progress_list').append(row);

            $('#tb_footer')[0].scrollIntoView({
                behavior: 'smooth',
                block: 'end'
            });

            let ds_thanh_cong = doi_sim && await doisim(row, boline);

            if (ds_thanh_cong) {
                toggle_gprs ? await kh_gprs(row, boline) : await kt_gprs(row, boline);
                send_sms && await gui_sms(row, boline);
            }

            if (lay_qr && (ds_thanh_cong || !doi_sim)) await layqr(row, boline);

            if (index < total) timeout = setTimeout(chay, delay * 1000);
            else stop();
        }

        async function doisim(row, boline) {
            let sdt = boline[0] ?? '';
            let esim = boline[1] ?? '';
            let ghichu = boline[2] ?? $('input[name="ghichu"]').val() ?? '';

            let sim = row.children().eq(3);
            let status = row.children().eq(4);
    
            if (esim == '') {
                sim.text('Không có SIM!');
                return false;
            }

            try {
                if (!ignore_overlap) {
                    let ktra_trung_tb = await $.ajax({
                        type: 'POST',
                        url: "{{ route('ktra-trung-tb.post') }}",
                        data: {'sdt': '84'+sdt},
                    });
    
                    if (ktra_trung_tb) {
                        status.text('Thuê bao đã được đảo sim trong ngày!');
                        return false;
                    }
                }

                if (check_user) {
                    let lay_ls_tb = await $.ajax({
                        type: 'POST',
                        url: "{{ route('lay-lsu-tbao.post') }}",
                        data: {'sdt': '84'+sdt},
                    });

                    let ten_user = lay_ls_tb[0]?.[4] ?? '';

                    if (ten_user != 'cuongpp_dng') {
                        status.text('User không hợp lệ!');
                        return false;
                    }
                }

                status.text('Bắt đầu đổi sim ...');

                let doi_sim = await $.ajax({
                    type: 'POST',
                    url: "{{ route('doi-sim.post') }}",
                    data: {
                        'sdt': sdt,
                        'esim': esim,
                        'ghichu': ghichu,
                    },
                });

                status.text(doi_sim['message']);

                return doi_sim['success'];
            } catch (error) {
                status.text('Lỗi ngoại biên!');
                return false;
            }
        }

        async function kt_gprs(row, boline) {
            let sdt = boline[0];

            let gprs = row.children().eq(5);

            gprs.text('Kiểm tra trạng thái ...');

            try {
                let kt_gprs = await $.ajax({
                    type: 'POST',
                    url: "{{ route('lay-dvu.post') }}",
                    data: {
                        'sdt': '84'+sdt,
                        'dich_vu': 'GPRS',
                    },
                });

                let tach = kt_gprs.split("|");
                if (tach.length < 2) {
                    gprs.text(tach[0]);
                    return;
                }

                gprs.text(tach[1] == 0 ? "ĐANG ĐÓNG" : "ĐANG MỞ");
            } catch (error) {
                gprs.text('Lỗi ngoại biên!');
            }
        }

        async function kh_gprs(row, boline) {
            let sdt = boline[0];

            let gprs = row.children().eq(5);

            gprs.text('Đang bật GPRS ...');

            try {
                let kh_gprs = await $.ajax({
                    type: 'POST',
                    url: "{{ route('kich-hoat-gprs.post') }}",
                    data: {'sdt': '84'+sdt},
                });

                gprs.text(kh_gprs);
            } catch (error) {
                gprs.text('Lỗi ngoại biên!');
            }
        }

        async function gui_sms(row, boline) {
            let sdt = boline[0];
            let hotline = $('input[name="hotline"]').val() ?? '-';

            let sms = row.children().eq(6);

            sms.text('Đang gửi SMS ...');

            try {
                let gui_sms = await $.ajax({
                    type: 'POST',
                    url: "{{ route('send-welcome-sms.post') }}",
                    data: {
                        'sdt': '84'+sdt,
                        'hotline': hotline,
                    },
                });

                sms.text(gui_sms);
            } catch (error) {
                sms.text('Lỗi ngoại biên!');
            }
        }

        async function layqr(row, boline) {
            let sdt = boline[0];

            let link_qr = row.children().eq(7);
            let qr_code = row.children().eq(8);

            link_qr.text('Lấy QR Esim ...');

            try {
                let lay_ma_sim = await $.ajax({
                    type: 'POST',
                    url: "{{ route('lay-ma-sim.post') }}",
                    data: {'sdt': sdt},
                });

                link_qr.text(lay_ma_sim);

                let tach = lay_ma_sim.split('|');

                if (tach.length <= 1) return false;

                if (tach[0] == "" || tach[1] == "") {
                    link_qr.text("Thiếu mã QR hoặc BarCode");
                    return false;
                }

                link_qr.text("Tải ảnh QR ...");

                let tai_anh = await $.ajax({
                    type: 'POST',
                    url: "{{ route('tai-anh.post') }}",
                    data: {
                        'ma': tach[0],
                        'bar': tach[1],
                        'sdt': sdt,
                    },
                });

                if (!tai_anh) {
                    link_qr.text("Tải ảnh thất bại!");
                    return false;
                }

                qr_code.html(`
                    <a href="${tai_anh}" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                    <a href="${tai_anh}" target="_blank" class="btn btn-outline-success btn-sm" download>
                        <i class="fa-solid fa-cloud-arrow-down"></i>
                    </a>
                `);

                link_qr.text(lay_ma_sim);

                return true;
            } catch (error) {
                link_qr.text('Lỗi ngoại biên!');
                return false;
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