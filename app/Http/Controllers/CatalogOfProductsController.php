<?php

namespace App\Http\Controllers;



use App\Models\Catalog;

class CatalogOfProductsController extends Controller
{

    public function view()
    {
        $catalogs = Catalog::query()->where('published' , true)->get()->toArray();
        return view('back.catalog.index' , compact('catalogs'));
    }



}
