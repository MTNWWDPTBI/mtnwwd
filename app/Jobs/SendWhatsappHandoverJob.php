<?php

namespace App\Jobs;

use App\Models\Handover;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWhatsappHandoverJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @var array<int, int>
     */
    public array $backoff = [5, 15, 30];

    public int $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Handover $handover
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $handoverDate = $this->handover->handover_date
            ? Carbon::parse($this->handover->handover_date)->format('d M Y')
            : date('d M Y');

        $shift = $this->handover->shift
            ? (str_contains(strtolower((string) $this->handover->shift), 'shift')
                ? $this->handover->shift
                : 'Shift '.$this->handover->shift)
            : '-';

        $team = $this->handover->team
            ? (str_contains(strtolower((string) $this->handover->team), 'tim')
                ? $this->handover->team
                : 'Tim '.$this->handover->team)
            : '-';

        $reportedBy = $this->handover->reported_by ?: 'Teknisi';
        $machineName = $this->handover->machine_name ?: '-';
        $problemDescription = $this->handover->problem_description ?: '-';
        $lototoStatus = $this->handover->lototo_status ?: 'Belum';

        $message = "🚨 *LOGBOOK & PROBLEM SHIFT MAINTENANCE (WWD)*\n"
            ."----------------------------------------\n"
            ."📅 *Tanggal:* {$handoverDate}\n"
            ."⏱️ *Shift:* {$shift}\n"
            ."👥 *Grup Tim:* {$team}\n"
            ."👤 *Pelapor:* {$reportedBy}\n\n"
            ."⚙️ *MESIN / FASILITAS:*\n"
            ."*{$machineName}*\n\n"
            ."📝 *DETAIL PROBLEM / TEMUAN:*\n"
            ."{$problemDescription}\n\n"
            ."🔒 *LOTOTO CHECKIN/CHECKOUT:*\n"
            ."*{$lototoStatus}*\n"
            ."----------------------------------------\n"
            .'📌 *Status:* OPEN (Mohon shift penerima koordinasi di area)';

        $url = config('services.wa_bot.url') ?: env('WA_BOT_URL');
        $secretToken = config('services.wa_bot.secret') ?: env('WA_BOT_SECRET');
        $groupId = config('services.wa_bot.group_id') ?: env('WA_GROUP_ID');

        if (! empty($url)) {
            try {
                Http::withOptions([
                    'force_ip_resolve' => 'v4',
                    'verify' => false,
                ])
                    ->connectTimeout(3)
                    ->timeout(5)
                    ->post($url, [
                        'secret_token' => $secretToken,
                        'group_id' => $groupId,
                        'message' => $message,
                    ]);
            } catch (\Throwable $e) {
                Log::warning("Gagal mengirim WhatsApp Handover via bot: {$e->getMessage()}", [
                    'handover_id' => $this->handover->id,
                ]);
            }
        }
    }
}
