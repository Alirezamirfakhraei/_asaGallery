<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{
    /**
     * نمایش فرم ورود مشتری
     */
    public function showLoginForm()
    {
        return view('back.nila.clients.auth.login');
    }

    /**
     * پردازش ورود مشتری
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'username.required' => 'لطفاً کد مشتری را وارد کنید.',
            'password.required' => 'لطفاً رمز عبور را وارد کنید.',
            'password.min' => 'رمز عبور باید حداقل 6 کاراکتر باشد.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->only('username'));
        }

        $credentials = $request->only('username', 'password');

        try {
            if (Auth::guard('sub_client')->attempt($credentials, $request->filled('remember'))) {
                $request->session()->regenerate();

                $subClient = Auth::guard('sub_client')->user();

                if (!$subClient) {
                    toastr()->error('خطا در دریافت اطلاعات کاربر.');
                    return redirect()->back();
                }

                // تلاش برای یافتن کاربر اصلی مربوط به sub_client
                $user = User::where('client_id', $subClient->client_id)->first();

                if ($user) {
                    Auth::login($user); // استفاده از گارد پیش‌فرض (web)
                }

                return redirect()->intended('/');
            }

            toastr()->error('نام کاربری یا رمز عبور نادرست می‌باشد.');
            return redirect()->back();

        } catch (\Throwable $e) {
            Log::error('خطا در ورود مشتری: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            toastr()->error('خطایی در ورود رخ داده است. لطفاً مجدداً تلاش کنید.');
            return redirect()->back();
        }
    }

    /**
     * خروج مشتری
     */
    public function logout(Request $request)
    {
        try {
            Auth::guard('sub_client')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        } catch (\Throwable $e) {
            Log::error('خطا در خروج مشتری: ' . $e->getMessage());
        }
        return redirect('/');
    }
}
