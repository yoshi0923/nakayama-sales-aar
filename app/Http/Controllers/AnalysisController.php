<?php

namespace App\Http\Controllers;

use App\Models\AarRecord;
use App\Models\Department;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AnalysisController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();

        // 年度リスト
        $years = AarRecord::selectRaw('YEAR(activity_date) as y, MONTH(activity_date) as m')
            ->where('is_draft', false)
            ->where('result', 'success')
            ->get()
            ->map(fn($r) => $r->m >= 3 ? $r->y : $r->y - 1)
            ->unique()
            ->sort()
            ->values();

        $selectedYear = $request->integer('year', $years->last() ?? now()->year);

        $allReal = AarRecord::with(['user.department'])
            ->where('is_draft', false)
            ->get();

        // KPIサマリー
        $total   = $allReal->count();
        $success = $allReal->where('result', 'success')->count();
        $members = $allReal->pluck('user_id')->unique()->count();
        $totalAmt = Opportunity::where('status', 'won')->sum('confirmed_amount');

        // プロセス別成功率
        $processStat = $this->calcProcessStat($allReal);

        // 年度別受注金額推移
        $amtChart = $this->calcAmtChart($selectedYear);

        // 支店別受注金額
        $branchAmt = $this->calcBranchAmt();

        // 月次プロセス別カード数（直近12ヶ月）
        $monthlyChart = $this->calcMonthlyChart($allReal);

        // 担当者別成績（マネージャー以上）
        $userRanking = null;
        if ($user->isManager()) {
            $userRanking = $this->calcUserRanking($allReal);
        }

        // 支店別プロセス別内訳テーブル
        $branchTable = $this->calcBranchTable($allReal);

        // ファネル分析
        $funnel = $this->calcFunnel();

        return Inertia::render('Analysis/Index', [
            'kpi' => [
                'total'   => $total,
                'rate'    => $total > 0 ? round($success / $total * 100) : 0,
                'members' => $members,
                'totalAmt' => (float) $totalAmt,
            ],
            'years'        => $years->values(),
            'selectedYear' => $selectedYear,
            'processStat'  => $processStat,
            'amtChart'     => $amtChart,
            'branchAmt'    => $branchAmt,
            'monthlyChart' => $monthlyChart,
            'userRanking'  => $userRanking,
            'branchTable'  => $branchTable,
            'funnel'       => $funnel,
        ]);
    }

    private function calcProcessStat($records): array
    {
        $labels = ['ヒアリング', '提案からフォローアップ', '受注してから売上まで'];
        return collect([1, 2, 3])->map(function ($step) use ($records, $labels) {
            $stepRecords = $records->where('process_step', $step);
            $total   = $stepRecords->count();
            $success = $stepRecords->where('result', 'success')->count();
            return [
                'step'    => $step,
                'label'   => $labels[$step - 1],
                'total'   => $total,
                'success' => $success,
                'rate'    => $total > 0 ? round($success / $total * 100) : 0,
            ];
        })->toArray();
    }

    private function calcAmtChart(int $year): array
    {
        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $m = (($i + 2) % 12) + 1;
            $y = $m >= 3 ? $year : $year + 1;
            $months[] = ['label' => $y . '/' . str_pad($m, 2, '0', STR_PAD_LEFT), 'year' => $y, 'month' => $m, 'amount' => 0];
        }

        $data = Opportunity::where('status', 'won')
            ->whereNotNull('confirmed_amount')
            ->get()
            ->filter(fn($o) => Opportunity::getFiscalYear(now()->setDate(
                (int) substr($o->updated_at, 0, 4),
                (int) substr($o->updated_at, 5, 2),
                1
            )) === $year)
            ->groupBy(fn($o) => date('Y/m', strtotime($o->updated_at)));

        foreach ($months as &$m) {
            $m['amount'] = (float) ($data->get($m['label'])?->sum('confirmed_amount') ?? 0);
        }

        $cumulative = 0;
        foreach ($months as &$m) {
            $cumulative += $m['amount'];
            $m['cumulative'] = $cumulative;
        }

        return $months;
    }

    private function calcBranchAmt(): array
    {
        return Department::withCount(['opportunities as won_count' => fn($q) => $q->where('status', 'won')])
            ->withSum(['opportunities as total_amount' => fn($q) => $q->where('status', 'won')], 'confirmed_amount')
            ->get()
            ->filter(fn($d) => $d->total_amount > 0)
            ->map(fn($d) => [
                'name'   => $d->name,
                'amount' => (float) $d->total_amount,
                'count'  => $d->won_count,
            ])
            ->sortByDesc('amount')
            ->values()
            ->toArray();
    }

    private function calcMonthlyChart($records): array
    {
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $months->push(now()->subMonths($i)->format('Y/m'));
        }

        return $months->map(fn($month) => [
            'month'   => $month,
            'step1'   => $records->where('process_step', 1)->filter(fn($r) => $r->activity_date->format('Y/m') === $month)->count(),
            'step2'   => $records->where('process_step', 2)->filter(fn($r) => $r->activity_date->format('Y/m') === $month)->count(),
            'step3'   => $records->where('process_step', 3)->filter(fn($r) => $r->activity_date->format('Y/m') === $month)->count(),
            'success' => $records->where('result', 'success')->filter(fn($r) => $r->activity_date->format('Y/m') === $month)->count(),
            'failure' => $records->where('result', 'failure')->filter(fn($r) => $r->activity_date->format('Y/m') === $month)->count(),
        ])->toArray();
    }

    private function calcUserRanking($records): array
    {
        return $records->groupBy('user_id')
            ->map(fn($userRecords) => [
                'user_name' => $userRecords->first()->user?->name ?? '不明',
                'dept'      => $userRecords->first()->user?->department?->name ?? '—',
                'total'     => $userRecords->count(),
                'success'   => $userRecords->where('result', 'success')->count(),
                'rate'      => $userRecords->count() > 0
                    ? round($userRecords->where('result', 'success')->count() / $userRecords->count() * 100)
                    : 0,
            ])
            ->sortByDesc('success')
            ->take(10)
            ->values()
            ->toArray();
    }

    private function calcBranchTable($records): array
    {
        return Department::all()->map(function ($dept) use ($records) {
            $deptRecords = $records->filter(fn($r) => $r->user?->department_id === $dept->id);
            $total   = $deptRecords->count();
            $success = $deptRecords->where('result', 'success')->count();
            return [
                'name'    => $dept->name,
                'step1'   => $deptRecords->where('process_step', 1)->count(),
                'step2'   => $deptRecords->where('process_step', 2)->count(),
                'step3'   => $deptRecords->where('process_step', 3)->count(),
                'total'   => $total,
                'success' => $success,
                'rate'    => $total > 0 ? round($success / $total * 100) : 0,
            ];
        })->filter(fn($d) => $d['total'] > 0)->values()->toArray();
    }

    private function calcFunnel(): array
    {
        $step1 = Opportunity::count();
        $step2 = Opportunity::where('current_process', '>=', 2)->count();
        $step3 = Opportunity::where('current_process', '>=', 3)->count();
        $won   = Opportunity::where('status', 'won')->count();

        return [
            ['label' => 'ヒアリング', 'count' => $step1, 'rate' => 100],
            ['label' => '提案・フォロー', 'count' => $step2, 'rate' => $step1 > 0 ? round($step2 / $step1 * 100) : 0],
            ['label' => '受注〜売上', 'count' => $step3, 'rate' => $step1 > 0 ? round($step3 / $step1 * 100) : 0],
            ['label' => '受注完了', 'count' => $won, 'rate' => $step1 > 0 ? round($won / $step1 * 100) : 0],
        ];
    }
}
