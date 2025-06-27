<?php

namespace App\Services\Nila;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class HolooService
{
    protected $baseUrl = 'http://84.241.9.75:8080/TncHoloo/api';

    protected $username = 'web';
    protected $userpass = 'MQ==';
    protected $dbname = 'Holoo1';

    protected $tokenCacheKey = '';

    public function getToken()
    {

        if (Cache::has($this->tokenCacheKey)) {
            return Cache::get($this->tokenCacheKey);
        }

        $response = Http::withOptions([
            'proxy' => null,
        ])->post("{$this->baseUrl}/Login", [
            'userinfo' => [
                'username' => $this->username,
                'userpass' => $this->userpass,
                'dbname' => $this->dbname,
            ]
        ]);

        $data = $response->json();
        if (!empty($data['Login']['State']) && $data['Login']['State'] === true) {
            $token = $data['Login']['Token'];
            Cache::put($this->tokenCacheKey, $token, now()->addSeconds(86400));
            return $token;
        }

        return null;
    }

    public function callApi($endpoint, $method = 'GET', $body = [])
    {
        $token = $this->getToken();
        if (!$token) return ['error' => 'Token failed'];

        $url = "{$this->baseUrl}/{$endpoint}";
        $request = Http::withToken($token);
        $response = $method === 'POST' ? $request->post($url, $body) : $request->get($url);
        // اگر توکن منقضی شده بود
        if ($response->status() === 401) {
            Cache::forget($this->tokenCacheKey);
            $token = $this->getToken();
            if (!$token) return ['error' => 'Token refresh failed'];
            $request = Http::withToken($token);
            $response = $method === 'POST' ? $request->post($url, $body) : $request->get($url);
        }

        return $response->json();
    }
}
