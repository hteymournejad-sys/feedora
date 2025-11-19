<?php

namespace App\Http\Controllers;

use App\Models\CreditSettings;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function guide()
    {
        $creditSettings = CreditSettings::first();
        $user = Auth::user();
        $profileComplete = $user && !empty($user->first_name) && !empty($user->last_name) && !empty($user->mobile) && !empty($user->national_code) && !empty($user->company_size) && !empty($user->company_type);
        return view('payment-guide', compact('creditSettings', 'profileComplete'));
    }

    public function start(Request $request)
    {
        $user = Auth::user();
        if ($user->is_admin == 1) {
            return redirect()->route('admin.profile')->with('error', 'ادمین نیازی به پرداخت ندارد.');
        }

        $creditSettings = CreditSettings::firstOrFail();
        $amount = $creditSettings->amount;

        $description = "شارژ اعتبار کاربر: {$user->email}";
        $email = $user->email;
        $mobile = $user->mobile;
        $merchantId = 'b56322fd-9f64-495e-8d86-6a5bca139d3f';

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://payment.zarinpal.com/pg/v4/payment/request.json',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode([
                'merchant_id' => $merchantId,
                'amount' => $amount,
                'callback_url' => route('payment.callback'),
                'description' => $description,
                'metadata' => ['mobile' => $mobile, 'email' => $email],
                'currency' => 'IRT',
            ]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
        ]);

        $response = json_decode(curl_exec($curl), true);
        $error = curl_error($curl);
        curl_close($curl);

        Log::info('Zarinpal Request Response: ', ['response' => $response, 'error' => $error]);

        if (isset($response['data']) && $response['data']['code'] == 100) {
            $authority = $response['data']['authority'];
            return redirect()->away("https://www.zarinpal.com/pg/StartPay/{$authority}");
        }

        return redirect()->back()->with('error', 'خطا در ایجاد تراکنش: '. ($response['errors'][0]['message'] ?? $error ?? 'مشکل ناشناخته'));
    }

    public function callback(Request $request)
    {
        $authority = $request->input('Authority');
        $status = $request->input('Status');

        $user = Auth::user();
        $creditSettings = CreditSettings::firstOrFail();

        if ($status == 'OK') {
            $merchantId = 'b56322fd-9f64-495e-8d86-6a5bca139d3f';
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://payment.zarinpal.com/pg/v4/payment/verify.json',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode([
                    'merchant_id' => $merchantId,
                    'amount' => $creditSettings->amount,
                    'authority' => $authority,
                ]),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
            ]);

            $response = json_decode(curl_exec($curl), true);
            $error = curl_error($curl);
            curl_close($curl);

            Log::info('Zarinpal Verify Response: ', ['response' => $response, 'error' => $error]);

            if (isset($response['data']) && $response['data']['code'] == 100) {
                $refId = $response['data']['ref_id'];

                if (Payment::where('payment_id', $refId)->exists()) {
                    return redirect()->route('profile')->with('error', 'این تراکنش قبلاً ثبت شده است.');
                }

                $user->remaining_evaluations += $creditSettings->evaluations;
                $user->remaining_days += $creditSettings->days;
                $user->save();

                // تولید شماره سریال برای صورتحساب
                $lastPayment = Payment::orderBy('invoice_number', 'desc')->first();
                $nextInvoiceNumber = $lastPayment ? ((int)$lastPayment->invoice_number + 1) : 10020001;

                Payment::create([
                    'user_id' => $user->id,
                    'invoice_number' => (string)$nextInvoiceNumber,
                    'amount' => $creditSettings->amount,
                    'status' => 'completed',
                    'evaluation_count' => $creditSettings->evaluations,
                    'payment_date' => now(),
                    'start_date' => now(),
                    'duration_days' => $creditSettings->days,
                    'payment_id' => $refId,
                    'payment_step' => 'completed',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return view('payment-confirmation', ['status' => 'success', 'refId' => $refId])
                    ->with('success', 'پرداخت با موفقیت انجام شد.')
                    ->with('redirect', route('profile'));
            } elseif (isset($response['data']) && $response['data']['code'] == 101) {
                return redirect()->route('profile')->with('error', 'تراکنش قبلاً تأیید شده است.');
            }
        }

        return view('payment-confirmation', ['status' => 'failed'])
            ->with('error', 'پرداخت ناموفق بود. لطفاً دوباره تلاش کنید.');
    }

    public function inquiry($authority)
    {
        $merchantId = 'b56322fd-9f64-495e-8d86-6a5bca139d3f';
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://payment.zarinpal.com/pg/v4/payment/inquiry.json',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode(['merchant_id' => $merchantId, 'authority' => $authority]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
        ]);

        $response = json_decode(curl_exec($curl), true);
        $error = curl_error($curl);
        curl_close($curl);

        Log::info('Zarinpal Inquiry Response: ', ['response' => $response, 'error' => $error]);

        if (isset($response['data']) && $response['data']['code'] == 100) {
            return response()->json(['status' => $response['data']['status'], 'message' => $response['data']['message']]);
        }

        return response()->json(['error' => $response['errors'][0]['message'] ?? $error ?? 'خطا در استعلام'], 400);
    }

    public function updateForPayment(Request $request)
    {
        $user = Auth::user();

        Log::info('Raw Request Data for Payment:', $request->all());

        $validated = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:11',
            'national_code' => 'nullable|string|max:10',
            'company_alias' => 'nullable|string|max:255',
            'company_size' => 'nullable|in:بزرگ,متوسط,کوچک',
            'company_type' => 'nullable|array',
            'company_type.*' => 'in:تولیدی,خدماتی,پخش,سرمایه‌گذاری,پروژه و تحقیقاتی,هلدینگ,پروژه‌ای,دانشگاهی,تحقیقاتی,بیمارستانی,بانکی',
            'holding_affiliation_code' => 'nullable|string|max:255',
        ]);

        $isHolding = !empty($validated['company_type']) && in_array('هلدینگ', $validated['company_type']);
        $inputCode = $request->input('holding_affiliation_code');

        Log::info('Update Profile Data for Payment:', [
            'user_id' => $user->id,
            'is_holding' => $isHolding,
            'input_code' => $inputCode,
            'current_code' => $user->holding_affiliation_code,
            'validated_data' => $validated,
        ]);

        if ($isHolding) {
            if (empty($user->holding_affiliation_code)) {
                $validated['holding_affiliation_code'] = $this->generateUniqueHoldingCode();
                Log::info('Generated new holding code for payment:', [
                    'user_id' => $user->id,
                    'new_code' => $validated['holding_affiliation_code'],
                ]);
            } else {
                $validated['holding_affiliation_code'] = $user->holding_affiliation_code;
                Log::info('Keeping existing holding code for payment:', [
                    'user_id' => $user->id,
                    'existing_code' => $user->holding_affiliation_code,
                ]);
            }
        } else {
            if ($inputCode !== null && $inputCode !== '') {
                $validated['holding_affiliation_code'] = $inputCode;
                Log::info('Set holding code from input for payment:', [
                    'user_id' => $user->id,
                    'new_code' => $inputCode,
                ]);
            } else {
                $validated['holding_affiliation_code'] = null;
                Log::info('Code set to null (empty input) for payment:', [
                    'user_id' => $user->id,
                ]);
            }
        }

        $currentCompanyTypes = is_array($user->company_type) ? $user->company_type : (json_decode($user->company_type ?? '[]', true) ?: []);
        $newCompanyTypes = $request->input('company_type', []);
        Log::info('Company Types for Payment:', [
            'current' => $currentCompanyTypes,
            'new' => $newCompanyTypes,
        ]);

        $companyTypes = $newCompanyTypes;
        $companyTypes = array_values($companyTypes);

        $validated['company_type'] = $companyTypes;
        Log::info('Final Company Types Before Update for Payment:', [
            'company_types' => $companyTypes,
        ]);

        $user->update($validated);

        Log::info('Updated User Data for Payment:', [
            'user_id' => $user->id,
            'updated_data' => $user->toArray(),
        ]);

        if ($this->checkProfileCompletion($user)) {
            session()->flash('success', 'اطلاعات پروفایل برای پرداخت با موفقیت به‌روزرسانی شد و پروفایل شما کامل است.');
        } else {
            session()->flash('notification', 'اطلاعات پروفایل برای پرداخت به‌روزرسانی شد. لطفاً تمام فیلدها (نام، نام خانوادگی، شماره موبایل، کد ملی) را تکمیل کنید.');
        }

        return redirect()->route('payment-guide')->with('success', 'اطلاعات با موفقیت ذخیره شد. حالا می‌توانید پرداخت را ادامه دهید.');
    }

    protected function generateUniqueHoldingCode()
    {
        do {
            $code = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (User::where('holding_affiliation_code', $code)->exists());

        return $code;
    }

    protected function checkProfileCompletion(User $user)
    {
        return $user && !empty($user->first_name) && !empty($user->last_name) && !empty($user->mobile) && !empty($user->national_code) && !empty($user->company_size) && !empty($user->company_type);
    }
}