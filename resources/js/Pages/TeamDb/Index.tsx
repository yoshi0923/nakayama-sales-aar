import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { AarRecord, KpiStats, PaginatedData } from '@/types';
import RecordCard from '@/Components/Aar/RecordCard';
import { useState } from 'react';

interface Props {
    records: PaginatedData<AarRecord>;
    stats: KpiStats;
    filters: Record<string, string>;
}

export default function TeamDbIndex({ records, stats, filters }: Props) {
    const [keyword, setKeyword] = useState(filters.keyword ?? '');

    const applyFilter = (params: Record<string, string>) => {
        const next = { ...filters, ...params };
        Object.keys(next).forEach(k => { if (!next[k]) delete next[k]; });
        router.get(route('team-db.index'), next, { preserveState: true, replace: true });
    };

    const FILTER_BTNS = [
        { label: 'すべて', key: 'process_step', value: '' },
        { label: 'ヒアリング', key: 'process_step', value: '1' },
        { label: '提案・フォロー', key: 'process_step', value: '2' },
        { label: '受注〜売上', key: 'process_step', value: '3' },
        { label: '✓ 成功', key: 'result', value: 'success', className: 'text-green-700' },
        { label: '✗ 失敗', key: 'result', value: 'failure', className: 'text-red-600' },
    ];

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">チームDB</h2>}>
            <Head title="チームDB" />

            <div className="py-8 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                {/* KPIカード */}
                <div className="grid grid-cols-4 gap-3 mb-5">
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

                {/* 検索 */}
                <input
                    type="text"
                    className="w-full mb-4 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400"
                    placeholder="担当者名・顧客名で検索..."
                    value={keyword}
                    onChange={e => setKeyword(e.target.value)}
                    onKeyDown={e => e.key === 'Enter' && applyFilter({ keyword })}
                />

                {/* フィルタ */}
                <div className="flex flex-wrap gap-2 mb-4">
                    {FILTER_BTNS.map(btn => {
                        const active = filters[btn.key] === btn.value || (btn.value === '' && !filters[btn.key]);
                        return (
                            <button
                                key={btn.value + btn.key}
                                onClick={() => applyFilter({ [btn.key]: active ? '' : btn.value })}
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
                    <div className="text-center py-12 text-gray-400">該当する記録がありません</div>
                ) : (
                    records.data.map(r => (
                        <RecordCard key={r.id} record={r} showUser />
                    ))
                )}

                {/* ページネーション */}
                {records.last_page > 1 && (
                    <div className="flex justify-center gap-2 mt-6">
                        {records.links.map((link, i) =>
                            link.url ? (
                                <a
                                    key={i}
                                    href={link.url}
                                    className={`px-3 py-1 text-sm rounded border ${
                                        link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-200'
                                    }`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ) : null
                        )}
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
