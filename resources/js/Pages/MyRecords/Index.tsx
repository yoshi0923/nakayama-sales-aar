import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { AarRecord, KpiStats, PaginatedData, ProcessStat } from '@/types';
import RecordCard from '@/Components/Aar/RecordCard';
import { useState } from 'react';

interface Props {
    records: PaginatedData<AarRecord>;
    stats: KpiStats;
    processStat: ProcessStat[];
    filters: Record<string, string>;
}

export default function MyRecordsIndex({ records, stats, processStat, filters }: Props) {
    const [localFilters, setLocalFilters] = useState(filters);

    const applyFilter = (key: string, value: string) => {
        const next = { ...localFilters, [key]: value };
        if (!value) delete next[key];
        setLocalFilters(next);
        router.get(route('my-records.index'), next, { preserveState: true, replace: true });
    };

    const STEP_BTNS = [
        { label: 'すべて', value: '' },
        { label: 'ヒアリング', value: '1' },
        { label: '提案・フォロー', value: '2' },
        { label: '受注〜売上', value: '3' },
        { label: '✓ 成功', value: 'success', filterKey: 'result', className: 'text-green-700' },
        { label: '✗ 失敗', value: 'failure', filterKey: 'result', className: 'text-red-600' },
    ];

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">マイ記録</h2>}>
            <Head title="マイ記録" />

            <div className="py-8 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                {/* KPIカード */}
                <div className="grid grid-cols-4 gap-3 mb-6">
                    {[
                        { label: '総件数', value: stats.total },
                        { label: '成功', value: stats.success, color: 'text-green-600' },
                        { label: '失敗', value: stats.failure, color: 'text-red-500' },
                        { label: '成功率', value: `${stats.rate}%`, color: stats.rate >= 50 ? 'text-green-600' : 'text-red-500' },
                    ].map(c => (
                        <div key={c.label} className="bg-white border border-gray-200 rounded-xl p-3">
                            <div className="text-xs text-gray-400 mb-1">{c.label}</div>
                            <div className={`text-2xl font-semibold ${c.color ?? 'text-gray-800'}`}>{c.value}</div>
                        </div>
                    ))}
                </div>

                {/* プロセス別成功率 */}
                <div className="bg-white border border-gray-200 rounded-xl p-4 mb-5">
                    <p className="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">プロセス別 成功率</p>
                    {processStat.map(p => (
                        <div key={p.step} className="mb-3">
                            <div className="flex justify-between text-sm mb-1">
                                <span>{p.label}</span>
                                <span className={`font-medium ${p.rate >= 50 ? 'text-green-600' : 'text-red-500'}`}>
                                    {p.rate}% <span className="text-gray-400 text-xs">({p.success}/{p.total}件)</span>
                                </span>
                            </div>
                            <div className="h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div
                                    className={`h-full rounded-full ${p.rate >= 50 ? 'bg-green-500' : 'bg-red-400'}`}
                                    style={{ width: `${p.rate}%` }}
                                />
                            </div>
                        </div>
                    ))}
                </div>

                {/* フィルタ */}
                <div className="flex flex-wrap gap-2 mb-4">
                    {STEP_BTNS.map(btn => {
                        const key = btn.filterKey ?? 'process_step';
                        const active = key === 'result'
                            ? localFilters.result === btn.value
                            : btn.value === '' ? !localFilters.process_step : localFilters.process_step === btn.value;
                        return (
                            <button
                                key={btn.value + key}
                                onClick={() => applyFilter(key, active ? '' : btn.value)}
                                className={`px-3 py-1 text-xs rounded-full border transition-colors ${btn.className ?? ''} ${
                                    active
                                        ? 'bg-gray-800 text-white border-gray-800'
                                        : 'bg-white text-gray-500 border-gray-200 hover:border-gray-400'
                                }`}
                            >
                                {btn.label}
                            </button>
                        );
                    })}
                </div>

                {/* 記録一覧 */}
                {records.data.length === 0 ? (
                    <div className="text-center py-12 text-gray-400">記録がありません</div>
                ) : (
                    records.data.map(r => (
                        <RecordCard key={r.id} record={r} onDelete={() => {}} />
                    ))
                )}

                {/* ページネーション */}
                {records.last_page > 1 && (
                    <div className="flex justify-center gap-2 mt-6">
                        {records.links.map((link, i) => (
                            link.url && (
                                <Link
                                    key={i}
                                    href={link.url}
                                    className={`px-3 py-1 text-sm rounded border ${
                                        link.active
                                            ? 'bg-blue-600 text-white border-blue-600'
                                            : 'bg-white text-gray-600 border-gray-200 hover:border-gray-400'
                                    }`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            )
                        ))}
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
