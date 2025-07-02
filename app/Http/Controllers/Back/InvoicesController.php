<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\Nila\FinanceService;
use App\Services\Nila\HolooService;

class InvoicesController extends Controller
{

    protected $holoo;
    protected $service;

    public function __construct(HolooService $holoo, FinanceService $service)
    {
        $this->holoo = $holoo;
        $this->service = $service;
    }


    public function invoices()
    {
        $filters = [
            'code.from' => 10,
            'code.to' => 200,
            'type' => 2
        ];
        $data = $this->holoo->callApi('invoice', 'GET', $filters);
        dd($data);
        $this->service->syncInvoicesFromHoloo($data);
        $clients = Invoice::orderBy('code', 'desc')->paginate(20);
        return view('back.nila.clients.getClients', compact('clients'));
    }
}
