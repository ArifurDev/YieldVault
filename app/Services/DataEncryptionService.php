<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Log;

class DataEncryptionService
{
    protected string $secret;
    protected string $algo;

    public function __construct()
    {
        $this->secret = config('jwt.secret');
        $this->algo   = config('jwt.algo', 'HS256');

        if (empty($this->secret)) {
            throw new \RuntimeException(
                'JWT_SECRET is null. Check .env and run php artisan config:clear'
            );
        }

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
