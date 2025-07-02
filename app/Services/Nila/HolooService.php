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

    public function callApi($endpoint, $method = 'GET', $params = [])
    {
        $token = $this->getToken();
        dd($token);
        if (!$token) return ['error' => 'Token failed'];

        // اگر متد GET هست، پارامترها را به صورت query string بچسبان
        if ($method === 'GET' && !empty($params)) {
            $endpoint .= '?' . http_build_query($params);
        }

        $url = "{$this->baseUrl}/{$endpoint}";
        $request = Http::withToken($token);
        // حالا فقط اگر POST بود، پارامترها را به عنوان body بفرست
        $response = $method === 'POST' ? $request->post($url, $params) : $request->get($url);

        // اگر توکن منقضی شده بود
        if ($response->status() === 401) {
            Cache::forget($this->tokenCacheKey);
            $token = $this->getToken();
            if (!$token) return ['error' => 'Token refresh failed'];
            $request = Http::withToken($token);
            $response = $method === 'POST' ? $request->post($url, $params) : $request->get($url);
        }

        return $response->json();
    }

}
