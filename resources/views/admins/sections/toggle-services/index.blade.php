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
                        <strong>Đóng mở dịch vụ</strong>
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
                        <th class="text-center">
                            <div class="custom-control custom-control-right custom-checkbox custom-control-inline">
                                <input type="checkbox" class="custom-control-input check-all" data-dvu="GPRS" id="check_all_GPRS">
                                <label class="custom-control-label" for="check_all_GPRS">GPRS</label>
                            </div>
                        </th>
                        <th class="text-center">Trạng thái</th>
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
                line = line.trim();
                return line.length < 11 ? "84"+line : line;
            }).filter((line, index, self) => {
                return self.indexOf(line) === index;
            });

            index = 0;
            total = lines.length;

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

        async function chay() {
            let line = lines[index++];
            let row = $('<tr></tr>');

            row.append($('<td class="text-center">' + (index) + '</td>'));
            row.append($('<td>' + (line ?? '') + '</td>'));
            row.append($('<td></td>'));
            row.append($('<td class="text-center"></td>'));
            row.append($('<td></td>'));

            $('#progress_list').append(row);

            await layIMEI(row, line) && await layDVu(row, line);

            if (index < total) timeout = setTimeout(chay, delay * 1000);
            else stop();
        }

        async function layIMEI(row, sdt) {
            let imei = row.children().eq(2);

            imei.text('Đang lấy IMEI ...');

            try {
                let lay_imei = await $.ajax({
                    type: 'POST',
                    url: "{{ route('lay-imei.post') }}",
                    data: {'sdt': sdt},
                });

                let tach = lay_imei.split("|");
                imei.text(tach[0]);

                return tach.length >= 2;
            } catch (error) {
                imei.text('Lỗi ngoại biên!');
                return false;
            }
        }

        async function layDVu(row, sdt) {
            let gprs = row.children().eq(3);
            let note = row.children().eq(4);

            try {
                gprs.html('<span class="spinner spinner-border spinner-border-sm text-muted"></span>');

                let lay_dvu = await $.ajax({
                    type: 'POST',
                    url: "{{ route('lay-dvu.post') }}",
                    data: {
                        'sdt': sdt,
                        'dich_vu': 'GPRS',
                    },
                });

                let tach = lay_dvu.split("|");
                if (tach.length < 2) {
                    note.tach[0];
                    return;
                }

                let custom = $('<div class="custom-control custom-checkbox custom-control-inline"></div>');
                let checkbox = $(`<input type="checkbox" class="custom-control-input" data-dvu="GPRS" data-sdt="${sdt}" id="GPRS_${sdt}">`);
                let label = $(`<label class="custom-control-label pl-0" for="GPRS_${sdt}"></label>`);

                if (tach[1] < 0) {
                    checkbox.prop('disabled', true);
                } else {
                    checkbox.prop('checked', !!(tach[1] * 1));
                    checkbox.attr('data-checked', tach[1]);
                }

                custom.append(checkbox);
                custom.append(label);

                gprs.html(custom);
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
            $(`#progress_list input[data-dvu="${dvu}"]`).prop('checked', checked);
        });

        let dvu_rows = [];
        let dvu_index = 0;
        let dvu_total = 0;

        $(document).on('click', '.btn-run-action', function() {
            dvu_rows = $('#progress_list').children('tr');

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
            let note = row.children().eq(4);
            let checkbox = row.find('input[data-dvu]');
            let sdt = checkbox.attr('data-sdt');
            let dvu = checkbox.attr('data-dvu');
            let valid = checkbox.length > 0 && !checkbox.is(':disabled') && +checkbox.is(':checked') != checkbox.attr('data-checked');

            if (checkbox.length <= 0 || checkbox.is(':disabled')) note.text('Không có dịch vụ');
            if (+checkbox.is(':checked') == checkbox.attr('data-checked')) note.text('Không thay đổi');
            if (valid) await dongMoDVu(row, sdt, dvu);

            if (dvu_index >= dvu_total) stop();
            else if (!valid) thucHien();
            else timeout = setTimeout(thucHien, delay * 1000);
        }

        async function dongMoDVu(row, sdt, dvu) {
            let note = row.children().eq(4);
            let checkbox = row.find(`input[data-dvu="${dvu}"]`);
            let checked = checkbox.is(':checked');

            note.text('Đang thực hiện ...');

            try {
                let dm_dvu = await $.ajax({
                    type: 'POST',
                    url: "{{ route('dm-dvu.post') }}",
                    data: {
                        'sdt': sdt,
                        'dvu': dvu,
                    },
                });

                if (dm_dvu == 'THÀNH CÔNG') checkbox.attr('data-checked', +checked);

                note.text(dm_dvu);
            } catch (error) {
                note.text('Đã xảy ra lỗi!');
            }
        }
    });
</script>
@endpush