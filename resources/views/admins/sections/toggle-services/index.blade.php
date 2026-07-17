@extends('admins.layouts.master')

@section('content')
<div class="row flex-lg-nowrap">
    <div class="col-lg-3 col-12 mb-2">
        <div class="card">
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col">
                        <strong>Chọn dịch vụ</strong>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col">
                        @php($services = ['IR', 'SMO', 'DIR', 'SMT', 'UMB', 'USSD', 'CF', 'NR', 'GPRS', 'CLIR', 'CLIP', 'CBR', 
                                            'DATA', 'CW', 'CH', 'FAX', 'MCA', 'CSM', 'VLTE', 'MSIM', 'VOWI'])
                        <select name="services" class="form-control select2" multiple>
                            @foreach ($services as $service)
                            <option value="{{ $service }}">{{ $service }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col text-right">
                        <button class="btn btn-outline-primary btn-select-all-services text-nowrap">
                            CHỌN TẤT CẢ
                        </button>
                        <button class="btn btn-outline-danger btn-services-clear text-nowrap">
                            <i class="fa-solid fa-trash mr-1"></i>XÓA
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
    </div>
    <div class="col-lg-auto col-12 mb-2">
        <div class="card">
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col">
                        <strong>Đóng mở dịch vụ</strong>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-auto">
                        <button class="btn btn-outline-primary btn-block btn-run-action text-nowrap">
                            <i class="fa-solid fa-play mr-1"></i>THỰC HIỆN
                        </button>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-outline-secondary btn-block btn-stop-action text-nowrap" disabled>
                            <i class="fa-solid fa-pause mr-1"></i>DỪNG
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col">
        <div class="table-responsive">
            <table class="table table-bordered table-xs bg-white result-table">
                <thead class="text-nowrap">
                    <tr>
                        <th class="text-center" style="width:0">STT</th>
                        <th class="text-center sdt">Số thuê bao</th>
                        <th class="text-center imei">Số IMEI</th>
                        <th class="text-center tttb">Thông tin TB</th>
                        <th class="text-center note">Trạng thái</th>
                        <th class="text-center" style="width:0">Xóa</th>
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
    let services = [];
    let index = 0;
    let total = 0;
    let phones = [];

    $(document).ready(function() {
        const saved_selected_services = localStorage.getItem('selectedServices');

        $('[name="services"]').select2({
            closeOnSelect: false,
        }).on('change', function(e) {
            localStorage.setItem('selectedServices', JSON.stringify($(this).val()));
        });

        if (saved_selected_services) $('[name="services"]').val(JSON.parse(saved_selected_services)).trigger('change');

        $('.btn-select-all-services').on('click', function () { 
            $('[name="services"] > option').prop("selected", "selected");
            $('[name="services"]').trigger("change");
        });

        $('.btn-services-clear').on('click', function () { 
            $('[name="services"]').val(null).trigger('change'); 
        });

        $(document).on('click', '.btn-run', function() {
            let list = $('textarea[name="list"]').val();
            services = $('[name="services"]').val();

            if (cookies == '') {
                noty('Không có Cookie, đăng nhập lại để tiếp tục!', 'error');
                return;
            }

            if (list == '') {
                noty('Vui lòng nhập dữ liệu!', 'error');
                return;
            }

            if (services.length <= 0) {
                noty('Vui lòng chọn dịch vụ!', 'error');
                return;
            }

            lines = list.split("\n").filter((line) => {
                return line != "";
            }).map((line) => {
                return line.trim();
            });

            index = 0;
            total = lines.length;
            phones = [];

            $('.result-table thead th.service-header').remove();
            services.forEach(service => {
                $('.result-table thead th.note').before(`
                <th class="text-center service-header" style="width:0">
                    <label class="m-0 cursor-pointer" for="check_all_${service}">${service}</label> <br>
                    <div class="custom-control custom-control-right custom-checkbox custom-control-inline">
                        <input type="checkbox" class="custom-control-input check-all" data-dvu="${service}" id="check_all_${service}">
                        <label class="custom-control-label p-0" for="check_all_${service}">‎</label>
                    </div>
                </th>`);
            });

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

            row.append(`
                <td class="text-center">${index}</td>
                <td class="sdt"></td>
                <td class="imei"></td>
                <td class="tttb"></td>
                <td class="note"></td>
                <td class="text-center">
                    <span type="button" class="badge badge-danger btn-remove-row">
                        <i class="fa-solid fa-trash-can"></i>
                    </span>
                </td>
            `);

            services.forEach(service => {
                row.find('.note').before(`<td class="text-center ${service}"></td>`);
            });

            $('#progress_list').append(row);

            $('#tb_footer')[0].scrollIntoView({
                behavior: 'smooth',
                block: 'end'
            });

            if ([9,11].includes(line.length) || mobile_headers.some(mobile_header => line.startsWith(mobile_header))) {
                let sdt = line.length == 11 ? line.slice(-9) : line;
                row.find('.sdt').text(sdt);
                await kiemTraTB(row, sdt) && await layDVu(row, sdt);
            } else if ([10,20].includes(line.length)) {
                let imei = line.length == 20 ? line.slice(9,19) : line;
                row.find('.imei').text(imei);
                let sdt = await kiemTraIMEI(row, imei);
                sdt && await layDVu(row, sdt);
            } else {
                row.find('.sdt').text("Dữ liệu không hợp lệ!");
            }

            if (index < total) timeout = setTimeout(chay, delay * 1000);
            else stop();
        }

        async function kiemTraIMEI(row, imei) {
            let sdt = row.find('.sdt');
            let tttb = row.find('.tttb');
            let note = row.find('.note');

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
            let imei = row.find('.imei');
            let tttb = row.find('.tttb');

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
                    imei.text(lay_imei);
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

        async function layDVu(row, sdt) {
            let note = row.find('.note');

            if (phones.includes(sdt)) {
                note.text('Trùng số thuê bao!');
                return;
            }

            phones.push(sdt);

            try {
                note.text('Đang lấy dịch vụ ...');

                let lay_dvu = await $.ajax({
                    type: 'POST',
                    url: "{{ route('lay-dvu.post') }}",
                    data: {
                        'sdt': '84'+sdt,
                        'dich_vu': services,
                    },
                });

                let tach = lay_dvu.split("|");
                if (tach.length < 2) {
                    note.text(tach[0]);
                    return;
                }

                for (let i = 0; i < services.length; i++) {
                    let dvu = services[i];
                    let checked = tach[i + 1];

                    let custom_checkbox = $(`
                        <div class="custom-control custom-checkbox custom-control-inline m-0">
                            <input type="checkbox" class="custom-control-input" data-dvu="${dvu}" data-sdt="${sdt}" id="${dvu}_${sdt}">
                            <label class="custom-control-label pl-3" for="${dvu}_${sdt}">‎</label>
                        </div>
                    `);

                    if (checked < 0) {
                        custom_checkbox.find('input[type="checkbox"]').prop('disabled', true);
                    } else {
                        custom_checkbox.find('input[type="checkbox"]').prop('checked', !!(checked * 1));
                        custom_checkbox.find('input[type="checkbox"]').attr('data-checked', checked);
                    }

                    row.find('td.'+dvu).html(custom_checkbox);
                    note.text('');
                }
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
            let note = row.find('.note');
            let checkboxes = row.find('input[data-dvu]');

            note.text('Đang thực hiện ...');

            row[0].scrollIntoView({
                behavior: 'smooth',
                block: 'end'
            });

            for (let checkbox of checkboxes) {
                checkbox = $(checkbox);
                let sdt = checkbox.attr('data-sdt');
                let dvu = checkbox.attr('data-dvu');
                let valid = checkbox.length > 0 && !checkbox.is(':disabled') && +checkbox.is(':checked') != checkbox.attr('data-checked');

                if (valid) {
                    checkbox.next('label').html('‎');
                    await dongMoDVu(row, sdt, dvu);
                }
            }

            note.text('Hoàn tất');

            if (dvu_index >= dvu_total) stop();
            else timeout = setTimeout(thucHien, delay * 1000);
        }

        async function dongMoDVu(row, sdt, dvu) {
            let checkbox = row.find(`input[data-dvu="${dvu}"]`);
            let checked = checkbox.is(':checked');

            try {
                let dm_dvu = await $.ajax({
                    type: 'POST',
                    url: "{{ route('dm-dvu.post') }}",
                    data: {
                        'sdt': '84'+sdt,
                        'dvu': dvu,
                    },
                });

                if (dm_dvu == 'THÀNH CÔNG') checkbox.attr('data-checked', +checked);

                let icon_class = dm_dvu == 'THÀNH CÔNG' ? 'fa-circle-check text-success' : 'fa-circle-exclamation text-danger';
                let icon = $(`<span><i class="fa-solid ${icon_class} fa-lg ml-1"></i></span>`);
                
                let label = checkbox.next('label');
                label.attr('data-popup', 'tooltip').attr('data-original-title', dm_dvu).tooltip();

                label.html(icon);

            } catch (error) {
                note.text('Đã xảy ra lỗi!');
            }
        }
    });
</script>
@endpush