<?php

namespace App\Services;

class CcbsServiceWrapper
{
    public function __construct(
        protected CcbsService $ccbsService,
    ) {}

    public function withLoginRetry(callable $task) {
        $result = $task();

        if ($result == "Vui lòng đăng nhập lại!") {
            if ($this->ccbsService->ccbsLogin()['status'] == 200) $result = $task();
        }

        return $result;
    }

    public function ccbsLogin($username = "", $password = "") {
        return $this->ccbsService->ccbsLogin($username, $password);
    }

    public function doiSim($sdt, $esim, $ghichu) {
        return $this->withLoginRetry(
            fn() => $this->ccbsService->doiSim($sdt, $esim, $ghichu)
        );
    }

    public function layMaSim($sdt) {
        return $this->withLoginRetry(
            fn() => $this->ccbsService->layMaSim($sdt)
        );
    }

    public function taiAnh($ma, $bar, $sdt) {
        return $this->withLoginRetry(
            fn() => $this->ccbsService->taiAnh($ma, $bar, $sdt)
        );
    }

    public function checkMSIN($msin) {
        return $this->withLoginRetry(
            fn() => $this->ccbsService->checkMSIN($msin)
        );
    }

    public function layTTThueBaoV4($sdt, $string_data) {
        return $this->withLoginRetry(
            fn() => $this->ccbsService->layTTThueBaoV4($sdt, $string_data)
        );
    }

    public function layTTTBao($sdt, $matinh) {
        return $this->withLoginRetry(
            fn() => $this->ccbsService->layTTTBao($sdt, $matinh)
        );
    }

    public function daoSim($sdt, $old_esim, $new_esim, $ghichu) {
        return $this->withLoginRetry(
            fn() => $this->ccbsService->daoSim($sdt, $old_esim, $new_esim, $ghichu)
        );
    }

    public function layDVu($sdt, $dich_vu) {
        return $this->withLoginRetry(
            fn() => $this->ccbsService->layDVu($sdt, $dich_vu)
        );
    }

    public function dmDVu($sdt, $dvu) {
        return $this->withLoginRetry(
            fn() => $this->ccbsService->dmDVu($sdt, $dvu)
        );
    }

    public function layIOC($sdt) {
        return $this->withLoginRetry(
            fn() => $this->ccbsService->layIOC($sdt)
        );
    }

    public function catmoIOC($sdt, $goidi, $goiden) {
        return $this->withLoginRetry(
            fn() => $this->ccbsService->catmoIOC($sdt, $goidi, $goiden)
        );
    }

    public function layBcEsim($date) {
        return $this->withLoginRetry(
            fn() => $this->ccbsService->layBcEsim($date)
        );
    }
}