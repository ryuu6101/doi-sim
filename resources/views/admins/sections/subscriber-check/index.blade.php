@extends('admins.layouts.master')

@push('styles')
<style>
    #mobileInfoContainer {
        table {
            th, td {
                padding-top: 0.3rem;
                padding-bottom: 0.3rem;
            }
        }
    }
</style>
@endpush

@section('content')

<form action="#!" id="subscriberCheckForm">
    <div class="row mb-2">
        <div class="col-12 col-sm-auto mb-2">
            <input type="text" name="number" class="form-control" placeholder="Nhập số thuê bao hoặc IMEI" style="min-width: 18rem">
            <span></span>
        </div>
        <div class="col-12 col-sm-auto mb-2 text-center">
            <button type="submit" class="btn btn-outline-primary">
                <i class="fa-solid fa-magnifying-glass mr-2"></i>
                Tìm kiếm
            </button>
        </div>
    </div>
</form>

<section id="mobileInfoContainer" style="scroll-margin-top:2.5rem"></section>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        let message = $('#subscriberCheckForm input[name="number"] + span');
        let number = '';

        $('#subscriberCheckForm').submit(function(e) {
            e.preventDefault();

            number = $('#subscriberCheckForm input[name="number"]').val();

            if (cookies == '') {
                noty('Không có Cookie, đăng nhập lại để tiếp tục!', 'error');
                return;
            }

            if (number == '') {
                noty('Vui lòng nhập dữ liệu!', 'error');
                return;
            }

            timkiem();
        });

        async function timkiem() {
            if ([9,11].includes(number.length)) {
                let sdt = number.length == 11 ? number.slice(-9) : number;
                kiemTraTTTBao(sdt);
            } else if ([10,20].includes(number.length)) {
                let imei = number.length == 20 ? number.slice(9,19) : number;
                let sdt = await kiemTraIMEI(imei);
                sdt && kiemTraTTTBao(sdt);
            } else {
                noty('Dữ liệu không hợp lệ!', 'error');
            }
        }

        async function kiemTraIMEI(imei) {
            try {
                message.text('Kiểm tra IMEI ...');

                let lay_tb = await $.ajax({
                    type: 'POST',
                    url: "{{ route('check-msin.post') }}",
                    data: {'msin': imei},
                });

                let tach = lay_tb.split("|");

                if (tach.length < 2) {
                    message.text(tach[0]);
                    return false;
                }

                return tach[1].slice(-9);
            } catch (error) {
                message.text('');
                noty('Đã xảy ra lỗi!', 'error');
                return false;
            }
        }

        function kiemTraTTTBao(sdt) {
            message.text('Tìm kiếm thông tin thuê bao ...');

            $.ajax({
                type: 'POST',
                url: "{{ route('kiem-tra-tttb.post') }}",
                data: {'sdt': sdt}
            }).done(function(data) {
                $('#mobileInfoContainer').html(data);
                $('#mobileInfoContainer')[0].scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }).fail(function() {
                noty('Đã xảy ra lỗi!', 'error');
            }).always(function() {
                $('#subscriberCheckForm input[name="number"] + span').text('');
            });
        }
    });
</script>
@endpush