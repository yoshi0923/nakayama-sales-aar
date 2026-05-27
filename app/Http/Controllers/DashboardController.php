<?php

namespace App\Http\Controllers;

use App\Models\AarRecord;
use App\Models\FollowupAction;
use App\Models\Opportunity;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();
        $currentMonth = now()->format('Y-m');

        // 今月の自分の統計
        $myRecords = AarRecord::where('user_id', $user->id)
            ->where('is_draft', false)
            ->whereYear('activity_date', now()->year)
            ->whereMonth('activity_date', now()->month)
            ->get();

        $myStats = [
            'total'   => $myRecords->count(),
            'success' => $myRecords->where('result', 'success')->count(),
            'failure' => $myRecords->where('result', 'failure')->count(),
            'rate'    => $myRecords->count() > 0
                ? round($myRecords->where('result', 'success')->count() / $myRecords->count() * 100)
                : 0,
        ];

        // 直近のAAR記録（5件）
        $recentRecords = AarRecord::with(['opportunity.client', 'opportunity.area'])
            ->where('user_id', $user->id)
            ->where('is_draft', false)
            ->orderByDesc('activity_date')
            ->limit(5)
            ->get()
            ->map(fn($r) => [
                'id'            => $r->id,
                'process_step'  => $r->process_step,
                'process_label' => $r->process_label,
                'activity_date' => $r->activity_date->format('Y/m/d'),
                'result'        => $r->result,
                'result_label'  => $r->result_label,
                'client_name'   => $r->opportunity->client->name ?? $r->opportunity->title,
                'opportunity_id' => $r->opportunity_id,
            ]);

        // フォローアップ期日アラート（期日が3日以内 or 過ぎたもの）
        $alerts = FollowupAction::with(['opportunity.client'])
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('scheduled_date', '<=', now()->addDays(3))
            ->orderBy('scheduled_date')
            ->limit(10)
            ->get()
            ->map(fn($f) => [
                'id'             => $f->id,
                'scheduled_date' => $f->scheduled_date->format('Y/m/d'),
                'is_overdue'     => $f->scheduled_date->isPast(),
                'description'    => $f->action_description,
                'client_name'    => $f->opportunity->client->name ?? $f->opportunity->title,
                'opportunity_id' => $f->opportunity_id,
            ]);

        // チームランキング（マネージャー以上）
        $teamRanking = null;
        if ($user->isManager()) {
            $teamRanking = AarRecord::with('user')
                ->where('is_draft', false)
                ->whereYear('activity_date', now()->year)
                ->whereMonth('activity_date', now()->month)
                ->where('result', 'success')
                ->get()
                ->groupBy('user_id')
                ->map(fn($records) => [
                    'user_name' => $records->first()->user->name,
                    'count'     => $records->count(),
                ])
                ->sortByDesc('count')
                ->take(3)
                ->values();
        }

        return Inertia::render('Dashboard', [
            'myStats'      => $myStats,
            'recentRecords' => $recentRecords,
            'alerts'       => $alerts,
            'teamRanking'  => $teamRanking,
        ]);
    }
}
