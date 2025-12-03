<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class DataEncryptionService
{
    protected string $secret;
    protected string $algo;

    public function __construct()
    {
        $this->secret = env('JWT_SECRET');
        $this->algo   = env('JWT_ALGO', 'HS256');
    }

    public function encrypt($data): string
    {
        $payload = [
            'data' => $data,
            'iat' => time()
        ];

        return JWT::encode($payload, $this->secret, $this->algo);
    }

    public function decrypt($token)
    {
        if (!$token) {
            return null;
        }
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algo));
            return $decoded->data;
        } catch (\Exception $e) {
            return null;
        }
    }
}
