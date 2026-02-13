@extends('admins.layouts.master')

@section('content')
<div class="row flex-lg-nowrap">
    <div class="col-lg-4 col-12 mb-2">
        <div class="card">
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col">
                        <label><strong>Ngày tháng:</strong></label>
                        <input type="text" class="form-control datepicker date-range" readonly>
                    </div>
                </div>

                <hr>

                <div class="row mb-2">
                    <div class="col-6">
                        <button class="btn btn-outline-success btn-block btn-run text-nowrap">
                            <i class="fa-solid fa-play mr-1"></i>CHẠY
                        </button>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-outline-secondary btn-block btn-stop text-nowrap" disabled>
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
                        <th class="text-center">Ngày tháng</th>
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
        $('.datepicker').daterangepicker({
            parentEl: '.content-inner',
            showDropdowns: true,
            maxDate: moment().format('DD/MM/YYYY'),
            locale: {
                applyLabel: 'OK',
                cancelLabel: 'Hủy',
                firstDay: 1,
                format: 'DD/MM/YYYY',
            }
        });

        function dateRange(startDate, endDate) {
            let dates = [];

            let currentDate = moment(startDate).clone(); 
            let stopDate = moment(endDate);

            while (currentDate <= stopDate) {
                dates.push(currentDate.clone().format('DD/MM/YYYY'));
                currentDate.add(1, 'days');
            }

            return dates;
        }

        $(document).on('click', '.btn-run', function() {
            let date_range = $('.date-range').val().split(' - ');
            lines = dateRange(moment(date_range[0], "DD/MM/YYYY"), moment(date_range[1], "DD/MM/YYYY"));

            if (cookies == '') {
                noty('Không có Cookie, đăng nhập lại để tiếp tục!', 'error');
                return;
            }

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

        async function chay() {
            let line = lines[index++];
            let row = $('<tr></tr>');

            row.append($('<td class="text-center">' + (index) + '</td>'));
            row.append($('<td>' + (line ?? '') + '</td>'));
            row.append($('<td></td>'));

            $('#progress_list').append(row);

            $('#tb_footer')[0].scrollIntoView({
                behavior: 'smooth',
                block: 'end'
            });

            await layBcEsim(row, line);

            if (index < total) timeout = setTimeout(chay, delay * 1000);
            else stop();
        }

        async function layBcEsim(row, date) {
            let note = row.children().eq(2);
            note.text('Đang import ...');

            try {
                let lay_bc_esim = await $.ajax({
                    type: 'POST',
                    url: "{{ route('lay-bc-esim.post') }}",
                    data: {'date': date},
                });

                note.text(lay_bc_esim);
            } catch (error) {
                note.text('Lỗi ngoại biên!');
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