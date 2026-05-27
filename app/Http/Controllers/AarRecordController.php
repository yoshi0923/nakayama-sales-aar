<?php

namespace App\Http\Controllers;

use App\Models\AarRecord;
use App\Models\Area;
use App\Models\Client;
use App\Models\FollowupAction;
use App\Models\Opportunity;
use App\Models\OpportunityType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AarRecordController extends Controller
{
    /**
     * AAR入力フォーム
     */
    public function create(Request $request): Response
    {
        $user = Auth::user();

        // 自分の進行中案件リスト（下書き含む）
        $myOpportunities = Opportunity::with(['client', 'area', 'opportunityType'])
            ->where('assigned_user_id', $user->id)
            ->whereIn('status', ['active', 'hold'])
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn($o) => [
                'id'              => $o->id,
                'title'           => $o->title,
                'client_name'     => $o->client->name ?? '',
                'current_process' => $o->current_process,
                'status'          => $o->status,
            ]);

        return Inertia::render('Aar/Create', [
            'myOpportunities' => $myOpportunities,
            'clients'         => Client::where('is_active', true)->orderBy('name')->get(['id', 'name', 'contact_name']),
            'areas'           => Area::where('is_active', true)->orderBy('sort_order')->get(['id', 'name']),
            'opportunityTypes' => OpportunityType::where('is_active', true)->orderBy('sort_order')->get(['id', 'name']),
            'draftId'         => $request->query('draft'),
        ]);
    }

    /**
     * AARレコード保存
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'opportunity_id'     => 'nullable|exists:opportunities,id',
            // 新規案件の場合
            'new_title'          => 'required_without:opportunity_id|nullable|string|max:255',
            'client_id'          => 'nullable|exists:clients,id',
            'contact_name'       => 'nullable|string|max:100',
            'area_id'            => 'nullable|exists:areas,id',
            'opportunity_type_id' => 'nullable|exists:opportunity_types,id',
            'estimated_amount'   => 'nullable|numeric|min:0',
            'confirmed_amount'   => 'nullable|numeric|min:0',
            // AAR記録
            'process_step'       => 'required|integer|in:1,2,3',
            'activity_date'      => 'required|date',
            'result'             => 'required|in:success,failure,ongoing',
            'q1_goal'            => 'required|string',
            'q2_result'          => 'required|string',
            'q3_cause'           => 'required|string',
            'q4_action'          => 'required|string',
            'is_draft'           => 'boolean',
            // フォローアップ
            'followup_description' => 'nullable|string',
            'followup_date'        => 'nullable|date|after:today',
        ]);

        $user = Auth::user();

        DB::transaction(function () use ($validated, $user) {
            // 案件の確定
            if (!empty($validated['opportunity_id'])) {
                $opportunity = Opportunity::findOrFail($validated['opportunity_id']);
            } else {
                $opportunity = Opportunity::create([
                    'opportunity_no'      => Opportunity::generateOpportunityNo(),
                    'title'               => $validated['new_title'],
                    'client_id'           => $validated['client_id'] ?? null,
                    'contact_name'        => $validated['contact_name'] ?? null,
                    'area_id'             => $validated['area_id'] ?? null,
                    'opportunity_type_id' => $validated['opportunity_type_id'] ?? null,
                    'assigned_user_id'    => $user->id,
                    'department_id'       => $user->department_id,
                    'estimated_amount'    => $validated['estimated_amount'] ?? null,
                    'fiscal_year'         => Opportunity::getFiscalYear(now()),
                    'status'              => 'active',
                    'current_process'     => $validated['process_step'],
                ]);
            }

            // AARレコード作成
            $isDraft = $validated['is_draft'] ?? false;
            $record = AarRecord::create([
                'opportunity_id' => $opportunity->id,
                'user_id'        => $user->id,
                'process_step'   => $validated['process_step'],
                'activity_date'  => $validated['activity_date'],
                'result'         => $validated['result'],
                'q1_goal'        => $validated['q1_goal'],
                'q2_result'      => $validated['q2_result'],
                'q3_cause'       => $validated['q3_cause'],
                'q4_action'      => $validated['q4_action'],
                'is_draft'       => $isDraft,
                'submitted_at'   => $isDraft ? null : now(),
            ]);

            // 案件の確定金額・プロセス・ステータスを更新
            if (!$isDraft) {
                $updates = ['current_process' => max($opportunity->current_process, $validated['process_step'])];

                if ($validated['process_step'] >= 2 && !empty($validated['confirmed_amount'])) {
                    $updates['confirmed_amount'] = $validated['confirmed_amount'];
                }
                if ($validated['result'] === 'success' && $validated['process_step'] === 3) {
                    $updates['status'] = 'won';
                } elseif ($validated['result'] === 'failure') {
                    $updates['status'] = 'lost';
                }

                $opportunity->update($updates);
            }

            // フォローアップアクションの作成
            if (!empty($validated['followup_description']) && !empty($validated['followup_date'])) {
                FollowupAction::create([
                    'aar_record_id'      => $record->id,
                    'opportunity_id'     => $opportunity->id,
                    'user_id'            => $user->id,
                    'action_description' => $validated['followup_description'],
                    'scheduled_date'     => $validated['followup_date'],
                    'status'             => 'pending',
                ]);
            }
        });

        return redirect()->route('my-records.index')
            ->with('success', $request->boolean('is_draft') ? '下書きを保存しました。' : 'AARを保存しました。');
    }

    /**
     * 自分の記録一覧
     */
    public function myRecords(Request $request): Response
    {
        $user = Auth::user();

        $query = AarRecord::with(['opportunity.client', 'opportunity.area', 'opportunity.opportunityType'])
            ->where('user_id', $user->id);

        // フィルタ
        if ($request->filled('process_step')) {
            $query->where('process_step', $request->process_step);
        }
        if ($request->filled('result')) {
            $query->where('result', $request->result);
        }
        if ($request->filled('is_draft')) {
            $query->where('is_draft', $request->boolean('is_draft'));
        }
        if ($request->filled('date_from')) {
            $query->where('activity_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('activity_date', '<=', $request->date_to);
        }

        $records = $query->orderByDesc('activity_date')
            ->paginate(20)
            ->withQueryString()
            ->through(fn($r) => $this->formatRecord($r));

        // 自分の統計
        $allMyRecords = AarRecord::where('user_id', $user->id)->where('is_draft', false)->get();
        $stats = $this->calcStats($allMyRecords);

        // プロセス別成功率
        $processStat = $this->calcProcessStat($allMyRecords);

        return Inertia::render('MyRecords/Index', [
            'records'    => $records,
            'stats'      => $stats,
            'processStat' => $processStat,
            'filters'    => $request->only(['process_step', 'result', 'is_draft', 'date_from', 'date_to']),
        ]);
    }

    /**
     * チームDB
     */
    public function teamDb(Request $request): Response
    {
        $query = AarRecord::with(['opportunity.client', 'opportunity.area', 'opportunity.opportunityType', 'user.department'])
            ->where('is_draft', false);

        if ($request->filled('process_step')) {
            $query->where('process_step', $request->process_step);
        }
        if ($request->filled('result')) {
            $query->where('result', $request->result);
        }
        if ($request->filled('keyword')) {
            $kw = $request->keyword;
            $query->where(function ($q) use ($kw) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$kw}%"))
                  ->orWhereHas('opportunity.client', fn($c) => $c->where('name', 'like', "%{$kw}%"))
                  ->orWhereHas('opportunity', fn($o) => $o->where('title', 'like', "%{$kw}%"));
            });
        }
        if ($request->filled('date_from')) {
            $query->where('activity_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('activity_date', '<=', $request->date_to);
        }

        $records = $query->orderByDesc('activity_date')
            ->paginate(20)
            ->withQueryString()
            ->through(fn($r) => $this->formatRecord($r, true));

        $allRecords = AarRecord::where('is_draft', false)->get();
        $stats = $this->calcStats($allRecords);

        return Inertia::render('TeamDb/Index', [
            'records' => $records,
            'stats'   => $stats,
            'filters' => $request->only(['process_step', 'result', 'keyword', 'date_from', 'date_to']),
        ]);
    }

    /**
     * 記録の詳細（モーダル用）
     */
    public function show(AarRecord $aarRecord): \Illuminate\Http\JsonResponse
    {
        $aarRecord->load(['opportunity.client', 'opportunity.area', 'opportunity.opportunityType', 'user.department', 'followupAction']);

        return response()->json([
            'id'            => $aarRecord->id,
            'process_step'  => $aarRecord->process_step,
            'process_label' => $aarRecord->process_label,
            'activity_date' => $aarRecord->activity_date->format('Y/m/d'),
            'result'        => $aarRecord->result,
            'result_label'  => $aarRecord->result_label,
            'q1_goal'       => $aarRecord->q1_goal,
            'q2_result'     => $aarRecord->q2_result,
            'q3_cause'      => $aarRecord->q3_cause,
            'q4_action'     => $aarRecord->q4_action,
            'is_draft'      => $aarRecord->is_draft,
            'submitted_at'  => $aarRecord->submitted_at?->format('Y/m/d H:i'),
            'user'          => [
                'id'   => $aarRecord->user->id,
                'name' => $aarRecord->user->name,
                'dept' => $aarRecord->user->department?->name,
            ],
            'opportunity'   => [
                'id'    => $aarRecord->opportunity->id,
                'title' => $aarRecord->opportunity->title,
                'client_name' => $aarRecord->opportunity->client?->name,
                'area'  => $aarRecord->opportunity->area?->name,
                'type'  => $aarRecord->opportunity->opportunityType?->name,
                'estimated_amount'  => $aarRecord->opportunity->estimated_amount,
                'confirmed_amount'  => $aarRecord->opportunity->confirmed_amount,
            ],
            'followup' => $aarRecord->followupAction ? [
                'description'    => $aarRecord->followupAction->action_description,
                'scheduled_date' => $aarRecord->followupAction->scheduled_date->format('Y/m/d'),
                'status'         => $aarRecord->followupAction->status,
            ] : null,
        ]);
    }

    /**
     * 記録削除（ソフトデリート）
     */
    public function destroy(AarRecord $aarRecord): RedirectResponse
    {
        $user = Auth::user();

        if ($aarRecord->user_id !== $user->id && !$user->isAdmin()) {
            abort(403);
        }

        $aarRecord->delete();

        return back()->with('success', '記録を削除しました。');
    }

    // ── Private helpers ───────────────────────────────────────

    private function formatRecord(AarRecord $r, bool $includeUser = false): array
    {
        $data = [
            'id'            => $r->id,
            'process_step'  => $r->process_step,
            'process_label' => $r->process_label,
            'activity_date' => $r->activity_date->format('Y/m/d'),
            'result'        => $r->result,
            'result_label'  => $r->result_label,
            'is_draft'      => $r->is_draft,
            'q1_goal'       => $r->q1_goal,
            'q2_result'     => $r->q2_result,
            'q3_cause'      => $r->q3_cause,
            'q4_action'     => $r->q4_action,
            'opportunity'   => [
                'id'    => $r->opportunity->id,
                'title' => $r->opportunity->title,
                'client_name' => $r->opportunity->client?->name,
                'area'  => $r->opportunity->area?->name,
                'type'  => $r->opportunity->opportunityType?->name,
                'estimated_amount'  => $r->opportunity->estimated_amount,
                'confirmed_amount'  => $r->opportunity->confirmed_amount,
            ],
        ];

        if ($includeUser) {
            $data['user'] = [
                'id'   => $r->user->id,
                'name' => $r->user->name,
                'dept' => $r->user->department?->name,
            ];
        }

        return $data;
    }

    private function calcStats($records): array
    {
        $real = $records->where('is_draft', false);
        $total   = $real->count();
        $success = $real->where('result', 'success')->count();
        $failure = $real->where('result', 'failure')->count();

        return [
            'total'   => $total,
            'success' => $success,
            'failure' => $failure,
            'rate'    => $total > 0 ? round($success / $total * 100) : 0,
        ];
    }

    private function calcProcessStat($records): array
    {
        $labels = ['ヒアリング', '提案からフォローアップ', '受注してから売上まで'];
        $result = [];

        foreach ([1, 2, 3] as $step) {
            $stepRecords = $records->where('process_step', $step);
            $total   = $stepRecords->count();
            $success = $stepRecords->where('result', 'success')->count();
            $result[] = [
                'step'    => $step,
                'label'   => $labels[$step - 1],
                'total'   => $total,
                'success' => $success,
                'rate'    => $total > 0 ? round($success / $total * 100) : 0,
            ];
        }

        return $result;
    }
}
