<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'code',              // شماره فاکتور
        'type',              // نوع فاکتور
        'sanadCode',         // شماره سند
        'comment',           // توضیحات فاکتور
        'customerName',      // نام طرف حساب
        'customerErpCode',   // شناسه طرف حساب در هلو
        'date',              // تاریخ فاکتور
        'time',              // ساعت فاکتور
        'sumNaghd',          // مبلغ نقد
        'sumNesiyeh',        // مبلغ نسیه
        'sumDiscount',       // مبلغ تخفیف
        'sumCheck',          // مبلغ چک
        'sumScot',           // مبلغ مالیات و عوارض
        'sumPrice',          // مبلغ فاکتور
        'erpCode',           // شناسه فاکتور در هلو
        'detail',            // جزئیات فاکتور
        'serials'            // سریال‌های استفاده‌شده
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    protected $hidden = [];
}
