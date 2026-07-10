<?php

namespace App\Repositories\GoogleTokens;

use App\Models\GoogleToken;
use App\Repositories\BaseRepository;

class GoogleTokenRepository extends BaseRepository implements GoogleTokenRepositoryInterface
{
    public function getModel() {
        return GoogleToken::class;
    }

    public function getToken() {
        return $this->first();
    }

    public function getLatestToken() {
        return $this->model
            ->orderBy('id','DESC')
            ->first();
    }
}