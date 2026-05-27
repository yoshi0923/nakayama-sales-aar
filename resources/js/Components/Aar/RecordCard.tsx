import { AarRecord, ProcessStep } from '@/types';
import { router } from '@inertiajs/react';

interface Props {
    record: AarRecord;
    showUser?: boolean;
    onDelete?: (id: number) => void;
}

const STEP_COLORS: Record<ProcessStep, string> = {
    1: 'bg-blue-50 border-blue-200',
    2: 'bg-green-50 border-green-200',
    3: 'bg-purple-50 border-purple-200',
};
const STEP_BADGE: Record<ProcessStep, string> = {
    1: 'bg-blue-100 text-blue-800',
    2: 'bg-green-100 text-green-800',
    3: 'bg-purple-100 text-purple-800',
};

const RESULT_BADGE: Record<string, string> = {
    success: 'bg-green-100 text-green-800',
    failure: 'bg-red-100 text-red-800',
    ongoing: 'bg-yellow-100 text-yellow-800',
};

export default function RecordCard({ record: r, showUser = false, onDelete }: Props) {
    const headerBg = r.is_draft
        ? 'bg-gray-50 border-gray-200'
        : r.result === 'success'
        ? 'bg-green-50 border-green-200'
        : r.result === 'failure'
        ? 'bg-red-50 border-red-200'
        : STEP_COLORS[r.process_step];

    return (
        <div className="border border-gray-200 rounded-xl overflow-hidden mb-3">
            {/* ヘッダー */}
            <div className={`px-4 py-3 flex items-center gap-2 flex-wrap border-b ${headerBg}`}>
                <span className={`text-xs font-medium px-2 py-0.5 rounded-full ${STEP_BADGE[r.process_step]}`}>
                    STEP{r.process_step} {r.process_label}
                </span>
                {r.is_draft ? (
                    <span className="text-xs px-2 py-0.5 rounded-full bg-gray-200 text-gray-600">下書き</span>
                ) : r.result && (
                    <span className={`text-xs px-2 py-0.5 rounded-full ${RESULT_BADGE[r.result]}`}>
                        {r.result_label}
                    </span>
                )}
                <span className="text-xs text-gray-500 ml-1">{r.activity_date}</span>

                {r.opportunity.confirmed_amount != null && r.process_step >= 2 && (
                    <span className="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">
                        ¥{r.opportunity.confirmed_amount.toLocaleString()}
                    </span>
                )}

                {onDelete && (
                    <button
                        onClick={() => {
                            if (confirm('この記録を削除しますか？')) {
                                router.delete(route('aar.destroy', r.id), { preserveScroll: true });
                            }
                        }}
                        className="ml-auto text-xs px-2 py-1 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded transition-colors"
                    >
                        削除
                    </button>
                )}
            </div>

            {/* 本文 */}
            <div className="px-4 py-3 bg-white">
                <div className="flex flex-wrap gap-x-4 gap-y-1 text-sm mb-3">
                    {showUser && r.user && (
                        <span>
                            <span className="text-xs text-gray-400">記入者　</span>
                            <strong className="font-medium">{r.user.name}</strong>
                            {r.user.dept && <span className="text-gray-400 text-xs ml-1">{r.user.dept}</span>}
                        </span>
                    )}
                    <span>
                        <span className="text-xs text-gray-400">顧客　</span>
                        <strong className="font-medium">{r.opportunity.client_name ?? r.opportunity.title}</strong>
                    </span>
                    {r.opportunity.area && (
                        <span className="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">{r.opportunity.area}</span>
                    )}
                    {r.opportunity.type && (
                        <span className="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">{r.opportunity.type}</span>
                    )}
                </div>

                {/* AAR4問 */}
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    {[
                        { n: 1, q: '何を達成しようとしたか', a: r.q1_goal },
                        { n: 2, q: '実際に何が起きたか', a: r.q2_result },
                        { n: 3, q: 'なぜ差異が生じたか', a: r.q3_cause },
                        { n: 4, q: '次回どう改善するか', a: r.q4_action },
                    ].map(({ n, q, a }) => (
                        <div key={n} className="bg-gray-50 rounded-lg p-3">
                            <div className="flex items-center gap-1.5 mb-1">
                                <span className="w-4 h-4 rounded-full bg-gray-200 text-gray-600 text-[9px] flex items-center justify-center font-medium">{n}</span>
                                <span className="text-[10px] text-gray-400 font-medium">{q}</span>
                            </div>
                            <p className="text-sm text-gray-800 leading-relaxed">{a ?? '—'}</p>
                        </div>
                    ))}
                </div>

                {/* フォローアップ */}
                {r.followup && (
                    <div className="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
                        <span className="font-medium">フォローアップ</span>
                        <span className="mx-2">·</span>
                        {r.followup.scheduled_date}
                        <span className="mx-2">·</span>
                        {r.followup.description}
                    </div>
                )}
            </div>
        </div>
    );
}
