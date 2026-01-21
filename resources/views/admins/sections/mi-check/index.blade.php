@extends('admins.layouts.master')

@push('styles')
<style>
    table.sortable th:not(.sorttable_nosort) {
        cursor: pointer;
    }
    th.sorttable_sorted::after, 
    th.sorttable_sorted_reverse::after {
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
    #sorttable_sortfwdind, #sorttable_sortrevind { display: none; }

</style>
@endpush

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
    let delay = {{ $delay ?? 1 }};
    let timeout;
    let lines = [];
    let index = 0;
    let total = 0;

    $(document).ready(function() {
        $(document).on('click', '.btn-run', function() {
            let list = $('textarea[name="list"]').val();

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
                if (line.length > 9) return line.slice(-9);
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
            let row = $('<tr></tr>');

            row.append($('<td class="text-center">' + (index) + '</td>'));
            row.append($('<td>' + (line ?? '') + '</td>'));
            row.append($('<td></td>'));
            row.append($('<td></td>'));
            row.append($('<td></td>'));

            $('#progress_list').append(row);

            await traCuuMI(row, line);

            if (index < total) timeout = setTimeout(chay, delay * 1000);
            else stop();
        }

        async function traCuuMI(row, sdt) {
            let name = row.children().eq(2);
            let limit = row.children().eq(3);
            let used = row.children().eq(4);

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
    });
</script>
@endpush