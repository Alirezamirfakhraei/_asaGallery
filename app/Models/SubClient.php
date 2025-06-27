<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;


class SubClient  extends Authenticatable
{
    protected $table = 'sub_clients';
    protected $primaryKey = 'id';



    protected $fillable = [
        'client_id',
        'username',
        'password',
        'fullName',
        'status',
    ];

    protected $hidden = [
        'password',
    ];



    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function user()
    {
        return $this->belongsTo(SubClient::class, 'client_id', 'id');
    }

}
