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
    </div>
    <div class="col">
        <div class="table-responsive">
            <table class="table table-bordered table-xs bg-white sortable">
                <thead class="text-nowrap">
                    <tr>
                        <th class="text-center" style="width:5rem">STT</th>
                        <th class="text-center">Số thuê bao</th>
                        <th class="text-center">Loại TB</th>
                        <th class="text-center">Tên thuê bao</th>
                        <th class="text-center">ĐC thường trú</th>
                        <th class="text-center">Gọi đi</th>
                        <th class="text-center">Gọi đến</th>
                        <th class="text-center">MSIN</th>
                        <th class="text-center">Ngày KH</th>
                        <th class="text-center">Hạn sử dụng</th>
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
            let line = lines[index++] ?? '';
            let row = $(`
                <tr>
                    <td class="text-center">${index}</td>
                    <td>${line}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            `);

            $('#progress_list').append(row);

            $('#tb_footer')[0].scrollIntoView({
                behavior: 'smooth',
                block: 'end'
            });

            await traCuuTTTBao(row, line);

            if (index < total) timeout = setTimeout(chay, delay * 1000);
            else stop();
        }

        async function traCuuTTTBao(row, sdt) {
            let loai_tb = row.children().eq(2);
            let ten_tb = row.children().eq(3);
            let dia_chi = row.children().eq(4);
            let goi_di = row.children().eq(5);
            let goi_den = row.children().eq(6);
            let msin = row.children().eq(7);
            let ngay_kh = row.children().eq(8);
            let han_su_dung = row.children().eq(9);

            loai_tb.text('Đang tìm kiếm ...');

            try {
                let result = await $.ajax({
                    type: 'POST',
                    url: "{{ route('tra-cuu-tttb-ccos.post') }}",
                    data: {
                        'sdt': sdt,
                        'string_data': ['TEN_LOAI', 'FULLNAME', 'ADDRESS', 'GOI_DI', 'GOI_DEN', 'SO_MSIN', 'NGAY_KH', 'HSD']
                    },
                });

                let tach = result.split('|');

                if (tach.length <= 1) {
                    loai_tb.text(tach[0]);
                    return;
                }

                loai_tb.text(tach[1]);
                ten_tb.text(tach[2]);
                dia_chi.text(tach[3]);
                goi_di.text(tach[4] == "A" ? 'Bật' : 'Tắt');
                goi_den.text(tach[5] == "A" ? 'Bật' : 'Tắt');
                msin.text(tach[6]);
                ngay_kh.text(tach[7]);
                han_su_dung.text(tach[8]);
            } catch (error) {
                loai_tb.text('Lỗi ngoại biên!');
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