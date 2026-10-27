<?php

namespace App\Jobs;

use App\Services\MailSettings;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class SendMailTest implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(public string $recipient, public string $resultId, public string $locale = 'en') {}

    public function handle(MailSettings $settings): void
    {
        try {
            $settings->sendTest($this->recipient, $this->locale ?? 'en');
            if (isset($this->resultId)) {
                DB::table('mail_test_results')->where('id', $this->resultId)->update(['status' => 'sent', 'updated_at' => now()]);
            }
        } catch (Throwable) {
            throw new RuntimeException('Falha ao enviar o e-mail de teste. Verifique a configuração do serviço.');
        }
    }

    public function failed(?Throwable $exception): void
    {
        if (isset($this->resultId)) {
            DB::table('mail_test_results')->where('id', $this->resultId)->update(['status' => 'failed', 'updated_at' => now()]);
        }
    }
}
