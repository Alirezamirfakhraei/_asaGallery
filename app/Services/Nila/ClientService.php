<?php

namespace App\Services\Nila;

use App\Models\Client;
use App\Models\SubClient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ClientService
{
    protected $holoo;
    public function __construct(HolooService $holoo)
    {
        $this->holoo = $holoo;
    }
    public function createSubClient(array $clientData, array $subData): ?SubClient
    {
        $client = Client::where('code', $clientData['code'])->first();

        if (!$client) {
            Log::warning('Client not found for sub-client creation.', ['code' => $clientData['code']]);
            return null;
        }

        return SubClient::create([
            'client_id' => $client->id,
            'username' => $subData['code'] ?? $clientData['code'],
            'password' => bcrypt($subData['password'] ?? Str::random(8)),
            'fullName' => $subData['fullName'] ?? null,
            'status' => $subData['status'] ?? 0,
        ]);
    }
    public function createUser(array $data): ?User
    {
        $client = Client::where('code', $data['code'])->first();

        if (!$client) {
            Log::warning('Client not found for user creation.', ['code' => $data['code']]);
            return null;
        }

        if (User::where('username', $data['code'])->exists()) {
            return null;
        }

        $password = $data['password'] ?? Str::random(8);
        // TODO: Send this password to the user via SMS/email if needed.

        return User::create([
            'client_id' => $client->id,
            'first_name' => $data['name'],
            'last_name' => $data['name'],
            'username' => $data['code'],
            'password' => bcrypt($password),
            'verified_at' => Carbon::now(),
        ]);
    }
    public function createFullClient(array $data): bool
    {
        return DB::transaction(function () use ($data) {
            // ارسال داده به Holoo
            $custinfo = [[
                'id' => '',
                'name' => $data['name'],
                'tel' => $data['tel'],
                'city' => $data['city'],
                'custtype' => $data['custtype'],
                'address' => $data['address'],
                'ispurchaser' => $data['isPurchaser'],
                'isseller' => $data['isSeller'],
            ]];

            $response = $this->holoo->callApi('Customer', 'POST', ['custinfo' => $custinfo]);
            $erpCode = data_get($response, 'Success.Code');

            if (!$erpCode) {
                throw new \Exception('ERP code not returned from Holoo.');
            }

            $data['code'] = $erpCode;

            Client::firstOrCreate(
                ['code' => $erpCode],
                [
                    'name' => $data['name'],
                    'mobile' => $data['tel'],
                    'address' => $data['address'],
                    'custtype' => $data['custtype'],
                    'ispurchaser' => $data['isPurchaser'],
                    'isseller' => $data['isSeller'],
                ]
            );

            // ایجاد SubClient
            if (!empty($data['sub_clients']) && is_array($data['sub_clients'])) {
                foreach ($data['sub_clients'] as $subData) {
                    $this->createSubClient($data, $subData);
                }
            } else {
                $this->createSubClient($data, [
                    'code' => $data['code'],
                    'password' => $data['password'] ?? Str::random(8),
                    'fullName' => $data['name'],
                    'status' => 1,
                ]);
            }

            // ایجاد User
            $this->createUser($data);

            return true;
        });
    }
    public function syncFromHoloo(array $customers): bool
    {
        DB::beginTransaction();

        try {
            $rows = array_slice($customers['Customer'] ?? [], 1);
            $holooCodes = [];

            foreach ($rows as $item) {
                $code = $item['Code'] ?? null;
                if (!$code) {
                    continue;
                }

                $holooCodes[] = $code;

                $attributes = [
                    'name' => $item['Name'] ?? null,
                    'code' => $code,
                    'isPurchaser' => $item['IsPurchaser'] ?? false,
                    'isSeller' => $item['IsSeller'] ?? false,
                    'isBlackList' => $item['IsBlackList'] ?? false,
                    'isVaseteh' => $item['IsVaseteh'] ?? false,
                    'vasetehPorsant' => $item['VasetehPorsant'] ?? 0.0,
                    'mandeh' => $item['Mandeh'] ?? 0.0,
                    'credit' => $item['Credit'] ?? 0.0,
                    'mobile' => $item['Mobile'] ?? '-',
                    'address' => $item['Address'] ?? '-',
                    'type' => $item['type'] ?? 0,
                    'isActive' => $item['IsActive'] ?? true,
                    'selectedPriceType' => $item['selectedPriceType'] ?? 0,
                    'isAmer' => $item['isAmer'] ?? false,
                    'sumFloatCheques' => $item['sumFloatCheques'] ?? 0.0,
                    'sumFloatNotCashedCheques' => $item['sumFloatNotCashedCheques'] ?? 0.0,
                    'debtInCheques' => $item['debtInCheques'] ?? 0.0,
                    'creditDiff' => $item['creditDiff'] ?? 0.0,
                    'sellerWithTax' => $item['sellerWithTax'] ?? true,
                ];

                $client = Client::where('code', $code)->first();

                if (!$client) {
                    Client::create(array_merge(['erpCode' => $item['ErpCode'] ?? null], $attributes));
                } else {
                    $dirty = array_diff_assoc($attributes, $client->only(array_keys($attributes)));
                    if (!empty($dirty)) {
                        $client->update($attributes);
                    }
                }
            }

            // حذف مشتریانی که دیگر در Holoo نیستند (فقط اگر erpCode دارند)
            if (!empty($holooCodes)) {
                Client::whereNotIn('code', $holooCodes)
                    ->whereNotNull('code')
                    ->delete();
            }

            DB::commit();
            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[Holoo Sync] خطا در همگام‌سازی مشتریان', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return false;
        }
    }

}
