<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;


class Catalog extends Model
{
    protected $table = 'catalogs';
    protected $guarded = ['id'];

    public function scopeFilter($query, Request $request)
    {
        if ($name = $request->input('query.name')) {
            $query->where('name', 'like', "%$name%");
        }
        if ($status = $request->input('query.status')) {
            $query->where('status', $status);
        }
        return $query;
    }

}
