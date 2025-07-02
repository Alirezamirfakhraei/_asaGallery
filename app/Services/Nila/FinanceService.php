<?php

namespace App\Services\Nila;

use Illuminate\Support\Facades\DB;

class FinanceService
{

    public function syncInvoicesFromHoloo(array $invoices): bool
    {
        DB::beginTransaction();

        try {
            $rows = array_slice($invoices['Invoice'] ?? [], 1); // اگر ساختار API شبیه مشتری‌هاست
            $holooCodes = [];

            foreach ($rows as $item) {
                $code = $item['Code'] ?? null;
                if (!$code) continue;

                $holooCodes[] = $code;

                $attributes = [
                    'code' => $code,
                    'type' => $item['Type'] ?? null,
                    'sanadCode' => $item['SanadCode'] ?? null,
                    'comment' => $item['Comment'] ?? null,
                    'customerName' => $item['CustomerName'] ?? null,
                    'customerErpCode' => $item['CustomerErpCode'] ?? null,
                    'date' => $item['Date'] ?? null,
                    'time' => $item['Time'] ?? null,
                    'sumNaghd' => $item['SumNaghd'] ?? 0,
                    'sumNesiyeh' => $item['SumNesiyeh'] ?? 0,
                    'sumDiscount' => $item['SumDiscount'] ?? 0,
                    'sumCheck' => $item['SumCheck'] ?? 0,
                    'sumScot' => $item['SumScot'] ?? 0,
                    'sumPrice' => $item['SumPrice'] ?? 0,
                    'erpCode' => $item['ErpCode'] ?? null,
                    'detail' => $item['Detail'] ?? null,
                    'serials' => $item['Serials'] ?? null,
                    'client_id' => $item['ClientId'] ?? null,
                ];

                $invoice = Invoice::where('code', $code)->first();

                if (!$invoice) {
                    Invoice::create($attributes);
                } else {
                    $dirty = array_diff_assoc($attributes, $invoice->only(array_keys($attributes)));
                    if (!empty($dirty)) {
                        $invoice->update($attributes);
                    }
                }
            }

            if (!empty($holooCodes)) {
                Invoice::whereNotIn('code', $holooCodes)->delete();
            }

            DB::commit();
            return true;

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[Holoo Sync] خطا در همگام‌سازی فاکتورها', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return false;
        }
    }


}
