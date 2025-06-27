<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\Nila\ClientService;
use App\Services\Nila\HolooService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class NilaController extends Controller
{
    protected $holoo;
    protected $clientService;


    public function __construct(HolooService $holoo, ClientService $clientService)
    {
        $this->holoo = $holoo;
        $this->clientService = $clientService;
    }

}
