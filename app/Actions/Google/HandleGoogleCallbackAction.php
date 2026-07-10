<?php

namespace App\Actions\Google;

use App\DTO\GoogleTokenDTO;
use App\Repositories\GoogleTokens\GoogleTokenRepositoryInterface;
use App\Services\GoogleService;

class HandleGoogleCallbackAction
{
    public function __construct(
        protected GoogleService $googleService,
        protected GoogleTokenRepositoryInterface $tokenRepo
    ) {}

    public function execute($code)
    {
        $client = $this->googleService->getClient();

        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            throw new \RuntimeException(
                'Google OAuth error: ' . $token['error']
            );
        }

        $dto = new GoogleTokenDTO($token);

        return $this->tokenRepo->create(
            $dto->toArray()
        );
    }
}