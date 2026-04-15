<div class="row mb-3">
    <div class="col">
        <div class="card">
            <div class="card-header bg-primary text-white py-1">
                <strong>Thông tin thuê bao</strong>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm text-nowrap">
                        <tr>
                            <th class="text-right">Số TB:</th>
                            <td colspan="2"><strong class="text-primary">{{ $tttb['so_tb'] ?? '' }}</strong></td>
                            <th class="text-right">
                                Esim: 
                                @if (isset($tttb['esim']) && $tttb['esim'] == 1)
                                <i class="fa-solid fa-square-check mx-1"></i>
                                @else
                                <i class="fa-regular fa-square mx-1"></i>
                                @endif
                                MSIN:
                            </th>
                            <td colspan="2">{{ $tttb['so_msin'] ?? '' }}</td>
                        </tr>
                        <tr>
                            <th class="text-right">Gọi đi:</th>
                            <td colspan="2">
                                @if (isset($tttb['goi_di']) && $tttb['goi_di'] == 1)
                                <i class="fa-solid fa-square-check"></i>
                                @else
                                <i class="fa-regular fa-square"></i>
                                @endif
                            </td>
                            <th class="text-right">Gọi đến:</th>
                            <td colspan="2">
                                @if (isset($tttb['goi_den']) && $tttb['goi_den'] == 1)
                                <i class="fa-solid fa-square-check"></i>
                                @else
                                <i class="fa-regular fa-square"></i>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-right">Loại TB/Tỉnh:</th>
                            <td><strong class="text-primary">{{ $tttb['loai_tb'] ?? '' }}</strong></td>
                            <td><strong class="text-primary">{{ $tttb['ma_tinh'] ?? '' }}</strong></td>
                            <th class="text-right">Ngày KH:</th>
                            <td colspan="2">{{ $tttb['ngay_kh'] ?? '' }}</td>
                        </tr>
                        <tr>
                            <th class="text-right">PIN/PUK:</th>
                            <td>{{ $tttb['pin'] ?? '' }}</td>
                            <td>{{ $tttb['puk'] ?? '' }}</td>
                            <th class="text-right">PIN2/PUK2:</th>
                            <td>{{ $tttb['pin2'] ?? '' }}</td>
                            <td>{{ $tttb['puk2'] ?? '' }}</td>
                        </tr>
                        <tr>
                            <th class="text-right">NS cá nhân/G.tính:</th>
                            <td>{{ $ttkh['ngay_sinh'] ?? '' }}</td>
                            <td>{{ $ttkh['phai'] ?? '' }}</td>
                            <th class="text-right">Số GT KH/N.cấp:</th>
                            <td>{{ $ttkh['so_gt'] ?? '' }}</td>
                            <td>{{ $ttkh['ngaycap_gt'] ?? '' }}</td>
                        </tr>
                        <tr>
                            <th class="text-right">Đối tượng:</th>
                            <td colspan="5"><span class="text-danger">{{ $ttkh['doituong'] ?? '' }}</span></td>
                        </tr>
                        <tr>
                            <th class="text-right">Tên thuê bao:</th>
                            <td colspan="5">{{ $ttkh['ten_tb'] ?? '' }}</td>
                        </tr>
                        <tr>
                            <th class="text-right">Tên khách hàng:</th>
                            <td colspan="5">{{ $ttkh['ten_kh'] ?? '' }}</td>
                        </tr>
                        <tr>
                            <th class="text-right">Địa chỉ chứng từ:</th>
                            <td colspan="5">{{ $ttkh['diachi_chungtu'] ?? '' }}</td>
                        </tr>
                        <tr>
                            <th class="text-right">Địa chi thanh toán:</th>
                            <td colspan="5">{{ $ttkh['diachi'] ?? '' }}</td>
                        </tr>
                        <tr>
                            <th class="text-right">ĐC giấy báo cước:</th>
                            <td colspan="5">{{ $ttkh['diachi_thuongtru'] ?? '' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-header bg-primary text-white py-1">
                <strong>Các dịch vụ đang sử dụng</strong>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        @foreach ($dvu_tb as $key => $value)
                        <tr>
                            <td class="text-right">{{ $value[0] }}</td>
                            <td class="text-right">{{ $value[1] }}</td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col">
        <div class="card">
            <div class="card-header p-0">
                <ul class="nav nav-tabs nav-tabs-solid border rounded-top">
                    <li class="nav-item">
                        <a href="#lichSuThueBao" class="nav-link rounded-top active py-1" data-toggle="tab">
                            <strong>Lịch sử thuê bao</strong>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#lichSu3G" class="nav-link rounded-top py-1" data-toggle="tab">
                            <strong>Lịch sử dịch vụ 3G</strong>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="lichSuThueBao">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="bg-dark text-white">
                                    <tr>
                                        <th class="text-center">Ngày thực hiện</th>
                                        <th class="text-center">Mã DV</th>
                                        <th class="text-center">Thao tác</th>
                                        <th class="text-center">Ghi chú</th>
                                        <th class="text-center">Người dùng</th>
                                        <th class="text-center">MSIN cũ</th>
                                        <th class="text-center">MSIN mới</th>
                                        <th class="text-center">Tỉnh cũ</th>
                                        <th class="text-center">Tỉnh mới</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($ls_tb as $row)
                                    <tr>
                                        @for ($i = 0; $i < 9; $i++)
                                        <td>{{ $row[$i] ?? '' }}</td>
                                        @endfor
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="lichSu3G">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="bg-dark text-white">
                                    <tr>
                                        <th class="text-center">STT</th>
                                        <th class="text-center">Mã DV</th>
                                        <th class="text-center">Dịch vụ</th>
                                        <th class="text-center">Gói</th>
                                        <th class="text-center">Thao tác</th>
                                        <th class="text-center">Ngày bắt đầu</th>
                                        <th class="text-center">Ngày kết thúc</th>
                                        <th class="text-center">Người cập nhật</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($ls_3g as $row)
                                    <tr>
                                        @for ($i = 0; $i < 8; $i++)
                                        <td>{{ $row[$i] ?? '' }}</td>
                                        @endfor
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>