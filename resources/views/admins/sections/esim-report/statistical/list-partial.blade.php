<div class="table-container">
    <div class="table-responsive mb-4 border-bottom position-relative">
        <table class="table table-bordered table-sm">
            <thead class="bg-dark text-white">
                <tr>
                    <th class="text-center">STT</th>
                    <th class="text-center">Ngày tháng thực hiện</th>
                    <th class="text-center">Số thuê bao</th>
                    <th class="text-center">Mã DV</th>
                    <th class="text-center">Thao tác</th>
                    <th class="text-center">Loại thuê bao</th>
                    <th class="text-center">Số SIM cũ</th>
                    <th class="text-center">Số SIM mới</th>
                    <th class="text-center">Người dùng</th>
                    <th class="text-center">Ghi chú</th>
                </tr>
            </thead>
            <tbody>
                @if (isset($esim_reports) && $esim_reports->count() > 0)
                @foreach ($esim_reports as $key => $value)
                <tr>
                    <td class="text-center">
                        {{ ($esim_reports->currentPage() - 1) * $esim_reports->perPage() + $loop->iteration }}
                    </td>
                    <td class="text-center">
                        {{ $value->date_time->format('d/m/Y H:i:s') }}
                    </td>
                    <td class="text-center">
                        {{ $value->mobile_number }}
                    </td>
                    <td class="text-center">
                        {{ $value->service_code }}
                    </td>
                    <td class="text-center">
                        {{ $value->action }}
                    </td>
                    <td class="text-center">
                        {{ $value->sub_type }}
                    </td>
                    <td class="text-center">
                        {{ $value->old_esim }}
                    </td>
                    <td class="text-center">
                        {{ $value->new_esim }}
                    </td>
                    <td class="text-center">
                        {{ $value->account }}
                    </td>
                    <td class="text-center">
                        {{ $value->note }}
                    </td>
                </tr>
                @endforeach
                @else
                <tr>
                    <td class="text-center" colspan="100%">(Không có kết quả)</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="position-absolute top-0 w-100 h-100 bg-white opacity-50 d-none align-items-center justify-content-center overlay">
        <span class="spinner"><i class="fa-solid fa-spinner fa-2xl"></i></span>
    </div>
</div>

<div class="pagination-links d-flex flex-column">
    {!! $esim_reports->links('helpers.pagination') !!}
</div>