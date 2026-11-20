<?php

namespace App\Http\Controllers;

use App\Models\PicMachine;
use App\Models\SapHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    /**
     * Tampilkan halaman utama Reliability & Breakdown Analytics.
     */
    public function index(Request $request): View
    {
        // 1. Ambil seluruh PIC yang memiliki penugasan mesin
        $pics = User::whereHas('picMachines')
            ->with('picMachines')
            ->orderBy('name')
            ->get();

        $currentUser = auth()->user();

        // 2. Baca parameter filter dari request
        $selectedPic = $request->input('pic', 'all');
        $selectedMachine = $request->input('machine', 'all');
        $selectedPeriod = $request->input('period', '30d');
        $inputMonth = $request->input('month');
        $inputStartDate = $request->input('start_date');
        $inputEndDate = $request->input('end_date');

        // 3. Tentukan default bulan dari data historis SAP terbaru (sinkron 100% dengan PicReportController)
        $defaultMonth = $this->resolveDefaultMonth();

        // 4. Tentukan mesin yang relevan berdasarkan PIC terpilih
        $picMachineCodes = [];
        if ($selectedPic !== 'all' && ! empty($selectedPic)) {
            $picMachineCodes = PicMachine::where('user_id', $selectedPic)
                ->pluck('functional_location')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            // Daftar mesin dropdown hanya mesin milik PIC terpilih
            $availableMachines = collect($picMachineCodes)->sort()->values();

            // Jika mesin yang sebelumnya dipilih tidak ada dalam daftar mesin PIC ini, reset ke 'all'
            if ($selectedMachine !== 'all' && ! in_array($selectedMachine, $picMachineCodes, true)) {
                $selectedMachine = 'all';
            }
        } else {
            // Jika "Semua PIC", ambil seluruh functional_location unik dari sap_histories
            $availableMachines = SapHistory::whereNotNull('functional_location')
                ->where('functional_location', '!=', '')
                ->where('functional_location', '!=', 'Total')
                ->distinct()
                ->orderBy('functional_location')
                ->pluck('functional_location');
        }

        // 5. Hitung rentang tanggal (Start & End Date, Selected Month) dengan sinkronisasi ke PicReportController
        [$startDate, $endDate, $activePeriod, $selectedMonth] = $this->resolveDateRange(
            $selectedPeriod,
            $inputMonth,
            $inputStartDate,
            $inputEndDate,
            $defaultMonth
        );

        // 6. Tentukan daftar mesin dalam cakupan (scope) untuk kalkulasi Available Hours
        if ($selectedMachine !== 'all' && ! empty($selectedMachine)) {
            $machinesInScope = collect([$selectedMachine]);
        } elseif ($selectedPic !== 'all' && ! empty($picMachineCodes)) {
            $machinesInScope = collect($picMachineCodes);
        } else {
            $machinesInScope = $availableMachines;
        }

        $machineCount = max(1, $machinesInScope->count());
        $daysCount = max(1, (int) $startDate->diffInDays($endDate) + 1);
        $totalAvailableHours = $daysCount * 24 * $machineCount;

        // 7. Base Query untuk periode & cakupan mesin (mengabaikan baris Total ringkasan SAP)
        $baseQuery = SapHistory::query()
            ->where('functional_location', '!=', 'Total');

        // Terapkan filter periode tanggal / bulan sesuai metode PicReportController
        $this->applyPeriodFilter($baseQuery, $activePeriod, $startDate, $endDate, $selectedMonth);

        // Terapkan filter mesin / PIC
        if ($selectedMachine !== 'all' && ! empty($selectedMachine)) {
            $baseQuery->where('functional_location', $selectedMachine);
        } elseif ($selectedPic !== 'all' && ! empty($picMachineCodes)) {
            $baseQuery->whereIn('functional_location', $picMachineCodes);
        }

        // 8. Query Work Order / Kejadian Breakdown Utama (HANYA baris notfis = 1)
        // Sesuai aturan struktur SAP: Baris Header WO Utama memiliki notfis = 1 (memuat bm_hrs & response_hrs).
        // Baris konfirmasi kerja teknisi memiliki notfis IS NULL (memuat actual_hrs & confirmation_text).
        $hasNotfis = (clone $baseQuery)->where('notfis', 1)->exists();
        $woQuery = $hasNotfis ? (clone $baseQuery)->where('notfis', 1) : (clone $baseQuery);

        // Total Breakdown (Frekuensi Kejadian WO): Hitung unik per maint_order dari baris notfis = 1
        $totalBreakdown = (int) ((clone $woQuery)
            ->select(DB::raw('COUNT(DISTINCT COALESCE(NULLIF(maint_order, ""), CAST(id AS CHAR))) as agg'))
            ->value('agg') ?? 0);

        // Total Downtime (Jam): Ambil dari baris notfis = 1
        $totalDowntime = (float) ((clone $woQuery)->sum('bm_hrs') ?? 0);

        // Total Jam Kerja Teknisi (actual_hrs): Diambil dari seluruh baris (konfirmasi teknisi notfis IS NULL)
        $totalActualHours = (float) ((clone $baseQuery)->sum('actual_hrs') ?? 0);

        // Average Response Time: Diambil dari baris notfis = 1
        $avgResponseTime = (float) round((clone $woQuery)->whereNotNull('response_hrs')->avg('response_hrs') ?? 0, 2);

        // MTTR (Mean Time To Repair): Total Downtime / Total Breakdown (pembagi notfis = 1)
        $mttr = $totalBreakdown > 0 ? round($totalDowntime / $totalBreakdown, 2) : 0.0;

        // MTBF (Mean Time Between Failures): (Total Available Hours - Total Downtime) / Total Breakdown (pembagi notfis = 1)
        $operatingHours = max(0.0, $totalAvailableHours - $totalDowntime);
        $mtbf = $totalBreakdown > 0 ? round($operatingHours / $totalBreakdown, 1) : round($totalAvailableHours, 1);

        // Machine Availability (%): ((Total Available Hours - Total Downtime) / Total Available Hours) * 100
        $availability = $totalAvailableHours > 0
            ? round(max(0.0, min(100.0, ($operatingHours / $totalAvailableHours) * 100)), 2)
            : 100.0;

        $kpis = [
            'total_breakdown' => $totalBreakdown,
            'total_downtime' => round($totalDowntime, 2),
            'total_actual_hours' => round($totalActualHours, 2),
            'mtbf' => $mtbf,
            'mttr' => $mttr,
            'availability' => $availability,
            'avg_response_time' => $avgResponseTime,
            'machine_count' => $machineCount,
            'days_count' => $daysCount,
            'available_hours' => $totalAvailableHours,
        ];

        // 9. Grafik 1: Top 10 Mesin Breakdown Terlama (Jam) - Filter notfis = 1
        $topDowntimeMachines = (clone $woQuery)
            ->select('functional_location', DB::raw('ROUND(SUM(CAST(bm_hrs AS DECIMAL(10,2))), 2) as total_downtime'))
            ->whereNotNull('functional_location')
            ->where('functional_location', '!=', '')
            ->groupBy('functional_location')
            ->orderByDesc('total_downtime')
            ->limit(10)
            ->get();

        $chartTopDowntime = [
            'labels' => $topDowntimeMachines->pluck('functional_location')->toArray(),
            'data' => $topDowntimeMachines->pluck('total_downtime')->map(fn ($val) => round((float) $val, 2))->toArray(),
        ];

        // 10. Grafik 2: Top 10 Mesin Paling Sering Rusak (Frekuensi) - Filter notfis = 1 & DISTINCT maint_order
        $topFrequencyMachines = (clone $woQuery)
            ->select('functional_location', DB::raw('COUNT(DISTINCT COALESCE(NULLIF(maint_order, ""), CAST(id AS CHAR))) as total_count'))
            ->whereNotNull('functional_location')
            ->where('functional_location', '!=', '')
            ->groupBy('functional_location')
            ->orderByDesc('total_count')
            ->limit(10)
            ->get();

        $chartTopFrequency = [
            'labels' => $topFrequencyMachines->pluck('functional_location')->toArray(),
            'data' => $topFrequencyMachines->pluck('total_count')->map(fn ($val) => (int) $val)->toArray(),
        ];

        // 11. Grafik 3: Tren Bulanan Downtime & MTTR (6 Bulan Terakhir) - Filter notfis = 1
        $chartTrend = $this->calculateSixMonthTrend(
            $endDate,
            $selectedMachine,
            $selectedPic,
            $picMachineCodes
        );

        // 12. Grafik 4: Top Komponen / Object Part Bermasalah - Filter notfis = 1 & DISTINCT maint_order
        $topComponents = (clone $woQuery)
            ->select(
                'object_part',
                DB::raw('COUNT(DISTINCT COALESCE(NULLIF(maint_order, ""), CAST(id AS CHAR))) as total_count'),
                DB::raw('ROUND(SUM(CAST(bm_hrs AS DECIMAL(10,2))), 2) as total_downtime')
            )
            ->whereNotNull('object_part')
            ->where('object_part', '!=', '')
            ->where('object_part', '!=', 'Total')
            ->groupBy('object_part')
            ->orderByDesc('total_count')
            ->limit(10)
            ->get();

        $chartTopComponents = [
            'labels' => $topComponents->pluck('object_part')->toArray(),
            'frequencies' => $topComponents->pluck('total_count')->map(fn ($val) => (int) $val)->toArray(),
            'downtimes' => $topComponents->pluck('total_downtime')->map(fn ($val) => round((float) $val, 2))->toArray(),
        ];

        // 13. Tabel Ringkasan Detail Performa Mesin - Filter notfis = 1
        $machineTable = $this->calculateMachineSummaryTable(
            $woQuery,
            $machinesInScope,
            $daysCount
        );

        return view('analytics.index', compact(
            'pics',
            'currentUser',
            'selectedPic',
            'selectedMachine',
            'availableMachines',
            'activePeriod',
            'selectedMonth',
            'defaultMonth',
            'startDate',
            'endDate',
            'kpis',
            'chartTopDowntime',
            'chartTopFrequency',
            'chartTrend',
            'chartTopComponents',
            'machineTable'
        ));
    }

    /**
     * Tentukan default bulan dari data historis SAP terbaru (sama persis dengan PicReportController).
     */
    private function resolveDefaultMonth(): string
    {
        $latestDate = SapHistory::whereNotNull('date')
            ->where('date', '<=', now()->toDateString())
            ->max('date');

        if ($latestDate && preg_match('/^(\d{4}-\d{2})/', $latestDate, $m)) {
            return $m[1];
        }

        $latestMonth = SapHistory::whereNotNull('month')
            ->where('month', '!=', 'Total')
            ->max('month');

        if ($latestMonth && preg_match('/^(\d{1,2})\.(\d{4})$/', $latestMonth, $mm)) {
            return sprintf('%04d-%02d', $mm[2], $mm[1]);
        }

        return date('Y-m');
    }

    /**
     * Hitung rentang tanggal berdasarkan filter period, month, atau custom date range.
     *
     * @return array{0: Carbon, 1: Carbon, 2: string, 3: string}
     */
    private function resolveDateRange(
        string $period,
        ?string $inputMonth,
        ?string $inputStart,
        ?string $inputEnd,
        string $defaultMonth
    ): array {
        // Tentukan anchor date
        $maxDate = SapHistory::whereNotNull('date')->max('date');
        $hasRecentData = SapHistory::whereDate('date', '>=', now()->subDays(30)->toDateString())->exists();

        if ($hasRecentData || empty($maxDate)) {
            $anchor = now();
        } else {
            $anchor = Carbon::parse($maxDate);
        }

        // 1. Filter Custom Date Range
        if ($period === 'custom' && ! empty($inputStart) && ! empty($inputEnd)) {
            $start = Carbon::parse($inputStart)->startOfDay();
            $end = Carbon::parse($inputEnd)->endOfDay();
            if ($start->greaterThan($end)) {
                $temp = $start;
                $start = $end;
                $end = $temp;
            }

            return [$start, $end, 'custom', $start->format('Y-m')];
        }

        // 2. Filter Periode Bulan (Sama dengan PicReportController)
        if ($period === 'month' || (empty($period) && ! empty($inputMonth))) {
            $targetMonth = ! empty($inputMonth) ? trim($inputMonth) : $defaultMonth;
            if (! preg_match('/^\d{4}-\d{2}$/', $targetMonth)) {
                $targetMonth = $defaultMonth;
            }

            $start = Carbon::parse($targetMonth.'-01')->startOfMonth();
            $end = Carbon::parse($targetMonth.'-01')->endOfMonth();

            return [$start, $end, 'month', $targetMonth];
        }

        // 3. Quick Periods lainnya
        switch ($period) {
            case '7d':
                $end = $anchor->copy()->endOfDay();
                $start = $anchor->copy()->subDays(6)->startOfDay();
                $activePeriod = '7d';
                break;

            case 'year':
                $end = $anchor->copy()->endOfYear();
                $start = $anchor->copy()->startOfYear();
                $activePeriod = 'year';
                break;

            case '30d':
            default:
                $end = $anchor->copy()->endOfDay();
                $start = $anchor->copy()->subDays(29)->startOfDay();
                $activePeriod = '30d';
                break;
        }

        return [$start, $end, $activePeriod, $start->format('Y-m')];
    }

    /**
     * Terapkan filter periode waktu pada query menggunakan logika sinkron dengan PicReportController.
     */
    private function applyPeriodFilter(
        Builder $query,
        string $period,
        Carbon $startDate,
        Carbon $endDate,
        string $selectedMonth
    ): void {
        if ($period === 'month') {
            $dashMonth = $selectedMonth;
            $sapMonths = $this->formatToSapMonths($selectedMonth);

            $query->where(function ($q) use ($dashMonth, $sapMonths) {
                $q->where('date', 'like', "{$dashMonth}%")
                    ->orWhereIn('month', $sapMonths);
            });
        } elseif ($period === 'year') {
            $year = $startDate->format('Y');

            $query->where(function ($q) use ($startDate, $endDate, $year) {
                $q->where(function ($sub) use ($startDate, $endDate) {
                    $sub->whereDate('date', '>=', $startDate->toDateString())
                        ->whereDate('date', '<=', $endDate->toDateString());
                })->orWhere('month', 'like', "%.{$year}");
            });
        } else {
            $query->whereDate('date', '>=', $startDate->toDateString())
                ->whereDate('date', '<=', $endDate->toDateString());
        }
    }

    /**
     * Hitung tren performa 6 bulan terakhir (Downtime vs MTTR bulanan) dengan filter notfis = 1.
     *
     * @param  array<int, string>  $picMachineCodes
     * @return array{labels: array<int, string>, downtimes: array<int, float>, mttr: array<int, float>}
     */
    private function calculateSixMonthTrend(
        Carbon $referenceDate,
        string $selectedMachine,
        string $selectedPic,
        array $picMachineCodes
    ): array {
        $labels = [];
        $downtimes = [];
        $mttrValues = [];

        // Ambil 6 bulan berurutan hingga referenceDate
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = $referenceDate->copy()->subMonths($i);
            $mDash = $monthDate->format('Y-m');
            $mSap = $this->formatToSapMonths($mDash);
            $monthLabel = $monthDate->translatedFormat('M Y');

            $mQuery = SapHistory::query()
                ->where('notfis', 1)
                ->where('functional_location', '!=', 'Total')
                ->where(function ($q) use ($mDash, $mSap) {
                    $q->where('date', 'like', "{$mDash}%")
                        ->orWhereIn('month', $mSap);
                });

            if ($selectedMachine !== 'all' && ! empty($selectedMachine)) {
                $mQuery->where('functional_location', $selectedMachine);
            } elseif ($selectedPic !== 'all' && ! empty($picMachineCodes)) {
                $mQuery->whereIn('functional_location', $picMachineCodes);
            }

            $mDowntime = (float) ($mQuery->sum('bm_hrs') ?? 0);
            $mCount = (int) ($mQuery->select(DB::raw('COUNT(DISTINCT COALESCE(NULLIF(maint_order, ""), CAST(id AS CHAR))) as agg'))->value('agg') ?? 0);
            $mMttr = $mCount > 0 ? round($mDowntime / $mCount, 2) : 0.0;

            $labels[] = $monthLabel;
            $downtimes[] = round($mDowntime, 2);
            $mttrValues[] = $mMttr;
        }

        return [
            'labels' => $labels,
            'downtimes' => $downtimes,
            'mttr' => $mttrValues,
        ];
    }

    /**
     * Hitung tabel ringkasan performa per mesin menggunakan query notfis = 1.
     *
     * @param  Collection<int, string>  $machinesInScope
     * @return Collection<int, array<string, mixed>>
     */
    private function calculateMachineSummaryTable(Builder $woQuery, Collection $machinesInScope, int $daysCount): Collection
    {
        $stats = (clone $woQuery)
            ->select(
                'functional_location',
                DB::raw('COUNT(DISTINCT COALESCE(NULLIF(maint_order, ""), CAST(id AS CHAR))) as total_breakdown'),
                DB::raw('ROUND(SUM(CAST(bm_hrs AS DECIMAL(10,2))), 2) as total_downtime'),
                DB::raw('AVG(response_hrs) as avg_response')
            )
            ->whereNotNull('functional_location')
            ->where('functional_location', '!=', '')
            ->groupBy('functional_location')
            ->get()
            ->keyBy('functional_location');

        $singleMachineAvailableHours = $daysCount * 24;
        $result = collect();

        // 1. Masukkan mesin-mesin yang memiliki breakdown (notfis = 1)
        foreach ($stats as $loc => $row) {
            $breakdown = (int) $row->total_breakdown;
            $downtime = (float) ($row->total_downtime ?? 0);
            $operating = max(0.0, $singleMachineAvailableHours - $downtime);
            $mttr = $breakdown > 0 ? round($downtime / $breakdown, 2) : 0.0;
            $mtbf = $breakdown > 0 ? round($operating / $breakdown, 1) : $singleMachineAvailableHours;
            $avail = $singleMachineAvailableHours > 0
                ? round(max(0.0, min(100.0, ($operating / $singleMachineAvailableHours) * 100)), 2)
                : 100.0;
            $avgResp = round((float) ($row->avg_response ?? 0), 2);

            $statusBadge = $this->resolveHealthStatus($avail);

            $result->push([
                'functional_location' => $loc,
                'breakdown' => $breakdown,
                'downtime' => round($downtime, 2),
                'mttr' => $mttr,
                'mtbf' => $mtbf,
                'availability' => $avail,
                'avg_response' => $avgResp,
                'status' => $statusBadge['label'],
                'status_color' => $statusBadge['color'],
            ]);
        }

        // 2. Tambahkan mesin dalam scope yang 0 breakdown jika scope terbatas (misal PIC terpilih)
        // agar user bisa melihat mesin yang kondisinya prima (100% availability)
        if ($machinesInScope->count() <= 50) {
            foreach ($machinesInScope as $loc) {
                if (! $stats->has($loc) && ! empty($loc)) {
                    $result->push([
                        'functional_location' => $loc,
                        'breakdown' => 0,
                        'downtime' => 0.0,
                        'mttr' => 0.0,
                        'mtbf' => $singleMachineAvailableHours,
                        'availability' => 100.0,
                        'avg_response' => 0.0,
                        'status' => 'Optimal',
                        'status_color' => 'green',
                    ]);
                }
            }
        }

        // Urutkan berdasarkan Downtime terbanyak, lalu Frekuensi breakdown terbanyak
        return $result->sortByDesc('downtime')->values();
    }

    /**
     * Ubah format input HTML bulan 'YYYY-MM' menjadi format SAP 'MM.YYYY' dan 'M.YYYY' (sama dengan PicReportController).
     *
     * @return array<int, string>
     */
    private function formatToSapMonths(string $yearMonth): array
    {
        if (preg_match('/^(\d{4})-(\d{2})$/', $yearMonth, $m)) {
            $y = $m[1];
            $mPadded = $m[2];
            $mUnpadded = (string) (int) $m[2];

            if ($mPadded !== $mUnpadded) {
                return ["{$mPadded}.{$y}", "{$mUnpadded}.{$y}"];
            }

            return ["{$mPadded}.{$y}"];
        }

        return [$yearMonth];
    }

    /**
     * Tentukan label & kelas badge status kehandalan berdasarkan Machine Availability.
     *
     * @return array{label: string, color: string}
     */
    private function resolveHealthStatus(float $availability): array
    {
        if ($availability >= 95.0) {
            return [
                'label' => 'Optimal',
                'color' => 'green',
            ];
        }

        if ($availability >= 85.0) {
            return [
                'label' => 'Perlu Perhatian',
                'color' => 'amber',
            ];
        }

        return [
            'label' => 'Kritis',
            'color' => 'red',
        ];
    }
}
