@extends('admins.layouts.master')

@section('content')
<div class="row flex-lg-nowrap">
    <div class="col-lg-auto col-12 mb-2">
        <div class="card">
            <div class="card-body">
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
                        <textarea name="list" cols="30" rows="5" class="form-control border-dark rounded-0 bg-light w-100"></textarea>
                    </div>
                </div>

            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col">
                        <strong>Cắt mở IOC</strong>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-6">
                        <button class="btn btn-outline-primary btn-block btn-run-action text-nowrap">
                            <i class="fa-solid fa-play mr-1"></i>THỰC HIỆN
                        </button>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-outline-secondary btn-block btn-stop-action text-nowrap" disabled>
                            <i class="fa-solid fa-pause mr-1"></i>DỪNG
                        </button>
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
                        <th class="text-center" style="width:5rem">STT</th>
                        <th class="text-center">Số thuê bao</th>
                        <th class="text-center">Số IMEI</th>
                        <th class="text-center">Thông tin TB</th>
                        <th class="text-center">
                            <div class="custom-control custom-control-right custom-checkbox custom-control-inline">
                                <input type="checkbox" class="custom-control-input check-all" data-dvu="goidi" id="check_all_goidi">
                                <label class="custom-control-label" for="check_all_goidi">Gọi đi</label>
                            </div>
                        </th>
                        <th class="text-center">
                            <div class="custom-control custom-control-right custom-checkbox custom-control-inline">
                                <input type="checkbox" class="custom-control-input check-all" data-dvu="goiden" id="check_all_goiden">
                                <label class="custom-control-label" for="check_all_goiden">Gọi đến</label>
                            </div>
                        </th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-center" style="width:5rem">Xóa</th>
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
    let delay = {{ $delay ?? 1 }};
    let timeout;
    let lines = [];
    let index = 0;
    let total = 0;
    let phones = [];

    $(document).ready(function() {
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

            lines = list.split("\n").filter((line) => {
                return line != "";
            }).map((line) => {
                return line.trim();
            })

            index = 0;
            total = lines.length;
            phones = [];

            $('#progress_list').html('');
            $('#tb_footer').removeClass('d-none');
            $('#tb_footer').html('<span class="spinner spinner-border spinner-border-sm mr-1"></span>Vui lòng không đóng hoặc tải lại trang');
            $('.btn-run').prop('disabled', true);
            $('.btn-stop').prop('disabled', false);
            $('.btn-run-action').prop('disabled', true);

            chay();
        });

        $(document).on('click', '.btn-stop', function() {
            clearTimeout(timeout);
            stop();
        });

        $(document).on('click', '.btn-reset', function() {
            $('textarea[name="list"]').val("");
        });

        $(document).on('click', '.btn-remove-row', function() {
            let row = $(this).closest('tr');
            row.remove();

            if ($('#progress_list tr').length > 0) return;

            $('#tb_footer').removeClass('d-none');
            $('#tb_footer').text('(Chưa có dữ liệu)');
        });

        async function chay() {
            let line = lines[index++];
            let row = $('<tr></tr>');

            row.append($('<td class="text-center">' + (index) + '</td>'));
            row.append($('<td></td>'));
            row.append($('<td></td>'));
            row.append($('<td></td>'));
            row.append($('<td class="text-center"></td>'));
            row.append($('<td class="text-center"></td>'));
            row.append($('<td></td>'));
            row.append($('<td class="text-center"></td>'));

            $('#progress_list').append(row);

            $('#tb_footer')[0].scrollIntoView({
                behavior: 'smooth',
                block: 'end'
            });

            if ([9,11].includes(line.length) || mobile_headers.some(mobile_header => line.startsWith(mobile_header))) {
                let sdt = line.length == 11 ? line.slice(-9) : line;
                row.children().eq(1).text(sdt);
                await kiemTraTB(row, sdt) && await layIOC(row, sdt);
            } else if ([10,20].includes(line.length)) {
                let imei = line.length == 20 ? line.slice(9,19) : line;
                row.children().eq(2).text(imei);
                let sdt = await kiemTraIMEI(row, imei);
                sdt && await layIOC(row, sdt);
            } else {
                row.children().eq(1).text("Dữ liệu không hợp lệ!");
            }

            row.children(':last-child').html(`
                <span type="button" class="badge badge-danger btn-remove-row">
                    <i class="fa-solid fa-trash-can"></i>
                </span>
            `);

            if (index < total) timeout = setTimeout(chay, delay * 1000);
            else stop();
        }

        async function kiemTraIMEI(row, imei) {
            let sdt = row.children().eq(1);
            let tttb = row.children().eq(3);
            let note = row.children().eq(6)

            try {
                sdt.text('Đang lấy số TB ...');

                let lay_tb = await $.ajax({
                    type: 'POST',
                    url: "{{ route('check-msin.post') }}",
                    data: {'msin': imei},
                });

                let tach = lay_tb.split("|");

                if (tach.length < 2) {
                    sdt.text('');
                    note.text(tach[0]);
                    return false;
                }

                mobile = tach[1].slice(2);

                sdt.text(mobile);

                tttb.text('Đang lấy thông tin ...');

                let lay_matinh = await $.ajax({
                    type: 'POST',
                    url: "{{ route('lay-tttb-v4.post') }}",
                    data: {
                        'sdt': '84'+mobile,
                        'string_data': 'ma_tinh',
                    },
                });

                tach = lay_matinh.split("|");

                if (tach.length < 2) {
                    tttb.text("Vui lòng đăng nhập lại!");
                    return false;
                }

                let matinh = tach[1];

                let lay_tttb = await $.ajax({
                    type: 'POST',
                    url: "{{ route('lay-tttb.post') }}",
                    data: {
                        'sdt': '84'+mobile,
                        'matinh': matinh,
                    },
                });

                tttb.text(lay_tttb);

                if (lay_tttb == 'Vui lòng đăng nhập lại!') return false;

                return mobile;

            } catch (error) {
                note.text('Lỗi ngoại biên!');
                return false;
            }
        }

        async function kiemTraTB(row, sdt) {
            let imei = row.children().eq(2);
            let tttb = row.children().eq(3);

            try {
                imei.text('Đang lấy IMEI ...');

                let lay_imei = await $.ajax({
                    type: 'POST',
                    url: "{{ route('lay-tttb-v4.post') }}",
                    data: {
                        'sdt': '84'+sdt,
                        'string_data': ['so_msin', 'ma_tinh'],
                    },
                });

                let tach = lay_imei.split("|");
                
                if (tach.length < 2) {
                    imei.text('Vui lòng đăng nhập lại!');
                    return false;
                }

                imei.text(tach[1]);

                let matinh = tach[2];

                tttb.text('Đang lấy thông tin ...');

                let lay_tttb = await $.ajax({
                    type: 'POST',
                    url: "{{ route('lay-tttb.post') }}",
                    data: {
                        'sdt': '84'+sdt,
                        'matinh': matinh,
                    },
                });

                tttb.text(lay_tttb);

                return lay_tttb != 'Vui lòng đăng nhập lại!';
            } catch (error) {
                imei.text('Lỗi ngoại biên!');
                return false;
            }
        }

        async function layIOC(row, sdt) {
            let goidi = row.children().eq(4);
            let goiden = row.children().eq(5);
            let note = row.children().eq(6);

            try {
                goidi.html('<span class="spinner spinner-border spinner-border-sm text-muted"></span>');

                let lay_ioc = await $.ajax({
                    type: 'POST',
                    url: "{{ route('lay-ioc.post') }}",
                    data: {'sdt': '84'+sdt},
                });

                let tach = lay_ioc.split("|");
                if (tach.length < 2) {
                    note.text(tach[0]);
                    return;
                }

                let checkboxs = [];
                let iocs = ['goidi', 'goiden'];

                tach.forEach((value, index) => {
                    let custom = $('<div class="custom-control custom-checkbox custom-control-inline"></div>');
                    let checkbox = $(`<input type="checkbox" class="custom-control-input" 
                                        data-dvu="${iocs[index]}" data-sdt="${sdt}" id="${iocs[index]}_${sdt}">`);
                    let label = $(`<label class="custom-control-label pl-0" for="${iocs[index]}_${sdt}"></label>`);

                    if (value < 0) checkbox.prop('disabled', true);
                    else checkbox.prop('checked', !!(value * 1));

                    custom.append(checkbox);
                    custom.append(label);

                    checkboxs.push(custom);
                });

                goidi.html(checkboxs[0]);
                goiden.html(checkboxs[1]);
            } catch (error) {
                note.text('Lỗi ngoại biên!');
            }
        }

        function stop() {
            $('.btn-run').prop('disabled', false);
            $('.btn-stop').prop('disabled', true);
            $('.btn-run-action').prop('disabled', false);
            $('.btn-stop-action').prop('disabled', true);
            $('#tb_footer').addClass('d-none');
            $('#tb_footer').html('‎');
        }

        $(document).on('click', '.check-all', function() {
            let dvu = $(this).attr('data-dvu');
            let checked = $(this).is(':checked');
            $(`#progress_list input[data-dvu="${dvu}"]:not(:disabled)`).prop('checked', checked);
        });

        let dvu_rows = [];
        let dvu_index = 0;
        let dvu_total = 0;

        $(document).on('click', '.btn-run-action', function() {
            dvu_rows = $('#progress_list tr');

            if (cookies == '') {
                noty('Không có Cookie, đăng nhập lại để tiếp tục!', 'error');
                return;
            }

            if (dvu_rows.length <= 0) {
                noty('Không có dữ liệu!', 'error');
                return;
            }

            dvu_index = 0;
            dvu_total = dvu_rows.length;

            $('#tb_footer').removeClass('d-none');
            $('#tb_footer').html('<span class="spinner spinner-border spinner-border-sm mr-1"></span>Vui lòng không đóng hoặc tải lại trang');
            $('.btn-run-action').prop('disabled', true);
            $('.btn-stop-action').prop('disabled', false);
            $('.btn-run').prop('disabled', true);

            thucHien();
        });

        async function thucHien() {
            let row = dvu_rows.eq(dvu_index++);
            let sdt = row.children().eq(1).text();
            let goidi = +row.find('input[data-dvu="goidi"]').is(':checked');
            let goiden = +row.find('input[data-dvu="goiden"]').is(':checked');

            await catmoIOC(row, sdt, goidi, goiden);

            if (dvu_index >= dvu_total) stop();
            else timeout = setTimeout(thucHien, delay * 1000);
        }

        async function catmoIOC(row, sdt, goidi, goiden) {
            let note = row.children().eq(6);

            note.text('Đang thực hiện ...');

            try {
                let catmo_ioc = await $.ajax({
                    type: 'POST',
                    url: "{{ route('catmo-ioc.post') }}",
                    data: {
                        'sdt': '84'+sdt,
                        'goidi': goidi,
                        'goiden': goiden,
                    },
                });

                note.text(catmo_ioc);
            } catch (error) {
                note.text('Đã xảy ra lỗi!');
            }
        }
    });
</script>
@endpush