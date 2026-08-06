@extends('admins.layouts.master')

@push('styles')
<style>
    table.sortable th:not(.sorttable_nosort) {
        cursor: pointer;
    }
    th.sorttable_sorted::after, 
    th.sorttable_sorted_reverse::after,
    th:not(.sorttable_sorted):not(.sorttable_sorted_reverse):not(.sorttable_nosort):after {
        font-family: "Font Awesome 7 Free";
        display: inline-block;
        margin-left: 0.5rem;
    }
    th.sorttable_sorted::after {
        content: "\f0d7";
        font-weight: 900;
    }
    th.sorttable_sorted_reverse::after {
        content: "\f0d8";
        font-weight: 900;
    }
    th:not(.sorttable_sorted):not(.sorttable_sorted_reverse):not(.sorttable_nosort):after { 
        content: "\f0dc";
        font-weight: 900;
        font-size: 11px;
    }
    #sorttable_sortfwdind, #sorttable_sortrevind { display: none; }

</style>
@endpush

@section('content')
<div class="row flex-lg-nowrap">
    <div class="col-lg-auto col-12 mb-2">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row mb-2" id="chucNang">
                    <div class="col col-form-label">
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input type="checkbox" class="custom-control-input" name="ss_dl" id="ss_dl">
                            <label class="custom-control-label" for="ss_dl">Chạy so sánh dung lượng</label>
                        </div>
                    </div>
                    <div class="col-auto">
                        <input type="text" readonly class="form-control form-control-sm text-right" id="startedAt" 
                        style="width:4rem" value="-- : --">
                    </div>
                </div>
                <div class="row">
                    <div class="col-form-label col-auto">
                        Khoảng thời gian (hour):
                    </div>
                    <div class="col">
                        <input type="number" name="interval" class="form-control">
                    </div>
                </div>
            </div>
        </div>

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

        <x-google-sheet-import />
    </div>
    <div class="col">
        <div class="table-responsive">
            <table class="table table-bordered table-xs bg-white sortable">
                <thead class="text-nowrap">
                    <tr>
                        <th class="text-center sorttable_nosort" style="width:5rem">STT</th>
                        <th class="text-center sorttable_nosort">Số thuê bao</th>
                        <th class="text-center sorttable_nosort">Tên gói</th>
                        <th class="text-center sorttable_numeric">Dung lượng tối đa</th>
                        <th class="text-center sorttable_numeric">Dung lượng sử dụng</th>
                        <th class="text-center sorttable_nosort" colspan="3">So sánh dung lượng</th>
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
<script src="{{ asset('assets/js/sorttable.js') }}"></script>

<script>
    let ss_dl = false;
    let interval = 0;
    let index_saved = 0;
    let delay = {{ $delay ?? 1 }};
    let timeout;
    let lines = [];
    let index = 0;
    let total = 0;

    $(document).ready(function() {
        const saved_checkboxes = localStorage.getItem('checkboxTraCuuMI');
        const saved_interval = localStorage.getItem('interval');

        if (saved_checkboxes) {
            const states = JSON.parse(saved_checkboxes);
            $.each(states, function(id, checked) {
                $('#' + id).prop('checked', checked);
            });
        } else {
            $('#chucNang input[type="checkbox"]').prop('checked', false);
        }

        if (saved_interval) $('input[name="interval"]').val(saved_interval);
        else $('input[name="interval"]').val(4);

        $('#chucNang input[type="checkbox"]').on('change', function() {
            const states = {};

            $('#chucNang input[type="checkbox"]').each(function() {
                states[$(this).attr('id')] = $(this).is(':checked');
            });

            localStorage.setItem('checkboxTraCuuMI', JSON.stringify(states));
        });

        $('input[name="interval"]').on('input', function(e) {
            localStorage.setItem('interval', $(this).val());
        });
    });

    $(document).ready(function() {
        $(document).on('click', '.btn-run', function() {
            let list = $('textarea[name="list"]').val();

            ss_dl = $('input[name="ss_dl"]').is(":checked");
            interval = $('input[name="interval"]').val();
            index_saved = 0;

            if (cookies == '') {
                noty('Vui lòng nhập Cookie!', 'error');
                return;
            }

            if (list == '') {
                noty('Vui lòng nhập dữ liệu!', 'error');
                return;
            }

            lines = list.split("\n").filter((line) => {return line != ""}).map((line) => {
                line = line.trim();
                if (line.length > 10) return line.slice(2);
                return line;
            });
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
            let line = lines[index++];
            let row = $('<tr data-sdt="' + (line ?? '') + '"></tr>');

            row.append($('<td class="text-center">' + (index) + '</td>'));
            row.append($('<td>' + (line ?? '') + '</td>'));
            row.append($('<td></td>'));
            row.append($('<td></td>'));
            row.append($('<td></td>'));
            row.append($('<td></td>'));
            row.append($('<td></td>'));
            row.append($('<td></td>'));

            $('#progress_list').append(row);

            $('#tb_footer')[0].scrollIntoView({
                behavior: 'smooth',
                block: 'end'
            });

            await traCuuMI(row, line);

            if (index < total) timeout = setTimeout(chay, delay * 1000);
            else if (ss_dl) startSSDL();
            else stop();
        }

        async function traCuuMI(row, sdt) {
            let name = row.children().eq(2);
            let limit = row.children().eq(3);
            let used = row.children().eq(4);
            let saved = row.children().eq(5);

            name.text('Đang tìm kiếm ...');

            try {
                let result = await $.ajax({
                    type: 'POST',
                    url: "{{ route('tra-cuu-mi.post') }}",
                    data: {'sdt': sdt},
                });

                let tach = result.split('|');

                name.text(tach[0] ?? '');
                limit.text(tach[1] ?? '');
                used.text(tach[2] ?? '');

                ss_dl && saved.text(tach[2] ?? '');
            } catch (error) {
                name.text('Lỗi ngoại biên!');
            }
        }

        function stop() {
            $('.btn-run').prop('disabled', false);
            $('.btn-stop').prop('disabled', true);
            $('#tb_footer').addClass('d-none');
            $('#tb_footer').html('‎');
        }

        function startSSDL() {
            $('#tb_footer').html(`
                <span class="spinner spinner-border spinner-border-sm mr-1"></span>
                Đang chạy chức năng so sánh dung lượng
            `);

            $('#startedAt').val(moment().format('HH:mm'));

            chaySSDL();
        }

        function chaySSDL() {
            index = 0;
            index_saved++;

            timeout = setTimeout(traCuuDungLuong, interval * 1000 * 60 * 60);
        }

        async function traCuuDungLuong() {
            let sdt = lines[index++];
            let row = $(`[data-sdt="${sdt}"]`);

            try {
                let result = await $.ajax({
                    type: 'POST',
                    url: "{{ route('tra-cuu-mi.post') }}",
                    data: {'sdt': sdt},
                });

                let tach = result.split('|');

                if (tach.length > 1) row.children().eq(5 + index_saved).text(tach[2]);
            } catch (error) {
                noty('Lỗi ngoại biên!', 'error');
            }

            if (index < total) timeout = setTimeout(traCuuDungLuong, delay * 1000);
            else if (index_saved < 2) chaySSDL();
            else stop();
        }
    });
</script>
@endpush