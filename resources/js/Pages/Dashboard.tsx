import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { KpiStats, AarRecord, ProcessStep } from '@/types';

interface Alert {
    id: number;
    scheduled_date: string;
    is_overdue: boolean;
    description: string;
    client_name: string;
    opportunity_id: number;
}

interface TeamRanking {
    user_name: string;
    count: number;
}

interface Props {
    myStats: KpiStats;
    recentRecords: AarRecord[];
    alerts: Alert[];
    teamRanking?: TeamRanking[] | null;
}

const STEP_COLORS: Record<ProcessStep, string> = {
    1: 'bg-blue-100 text-blue-800',
    2: 'bg-green-100 text-green-800',
    3: 'bg-purple-100 text-purple-800',
};

const RESULT_COLORS: Record<string, string> = {
    success: 'bg-green-100 text-green-800',
    failure: 'bg-red-100 text-red-800',
    ongoing: 'bg-yellow-100 text-yellow-800',
};

export default function Dashboard({ myStats, recentRecords, alerts, teamRanking }: Props) {
    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold text-gray-800">ダッシュボード</h2>}
        >
            <Head title="ダッシュボード" />

            <div className="py-8 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                {/* フォローアップアラート */}
                {alerts.length > 0 && (
                    <div className="bg-amber-50 border border-amber-200 rounded-xl p-4">
                        <h3 className="text-sm font-semibold text-amber-800 mb-2">
                            ⚠ フォローアップ期日アラート（{alerts.length}件）
                        </h3>
                        <div className="space-y-1">
                            {alerts.map(a => (
                                <div key={a.id} className="flex items-center gap-3 text-sm">
                                    <span className={`font-medium ${a.is_overdue ? 'text-red-600' : 'text-amber-700'}`}>
                                        {a.scheduled_date}
                                        {a.is_overdue && ' (期日超過)'}
                                    </span>
                                    <span className="text-gray-600">{a.client_name}</span>
                                    <span className="text-gray-500 truncate">{a.description}</span>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                {/* KPIカード */}
                <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    {[
                        { label: '今月の活動件数', value: myStats.total, unit: '件' },
                        { label: '成功', value: myStats.success, unit: '件', color: 'text-green-600' },
                        { label: '失敗', value: myStats.failure, unit: '件', color: 'text-red-500' },
                        { label: '成功率', value: myStats.rate, unit: '%', color: myStats.rate >= 50 ? 'text-green-600' : 'text-red-500' },
                    ].map(card => (
                        <div key={card.label} className="bg-white border border-gray-200 rounded-xl p-4">
                            <div className="text-xs text-gray-500 mb-1">{card.label}</div>
                            <div className={`text-3xl font-semibold ${card.color ?? 'text-gray-800'}`}>
                                {card.value}<span className="text-base font-normal ml-0.5">{card.unit}</span>
                            </div>
                        </div>
                    ))}
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    {/* 直近の活動 */}
                    <div className="bg-white border border-gray-200 rounded-xl p-5">
                        <div className="flex items-center justify-between mb-4">
                            <h3 className="text-sm font-semibold text-gray-700">直近の活動</h3>
                            <Link href={route('my-records.index')} className="text-xs text-blue-600 hover:underline">
                                すべて見る →
                            </Link>
                        </div>
                        {recentRecords.length === 0 ? (
                            <p className="text-sm text-gray-400 text-center py-4">記録がありません</p>
                        ) : (
                            <div className="space-y-2">
                                {recentRecords.map(r => (
                                    <div key={r.id} className="flex items-center gap-2 text-sm">
                                        <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${STEP_COLORS[r.process_step]}`}>
                                            STEP{r.process_step}
                                        </span>
                                        <span className="text-gray-500 text-xs">{r.activity_date}</span>
                                        <span className="text-gray-800 truncate flex-1">{r.opportunity.client_name ?? r.opportunity.title}</span>
                                        {r.result && (
                                            <span className={`text-xs px-2 py-0.5 rounded-full ${RESULT_COLORS[r.result]}`}>
                                                {r.result_label}
                                            </span>
                                        )}
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* チームランキング */}
                    {teamRanking && (
                        <div className="bg-white border border-gray-200 rounded-xl p-5">
                            <h3 className="text-sm font-semibold text-gray-700 mb-4">今月のチーム成功件数 TOP3</h3>
                            {teamRanking.length === 0 ? (
                                <p className="text-sm text-gray-400 text-center py-4">データがありません</p>
                            ) : (
                                <div className="space-y-3">
                                    {teamRanking.map((r, i) => (
                                        <div key={i} className="flex items-center gap-3">
                                            <span className="text-lg font-bold text-gray-300 w-6">{i + 1}</span>
                                            <span className="flex-1 text-sm font-medium text-gray-800">{r.user_name}</span>
                                            <span className="text-sm font-semibold text-green-600">{r.count}件</span>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    )}
                </div>

                {/* クイックアクション */}
                <div className="flex gap-3">
                    <Link
                        href={route('aar.create')}
                        className="px-5 py-2.5 bg-[#185FA5] text-white text-sm font-medium rounded-lg hover:bg-[#0C447C] transition-colors"
                    >
                        ＋ AARを記録する
                    </Link>
                    <Link
                        href={route('my-records.index')}
                        className="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        マイ記録
                    </Link>
                    <Link
                        href={route('team-db.index')}
                        className="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        チームDB
                    </Link>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
