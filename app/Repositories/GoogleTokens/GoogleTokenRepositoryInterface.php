<?php

namespace App\Repositories\GoogleTokens;

use App\Repositories\RepositoryInterface;

interface GoogleTokenRepositoryInterface extends RepositoryInterface
{
    public function getToken();
    public function getLatestToken();
}