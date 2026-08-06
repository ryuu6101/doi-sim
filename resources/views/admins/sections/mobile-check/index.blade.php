@extends('admins.layouts.master')

@section('content')
<div class="row">
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
    <div class="col-12">
        <div class="table-responsive">
            <table class="table table-bordered table-xs bg-white">
                <thead class="text-nowrap">
                    <tr>
                        <th class="text-center" style="width:5rem">STT</th>
                        <th class="text-center">Số thuê bao</th>
                        <th class="text-center">Số IMEI</th>
                        <th class="text-center">Loại TB</th>
                        <th class="text-center">Chủ thuê bao</th>
                        <th class="text-center">Người đảo gần nhất</th>
                        <th class="text-center">Ngày đảo gần nhất</th>
                        <th class="text-center">Mã DV</th>
                        <th class="text-center">Tỉnh mới</th>
                        <th class="text-center">Ghi chú</th>
                        <th class="text-center">Gói</th>
                        <th class="text-center">Ngày kết thúc</th>
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

            lines = list.split("\n").filter((line) => {return line != ""}).map((line) => {
                line = line.trim();
                if (line.length < 11) return "84"+line;
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
            row.append($('<td></td>'));
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

            let matinh = await layIMEI(row, line);
            matinh && await layTTKhTb(row, line, matinh);
            await layLSuTBao(row, line);
            await layLsu3g(row, line);

            if (index < total) timeout = setTimeout(chay, delay * 1000);
            else stop();
        }

        async function layIMEI(row, sdt) {
            let imei = row.children().eq(2);
            let loai_tb = row.children().eq(3);

            imei.text('Đang tìm kiếm ...');

            try {
                let result = await $.ajax({
                    type: 'POST',
                    url: "{{ route('lay-tttb-v4.post') }}",
                    data: {
                        'sdt': sdt,
                        'string_data': ['so_msin', 'loai_tb', 'ma_tinh'],
                    },
                });

                if (!result.includes("OK|")) {
                    imei.text("Vui lòng đăng nhập lại!");
                    return false;
                }

                let tach = result.split("|");

                imei.text(tach[1]);
                loai_tb.text(tach[2]);

                return tach[3];
            } catch (error) {
                imei.text('Lỗi ngoại biên!');
                return false;
            }
        }

        async function layTTKhTb(row, sdt, matinh) {
            let cell = row.children().eq(4);
            cell.text('Đang tìm kiếm ...');

            try {
                let result = await $.ajax({
                    type: 'POST',
                    url: "{{ route('lay-tttb.post') }}",
                    data: {
                        'sdt': sdt,
                        'matinh': matinh,
                    },
                });

                cell.text(result);
            } catch (error) {
                cell.text('Lỗi ngoại biên!');
            }
        }

        async function layLSuTBao(row, sdt) {
            let nguoi_thuc_hien = row.children().eq(5);
            let ngay_thuc_hien = row.children().eq(6);
            let ma_dv = row.children().eq(7);
            let tinh_moi = row.children().eq(8);
            let ghi_chu = row.children().eq(9);

            nguoi_thuc_hien.text('Đang tìm kiếm ...');

            try {
                let result = await $.ajax({
                    type: 'POST',
                    url: "{{ route('lay-lsu-tbao.post') }}",
                    data: {'sdt': sdt},
                });

                let lsu_gan_nhat = result[0] ?? [];

                nguoi_thuc_hien.text(lsu_gan_nhat[4] ?? '-');
                ngay_thuc_hien.text(lsu_gan_nhat[0] ?? '-');
                ma_dv.text(lsu_gan_nhat[1] ?? '-');
                tinh_moi.text(lsu_gan_nhat[8] ?? '-');
                ghi_chu.text(lsu_gan_nhat[3] ?? '-');
            } catch (error) {
                nguoi_thuc_hien.text('Lỗi ngoại biên!');
                ngay_thuc_hien.text('Lỗi ngoại biên!');
                ma_dv.text('Lỗi ngoại biên!');
                tinh_moi.text('Lỗi ngoại biên!');
                ghi_chu.text('Lỗi ngoại biên!');
            }
        }

        async function layLsu3g(row, sdt) {
            let goi = row.children().eq(10);
            let ngay_ket_thuc = row.children().eq(11);

            goi.text('Đang tìm kiếm ...');

            try {
                let result = await $.ajax({
                    type: 'POST',
                    url: "{{ route('lay-lsu-3g.post') }}",
                    data: {'sdt': sdt},
                });

                let lsu_gan_nhat = result[0] ?? [];

                goi.text(lsu_gan_nhat[3] ?? '-');
                ngay_ket_thuc.text(lsu_gan_nhat[6] ?? '-');
            } catch (error) {
                goi.text('Lỗi ngoại biên!');
                ngay_ket_thuc.text('Lỗi ngoại biên!');
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