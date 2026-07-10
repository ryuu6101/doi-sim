<?php

namespace App\DTO;

class GoogleTokenDTO
{
    protected $accessToken;
    protected $refreshToken;
    protected $expiresIn;
    protected $scope;
    protected $tokenType;
    protected $created;

    public function __construct(array $data)
    {
        $this->accessToken = $data['access_token'];
        $this->refreshToken = $data['refresh_token'] ?? null;
        $this->expiresIn = $data['expires_in'];
        $this->scope = $data['scope'];
        $this->tokenType = $data['token_type'];
        $this->created = $data['created'];
    }

    public function toArray()
    {
        return [
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'expires_in' => $this->expiresIn,
            'scope' => $this->scope,
            'token_type' => $this->tokenType,
            'created_at_google' => $this->created
        ];
    }
}