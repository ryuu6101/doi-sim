<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;
use App\Repositories\GoogleConfigs\GoogleConfigRepositoryInterface;
use App\Repositories\GoogleTokens\GoogleTokenRepositoryInterface;

class GoogleService
{
    public function __construct(
        protected GoogleConfigRepositoryInterface $configRepo,
        protected GoogleTokenRepositoryInterface $tokenRepo
    ) {}

    public function getClient(): Client
    {
        $config = $this->configRepo->getConfig();

        $client = new Client();

        $client->setClientId($config->client_id);
        $client->setClientSecret($config->client_secret);
        $client->setRedirectUri($config->redirect_uri);

        $client->addScope(Sheets::SPREADSHEETS);

        $client->setAccessType('offline');
        $client->setPrompt('consent');

        return $client;
    }

    public function getAuthorizedClient(): Client
    {
        $client = $this->getClient();

        $token = $this->tokenRepo->getLatestToken();

        if (!$token) {
            throw new \RuntimeException('Google token not found');
        }

        $client->setAccessToken([
            'access_token' => $token->access_token,
            'refresh_token' => $token->refresh_token,
            'expires_in' => $token->expires_in,
            'created' => $token->created_at_token
        ]);

        if ($client->isAccessTokenExpired()) {

            $newToken = $client->fetchAccessTokenWithRefreshToken(
                $token->refresh_token
            );

            if (isset($newToken['error'])) {
                throw new \RuntimeException('Google refresh token failed: '.$newToken['error']);
            }

            $this->tokenRepo->update(
                $token->id,
                [
                    'access_token' => $newToken['access_token'],
                    'expires_in' => $newToken['expires_in'],
                ]
            );

            $client->setAccessToken($newToken);
        }

        return $client;
    }
}