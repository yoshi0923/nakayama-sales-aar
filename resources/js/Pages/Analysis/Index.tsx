import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { router } from '@inertiajs/react';
import {
    Bar, BarChart, CartesianGrid, Cell, ComposedChart,
    Legend, Line, ResponsiveContainer, Tooltip, XAxis, YAxis,
} from 'recharts';

// ── 型定義 ────────────────────────────────────────────────────
interface ProcessStat   { step: number; label: string; total: number; success: number; rate: number }
interface AmtPoint      { label: string; amount: number; cumulative: number }
interface BranchAmt     { name: string; amount: number; count: number }
interface MonthlyPoint  { month: string; step1: number; step2: number; step3: number; success: number; failure: number }
interface UserRankItem  { user_name: string; dept: string; total: number; success: number; rate: number }
interface BranchRow     { name: string; step1: number; step2: number; step3: number; total: number; success: number; rate: number }
interface FunnelStep    { label: string; count: number; rate: number }

interface Props extends PageProps {
    kpi: { total: number; rate: number; members: number; totalAmt: number }
    years: number[]
    selectedYear: number
    processStat: ProcessStat[]
    amtChart: AmtPoint[]
    branchAmt: BranchAmt[]
    monthlyChart: MonthlyPoint[]
    userRanking: UserRankItem[]
    branchTable: BranchRow[]
    funnel: FunnelStep[]
}

// ── ユーティリティ ──────────────────────────────────────────
function fmt(n: number): string {
    if (n >= 100_000_000) return `${(n / 100_000_000).toFixed(1)}億`;
    if (n >= 10_000)      return `${Math.round(n / 10_000)}万`;
    return n.toLocaleString();
}

// ── 共通コンポーネント ────────────────────────────────────────
function KpiCard({ label, value, sub, accent }: { label: string; value: string; sub?: string; accent?: string }) {
    return (
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm px-5 py-4">
            <p className="text-xs text-gray-500 mb-1">{label}</p>
            <p className={`text-2xl font-bold ${accent ?? 'text-gray-800'}`}>{value}</p>
            {sub && <p className="text-xs text-gray-400 mt-0.5">{sub}</p>}
        </div>
    );
}

function Card({ children, className = '' }: { children: React.ReactNode; className?: string }) {
    return (
        <div className={`bg-white rounded-xl border border-gray-200 shadow-sm ${className}`}>
            {children}
        </div>
    );
}

function SectionTitle({ children }: { children: React.ReactNode }) {
    return <h2 className="text-sm font-semibold text-gray-700 mb-3 pb-2 border-b border-gray-100">{children}</h2>;
}

const TOOLTIP_STYLE = { fontSize: 12, borderRadius: 8, border: '1px solid #e5e7eb' };

// ── メインページ ──────────────────────────────────────────────
export default function Index({ kpi, years, selectedYear, processStat, amtChart, branchAmt, monthlyChart, userRanking, branchTable, funnel }: Props) {

    const changeYear = (y: number) =>
        router.get(route('analysis.index'), { year: y }, { preserveScroll: true });

    return (
        <AuthenticatedLayout header={<h1 className="text-lg font-semibold text-gray-800">分析ダッシュボード</h1>}>
            <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

                {/* ── KPIカード ── */}
                <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <KpiCard label="総AARカード数" value={`${kpi.total.toLocaleString()}件`} />
                    <KpiCard
                        label="全体成功率"
                        value={`${kpi.rate}%`}
                        accent={kpi.rate >= 50 ? 'text-green-600' : kpi.rate >= 30 ? 'text-yellow-600' : 'text-red-500'}
                    />
                    <KpiCard label="参加メンバー" value={`${kpi.members}名`} />
                    <KpiCard label="累計受注金額" value={fmt(kpi.totalAmt)} sub="受注完了のみ" accent="text-[#185FA5]" />
                </div>

                {/* ── 受注金額推移 ── */}
                <div>
                    <SectionTitle>受注金額推移</SectionTitle>
                    <div className="flex gap-2 mb-3 flex-wrap">
                        {years.length === 0
                            ? <span className="text-xs text-gray-400">データなし</span>
                            : years.map(y => (
                                <button key={y} onClick={() => changeYear(y)}
                                    className={`px-3 py-1 text-xs rounded-full border transition-colors ${
                                        y === selectedYear
                                            ? 'bg-[#185FA5] text-white border-[#185FA5]'
                                            : 'text-gray-600 border-gray-300 hover:bg-gray-50'
                                    }`}>
                                    {y}年度
                                </button>
                            ))}
                    </div>
                    <Card className="p-4">
                        {amtChart.every(m => m.amount === 0) ? (
                            <p className="text-center text-sm text-gray-400 py-10">受注データがありません</p>
                        ) : (
                            <ResponsiveContainer width="100%" height={240}>
                                <ComposedChart data={amtChart} margin={{ top: 5, right: 20, left: 10, bottom: 5 }}>
                                    <CartesianGrid strokeDasharray="3 3" stroke="#f3f4f6" />
                                    <XAxis dataKey="label" tick={{ fontSize: 10 }} />
                                    <YAxis yAxisId="bar" tickFormatter={fmt} tick={{ fontSize: 10 }} />
                                    <YAxis yAxisId="line" orientation="right" tickFormatter={fmt} tick={{ fontSize: 10 }} />
                                    <Tooltip contentStyle={TOOLTIP_STYLE} formatter={(v, name) => [fmt(Number(v)), String(name)]} />
                                    <Legend wrapperStyle={{ fontSize: 11 }} />
                                    <Bar yAxisId="bar" dataKey="amount" name="月次受注" fill="#93c5fd" radius={[3, 3, 0, 0]} />
                                    <Line yAxisId="line" type="monotone" dataKey="cumulative" name="累計" stroke="#185FA5" strokeWidth={2} dot={false} />
                                </ComposedChart>
                            </ResponsiveContainer>
                        )}
                    </Card>
                </div>

                {/* ── プロセス成功率 + ファネル ── */}
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <SectionTitle>プロセス別成功率</SectionTitle>
                        <Card className="p-5 space-y-5">
                            {processStat.map(p => (
                                <div key={p.step}>
                                    <div className="flex justify-between text-xs mb-1.5">
                                        <span className="text-gray-600 font-medium">
                                            STEP{p.step}　{p.label}
                                        </span>
                                        <span className="text-gray-500">{p.success}/{p.total}件　<span className="font-bold text-gray-700">{p.rate}%</span></span>
                                    </div>
                                    <div className="h-2 bg-gray-100 rounded-full overflow-hidden">
                                        <div className="h-full rounded-full transition-all duration-500"
                                            style={{
                                                width: `${p.rate}%`,
                                                backgroundColor: ['#3b82f6', '#10b981', '#8b5cf6'][p.step - 1],
                                            }} />
                                    </div>
                                </div>
                            ))}
                            {processStat.length === 0 && (
                                <p className="text-center text-sm text-gray-400 py-4">データなし</p>
                            )}
                        </Card>
                    </div>

                    <div>
                        <SectionTitle>案件ファネル</SectionTitle>
                        <Card className="p-4">
                            <ResponsiveContainer width="100%" height={200}>
                                <BarChart data={funnel} layout="vertical" margin={{ top: 5, right: 50, left: 70, bottom: 5 }}>
                                    <CartesianGrid strokeDasharray="3 3" stroke="#f3f4f6" horizontal={false} />
                                    <XAxis type="number" tick={{ fontSize: 10 }} allowDecimals={false} />
                                    <YAxis type="category" dataKey="label" tick={{ fontSize: 11 }} width={70} />
                                    <Tooltip contentStyle={TOOLTIP_STYLE}
                                        formatter={(v, _, entry) => [
                                            `${Number(v)}件（${(entry.payload as FunnelStep).rate}%）`, '案件数'
                                        ]} />
                                    <Bar dataKey="count" radius={[0, 4, 4, 0]}>
                                        {funnel.map((_, i) => (
                                            <Cell key={i} fill={['#185FA5', '#3b82f6', '#60a5fa', '#10b981'][i] ?? '#93c5fd'} />
                                        ))}
                                    </Bar>
                                </BarChart>
                            </ResponsiveContainer>
                        </Card>
                    </div>
                </div>

                {/* ── 月次トレンド ── */}
                <div>
                    <SectionTitle>月次AARカード推移（直近12ヶ月）</SectionTitle>
                    <Card className="p-4">
                        <ResponsiveContainer width="100%" height={260}>
                            <ComposedChart data={monthlyChart} margin={{ top: 5, right: 20, left: 0, bottom: 5 }}>
                                <CartesianGrid strokeDasharray="3 3" stroke="#f3f4f6" />
                                <XAxis dataKey="month" tick={{ fontSize: 10 }} />
                                <YAxis tick={{ fontSize: 10 }} allowDecimals={false} />
                                <Tooltip contentStyle={TOOLTIP_STYLE} />
                                <Legend wrapperStyle={{ fontSize: 11 }} />
                                <Bar dataKey="step1" name="STEP1 ヒアリング" stackId="a" fill="#93c5fd" />
                                <Bar dataKey="step2" name="STEP2 提案" stackId="a" fill="#6ee7b7" />
                                <Bar dataKey="step3" name="STEP3 受注" stackId="a" fill="#c4b5fd" radius={[3, 3, 0, 0]} />
                                <Line type="monotone" dataKey="success" name="成功" stroke="#059669" strokeWidth={2} dot={{ r: 3 }} />
                                <Line type="monotone" dataKey="failure" name="失敗" stroke="#ef4444" strokeWidth={2} dot={{ r: 3 }} />
                            </ComposedChart>
                        </ResponsiveContainer>
                    </Card>
                </div>

                {/* ── 部署別受注 + 部署テーブル ── */}
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <SectionTitle>部署別受注金額</SectionTitle>
                        <Card className="p-4">
                            {branchAmt.length === 0 ? (
                                <p className="text-center text-sm text-gray-400 py-10">受注データなし</p>
                            ) : (
                                <ResponsiveContainer width="100%" height={Math.max(160, branchAmt.length * 48)}>
                                    <BarChart data={branchAmt} layout="vertical" margin={{ top: 5, right: 60, left: 60, bottom: 5 }}>
                                        <CartesianGrid strokeDasharray="3 3" stroke="#f3f4f6" horizontal={false} />
                                        <XAxis type="number" tickFormatter={fmt} tick={{ fontSize: 10 }} />
                                        <YAxis type="category" dataKey="name" tick={{ fontSize: 11 }} width={60} />
                                        <Tooltip contentStyle={TOOLTIP_STYLE}
                                            formatter={(v, _, entry) => [
                                                `${fmt(Number(v))}（${(entry.payload as BranchAmt).count}件）`, '受注金額'
                                            ]} />
                                        <Bar dataKey="amount" fill="#185FA5" radius={[0, 4, 4, 0]} />
                                    </BarChart>
                                </ResponsiveContainer>
                            )}
                        </Card>
                    </div>

                    <div>
                        <SectionTitle>部署別プロセス内訳</SectionTitle>
                        <Card className="overflow-hidden">
                            <table className="w-full text-xs">
                                <thead className="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th className="text-left px-3 py-2.5 font-medium text-gray-500">部署</th>
                                        <th className="text-center px-2 py-2.5 font-medium text-blue-500">S1</th>
                                        <th className="text-center px-2 py-2.5 font-medium text-green-500">S2</th>
                                        <th className="text-center px-2 py-2.5 font-medium text-purple-500">S3</th>
                                        <th className="text-center px-2 py-2.5 font-medium text-gray-500">計</th>
                                        <th className="text-center px-2 py-2.5 font-medium text-gray-500">成功率</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100">
                                    {branchTable.length === 0 ? (
                                        <tr><td colSpan={6} className="px-3 py-8 text-center text-gray-400">データなし</td></tr>
                                    ) : branchTable.map((b, i) => (
                                        <tr key={i} className="hover:bg-gray-50">
                                            <td className="px-3 py-2.5 font-medium text-gray-800">{b.name}</td>
                                            <td className="px-2 py-2.5 text-center text-blue-600">{b.step1}</td>
                                            <td className="px-2 py-2.5 text-center text-green-600">{b.step2}</td>
                                            <td className="px-2 py-2.5 text-center text-purple-600">{b.step3}</td>
                                            <td className="px-2 py-2.5 text-center font-medium text-gray-700">{b.total}</td>
                                            <td className="px-2 py-2.5 text-center">
                                                <span className={`font-semibold ${b.rate >= 50 ? 'text-green-600' : b.rate >= 30 ? 'text-yellow-600' : 'text-red-500'}`}>
                                                    {b.rate}%
                                                </span>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </Card>
                    </div>
                </div>

                {/* ── メンバー別成績 ── */}
                {userRanking.length > 0 && (
                    <div>
                        <SectionTitle>メンバー別成績（成功件数順）</SectionTitle>
                        <Card className="overflow-hidden">
                            <table className="w-full text-sm">
                                <thead className="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th className="text-center px-4 py-3 text-xs font-medium text-gray-500 w-10">#</th>
                                        <th className="text-left px-4 py-3 text-xs font-medium text-gray-500">氏名</th>
                                        <th className="text-left px-4 py-3 text-xs font-medium text-gray-500">部署</th>
                                        <th className="text-center px-4 py-3 text-xs font-medium text-gray-500">総件数</th>
                                        <th className="text-center px-4 py-3 text-xs font-medium text-gray-500">成功</th>
                                        <th className="text-left px-4 py-3 text-xs font-medium text-gray-500 min-w-[140px]">成功率</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100">
                                    {userRanking.map((u, i) => (
                                        <tr key={i} className="hover:bg-gray-50">
                                            <td className="px-4 py-3 text-center">
                                                <span className={`text-sm font-bold ${
                                                    i === 0 ? 'text-yellow-500' :
                                                    i === 1 ? 'text-gray-400' :
                                                    i === 2 ? 'text-amber-600' : 'text-gray-300'
                                                }`}>
                                                    {i + 1}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 font-medium text-gray-800">{u.user_name}</td>
                                            <td className="px-4 py-3 text-gray-500 text-xs">{u.dept || '—'}</td>
                                            <td className="px-4 py-3 text-center text-gray-700">{u.total}</td>
                                            <td className="px-4 py-3 text-center font-semibold text-green-600">{u.success}</td>
                                            <td className="px-4 py-3">
                                                <div className="flex items-center gap-2">
                                                    <div className="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                                        <div className="h-full rounded-full bg-green-500"
                                                            style={{ width: `${u.rate}%` }} />
                                                    </div>
                                                    <span className="text-xs text-gray-600 w-9 text-right">{u.rate}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </Card>
                    </div>
                )}

            </div>
        </AuthenticatedLayout>
    );
}
