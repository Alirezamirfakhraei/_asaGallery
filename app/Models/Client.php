<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = 'clients';

    protected $fillable = [
        'name',
        'code',
        'email',
        'isPurchaser',
        'isSeller',
        'isBlackList',
        'custtype',
        'bedsarfasl',
        'nationalid',
        'economicid',
        'isVaseteh',
        'tel',
        'vasetehPorsant',
        'fax',
        'city',
        'ostan',
        'mantagheh',
        'mandeh',
        'credit',
        'mobile',
        'erpCode',
        'zipcode',
        'address',
        'type',
        'isActive',
        'selectedPriceType',
        'isAmer ',
        'sumFloatCheques',
        'sumFloatNotCashedCheques',
        'debtInCheques',
        'creditDiff',
        'sellerWithTax',
        'password',
    ];

    protected $hidden = ['password'];

    public function getAuthIdentifierName()
    {
        return 'code';
    }


    public function subClients()
    {
        return $this->hasMany(SubClient::class, 'client_id');
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

}
