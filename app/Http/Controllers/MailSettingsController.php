<?php

namespace App\Http\Controllers;

use App\Jobs\SendMailTest;
use App\Models\MailSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class MailSettingsController extends Controller
{
    public static function available(Request $request): bool
    {
        return $request->user()?->is_owner === true;
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless(self::available($request), 403);

        $validator = Validator::make($request->all(), [
            'provider' => ['required', Rule::in(['resend'])],
            'api_key' => ['required', 'string', 'max:255'],
            'from_address' => ['required', 'email', 'max:255'],
        ], ['api_key.required' => 'mail.invalid_key', 'api_key.string' => 'mail.invalid_key', 'api_key.max' => 'mail.invalid_key', 'from_address.required' => 'mail.invalid_from', 'from_address.email' => 'mail.invalid_from', 'from_address.max' => 'mail.invalid_from']);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'mailSettings');
        }

        MailSetting::query()->updateOrCreate(['id' => 1], $validator->validated());

        return back()->with('success', 'mail.saved');
    }

    public function test(Request $request): RedirectResponse
    {
        abort_unless(self::available($request), 403);

        $validator = Validator::make($request->all(), ['recipient' => ['required', 'email', 'max:255'], 'locale' => ['nullable', Rule::in(['pt-BR', 'en', 'es', 'fr', 'de'])]], ['recipient.required' => 'mail.invalid_recipient', 'recipient.email' => 'mail.invalid_recipient', 'recipient.max' => 'mail.invalid_recipient']);
        if ($validator->fails()) {
            return back()->withErrors($validator, 'mailTest');
        }
        if (! MailSetting::query()->exists()) {
            return back()->withErrors(['recipient' => 'mail.setup_required'], 'mailTest');
        }
        $resultId = (string) Str::uuid();
        try {
            DB::transaction(function () use ($validator, $resultId): void {
                DB::table('mail_test_results')->insert(['id' => $resultId, 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()]);
                SendMailTest::dispatch($validator->validated()['recipient'], $resultId, $validator->validated()['locale'] ?? 'en')->onConnection('database');
            });
        } catch (Throwable) {
            return back()->withErrors(['recipient' => 'mail.queue_error'], 'mailTest');
        }

        return back()->with('mail_test_id', $resultId);
    }

    public function status(Request $request, string $id): JsonResponse
    {
        abort_unless(self::available($request), 403);
        $status = DB::table('mail_test_results')->where('id', $id)->value('status');
        abort_if($status === null, 404);

        return response()->json(['status' => $status]);
    }
}
