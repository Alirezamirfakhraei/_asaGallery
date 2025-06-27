<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\SubClient;
use App\Models\User;
use App\Services\Nila\ClientService;
use App\Services\Nila\HolooService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{
    protected $holoo;
    protected $clientService;

    public function __construct(HolooService $holoo, ClientService $clientService)
    {
        $this->holoo = $holoo;
        $this->clientService = $clientService;
    }

    public function getClients()
    {
        $data = $this->holoo->callApi('Customer');
        $this->clientService->syncFromHoloo($data);
        $clients = Client::orderBy('code', 'desc')->paginate(20);
        return view('back.nila.clients.getClients', compact('clients'));
    }

    public function create()
    {
        return view('back.nila.clients.create');
    }

    public function store(Request $request)
    {
        try {
            $data = $request->all();

            // اصلاح مقادیر چک‌باکس‌ها
            $data['isPurchaser'] = $request->has('isPurchaser');
            $data['isSeller'] = $request->has('isSeller');

            // حذف ساب‌کلاینت‌های خالی
            if (isset($data['sub_clients'])) {
                $data['sub_clients'] = array_filter($data['sub_clients'], function ($sub) {
                    return !empty($sub['fullName']);
                });

                if (empty($data['sub_clients'])) {
                    unset($data['sub_clients']);
                }
            }

            $validatedData = validator($data, [
                'name' => 'required|string|max:255',
                'tel' => 'nullable|string|max:20',
                'city' => 'nullable|string|max:50',
                'custtype' => 'required|integer',
                'address' => 'nullable|string|max:500',
                'isPurchaser' => 'boolean',
                'isSeller' => 'boolean',
                'hasSubClient' => 'boolean',
                'sub_clients' => 'nullable|array',
                'sub_clients.*.fullName' => 'required_with:sub_clients|string|max:255',
                'sub_clients.*.password' => 'required_with:sub_clients|string|max:255',
                'sub_clients.*.status' => 'required_with:sub_clients|in:active,inactive',
            ])->validate();
            $this->clientService->createFullClient($validatedData);
            $this->holoo->callApi('Customer');
            toastr()->success('کلاینت با موفقیت ایجاد شد.');
            $clients = Client::orderBy('code', 'desc')->paginate(20);
            return view('back.nila.clients.getClients', compact('clients')); // مسیر اصلاح شد
        } catch (\Exception $e) {
            Log::error('Validation failed', [
                'errors' => $e->getMessage(),
            ]);
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function search(Request $request)
    {
        $search = $request->get('query');
        $search = str_replace('ي', 'ی', $search);

        $clients = Client::where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%" . str_replace('ی', 'ي', $search) . "%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%" . str_replace('ی', 'ي', $search) . "%");
        })->get();

        $html = '';
        foreach ($clients as $client) {
        $html .= '<tr>
        <td>' . $client->code . '</td>
        <td>' . $client->name . '</td>
        <td>' . $client->erpCode . '</td>
        <td>' . $client->mobile . '</td>
        <td>' . $client->address . '</td>
        <td class="text-center">
            <a href="' . route('admin.subClients.add.view', $client->id) . '" class="btn btn-sm btn-outline-primary" title="ثبت اکانت">
                <i class="fa fa-plus"></i>
            </a>
        </td>
        </tr>';
        }

        return response()->json(['html' => $html]);
    }


    public function addSubClientView($id)
    {
        $client = Client::findOrFail($id);
        return view('back.nila.clients.addSubClient', compact('client'));
    }

    public function addSubClient(Request $request)
    {
        $request->validate([
//            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string',
            'password' => 'required|string|min:6',
            'status' => 'required|in:0,1',
        ]);
        try {
            DB::beginTransaction();
            $client = Client::findOrFail($request->client_id);
            $subClient = SubClient::where('client_id', $client->id)->first();

            $findUser = User::query()->where('client_id', $client->id)->first();
            if (!$findUser) {
                User::query()->create([
                    'client_id' => $client->id,
                    'first_name' => $client->name,
                    'last_name' => $client->name,
                    'username' => $client->code,
                    'password' => bcrypt($request->password),
                    'verified_at' => Carbon::now(),
                ]);
            }
            if ($subClient) {
                $subClient->update([
                    'fullName' => $request->name,
                    'password' => bcrypt($request->password),
                    'status' => $request->status,
                ]);
            } else {
                SubClient::create([
                    'client_id' => $client->id,
                    'fullName' => $request->name,
                    'username' => $client->code,
                    'password' => bcrypt($request->password),
                    'status' => $request->status,
                ]);
            }
            DB::commit();
            toastr()->success('ساب کلاینت با موفقیت ایجاد شد.');
            $clients = Client::orderBy('code', 'desc')->paginate(20);
            return view('back.nila.clients.getClients', compact('clients'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return back()->with('error', 'خطا در ثبت اطلاعات: ' . $exception->getMessage());
        }
    }

    public function deleteSubClient($id)
    {
        SubClient::query()->where('id', $id)->delete();
        toastr()->success('ویزیتور با موفقیت حذف شد.');
        $subClients = SubClient::orderBy('id', 'desc')->paginate(20);
        return view('back.nila.clients.subClients', compact('subClients'));

    }

    public function subClients()
    {
        $subClients = SubClient::orderBy('id', 'desc')->paginate(20);
        return view('back.nila.clients.subClients', compact('subClients'));
    }

}
