@extends('admins.layouts.master')

@push('styles')
<style>
    .table-responsive {
        max-height: 400px;
        overflow-y: auto;
        overscroll-behavior: contain;

        .table {
            th, td {
                border-collapse: separate;
            }

            thead {
                tr {
                    background-color: inherit;

                    th {
                        position: sticky;
                        top: 0;
                        background-color: inherit;
                        box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4);
                    }
                }
            }
        }
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <div class="row search-form mb-2">
                    <div class="col-lg-3 col-12 mb-2">
                        <label>Ngày tháng</label>
                        <input type="text" class="form-control datepicker" name="date_time" readonly>
                    </div>
                    <div class="col-lg-3 col-12 mb-2">
                        <label>Số thuê bao</label>
                        <input type="text" class="form-control" name="mobile_number">
                    </div>
                    <div class="col-lg-3 col-12 mb-2">
                        <label>Mã dịch vụ</label>
                        <select class="form-control select2" name="service_code">
                            <option></option>
                            @foreach ($service_codes as $key => $value)
                            <option value="{{ $value }}">{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-12 mb-2">
                        <label>Thao tác</label>
                        <select class="form-control select2" name="action">
                            <option></option>
                            @foreach ($actions as $key => $value)
                            <option value="{{ $value }}">{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-12 mb-2">
                        <label>Loại thuê bao</label>
                        <select class="form-control select2" name="sub_type">
                            <option></option>
                            @foreach ($sub_types as $key => $value)
                            <option value="{{ $value }}">{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-12 mb-2">
                        <label>Số SIM cũ</label>
                        <input type="text" class="form-control" name="old_esim">
                    </div>
                    <div class="col-lg-3 col-12 mb-2">
                        <label>Số SIM mới</label>
                        <input type="text" class="form-control" name="new_esim">
                    </div>
                    <div class="col-lg-3 col-12 mb-2">
                        <label>Người dùng</label>
                        <select class="form-control select2" name="account">
                            <option></option>
                            @foreach ($accounts as $key => $value)
                            <option value="{{ $value }}">{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row justify-content-end">
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary btn-search">
                            <i class="fa-solid fa-magnifying-glass mr-2"></i>
                            Tìm kiếm
                        </button>
                        <button type="button" class="btn btn-warning btn-reset">
                            <i class="fa-solid fa-rotate fa-flip-horizontal mr-2"></i>
                            Làm mới
                        </button>
                    </div>
                </div>

                <hr>

                <div class="row mb-2 justify-content-start">
                    <div class="col-auto">
                        <select name="paginate" class="form-control">
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="500">500</option>
                            <option value="1000">1000</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col list-container">
                        @include('admins.sections.esim-report.statistical.list-partial', ['esim_reports' => $esim_reports])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.datepicker').daterangepicker({
            parentEl: '.content-inner',
            showDropdowns: true,
            maxDate: moment().format('DD/MM/YYYY'),
            autoUpdateInput: false,
            locale: {
                applyLabel: 'OK',
                cancelLabel: 'Xóa',
                firstDay: 1,
                format: 'DD/MM/YYYY',
            }
        }).on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
        }).on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });

        $('.select2').select2({
            placeholder: "‎",
            allowClear: true,
        });

        $('select[name="paginate"]').select2({
            minimumResultsForSearch: Infinity,
        });

        const params = function() {
            let inputs = $('.search-form').find('input.form-control, select.select2');
            let params = [];

            inputs.each((index, input) => {
                let name = $(input).attr("name");
                let value = $(input).val();

                if (name == 'date_time') {
                    let split = value.split(' - ');
                    params['date_time_start'] = split[0];
                    params['date_time_end'] = split[1];
                    return true;
                }

                params[name] = value;
            });

            return params;
        };

        function fetchData(url) {
            let paginate = $('select[name="paginate"]').val();
            let overlay = $('.table-container .overlay');
            
            overlay.addClass('d-flex');

            $.ajax({
                url: url,
                data: {
                    params: {...params()},
                    paginate: paginate,
                },
                success: function(data) {
                    $('.list-container').eq(0).html(data);
                },
                error: function(xhr, status, error) {
                    noty('Đã xảy ra lỗi!', 'error');
                },
                complete: function() {
                    overlay.removeClass('d-flex');
                }
            });
        }

        $(document).on('select2:select', 'select[name="paginate"]', function() {
            let url = "{{ route('esim-report.statistical.index') }}";
            fetchData(url);
        });

        $(document).on('click', '.btn-search', function() {
            let url = "{{ route('esim-report.statistical.index') }}";
            fetchData(url);
        });

        $(document).on('click', '.pagination-links .pagination a', function (event) {
            event.preventDefault();
            let url = $(this).attr('href');
            fetchData(url);
        });

        $(document).on('click', '.btn-reset', function() {
            $('.search-form').find('input.form-control, select.select2').val("").trigger('change');
        });
    });
</script>
@endpush