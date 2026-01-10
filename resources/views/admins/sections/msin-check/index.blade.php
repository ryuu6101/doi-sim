@extends('admins.layouts.master')

@section('content')
<div class="row flex-lg-nowrap">
    <div class="col-lg-auto col-12 mb-2">
        <div class="card">
            <div class="card-body">
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
                        <th class="text-center" style="width:5rem">STT</th>
                        <th class="text-center">Số MSIN</th>
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
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $(document).on('click', '.btn-run', function() {
            let list = $('textarea[name="list"]').val();
            console.log('test')
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
            $('#tb_footer').html('<i class="fa-solid fa-spinner spinner ml-2"></i>Vui lòng không đóng hoặc tải lại trang');
            $('.btn-check').prop('disabled', true);

            chay();
        });

        async function chay() {
            let boline = lines[index++].split(/[\t|]/);
            let row = $('<tr></tr>');

            row.append($('<td class="text-center">' + (index) + '</td>'));
            row.append($('<td>' + (boline[0] ?? '') + '</td>'));
            row.append($('<td></td>'));

            $('#progress_list').append(row);

            await checkmsin(row, boline);

            if (index < total) setTimeout(chay, 1000);
            else stop();
        }

        async function checkmsin(row, boline) {
            let msin = boline[0];

            let cell = row.children().eq(2);
            cell.text('Đang tìm kiếm ...');

            try {
                let result = await $.ajax({
                    type: 'POST',
                    url: "{{ route('check-msin.post') }}",
                    data: {'msin': msin},
                });

                cell.text(result);
            } catch (error) {
                cell.text('Lỗi ngoại biên!');
            }
        }

        function stop() {
            $('.btn-check').prop('disabled', false);
            $('#tb_footer').addClass('d-none');
            $('#tb_footer').html('‎');
        }
    });
</script>
@endpush