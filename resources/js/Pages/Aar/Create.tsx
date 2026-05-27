import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { Area, Client, Opportunity, OpportunityType, ProcessStep, Result } from '@/types';
import { FormEvent, useState } from 'react';

interface Props {
    myOpportunities: Opportunity[];
    clients: Client[];
    areas: Area[];
    opportunityTypes: OpportunityType[];
    draftId?: string;
}

type ProcessMode = 'existing' | 'new';

const STEP_LABELS: Record<number, string> = {
    1: 'ヒアリング',
    2: '提案からフォローアップ',
    3: '受注してから売上まで',
};
const STEP_HINTS: Record<number, string> = {
    1: '顧客課題のヒアリング',
    2: '提案・交渉・フォロー',
    3: '受注後〜売上計上',
};
const STEP_COLORS: Record<number, string> = {
    1: 'border-blue-400 bg-blue-50 text-blue-800',
    2: 'border-green-400 bg-green-50 text-green-800',
    3: 'border-purple-400 bg-purple-50 text-purple-800',
};

export default function AarCreate({ myOpportunities, clients, areas, opportunityTypes }: Props) {
    const [mode, setMode] = useState<ProcessMode>('existing');
    const [step, setStep] = useState<ProcessStep | null>(null);

    const { data, setData, post, processing, errors } = useForm({
        opportunity_id: '',
        new_title: '',
        client_id: '',
        contact_name: '',
        area_id: '',
        opportunity_type_id: '',
        estimated_amount: '',
        confirmed_amount: '',
        process_step: '' as ProcessStep | '',
        activity_date: new Date().toISOString().split('T')[0],
        result: '' as Result | '',
        q1_goal: '',
        q2_result: '',
        q3_cause: '',
        q4_action: '',
        is_draft: false,
        followup_description: '',
        followup_date: '',
    });

    const handleSubmit = (e: FormEvent, isDraft = false) => {
        e.preventDefault();
        setData('is_draft', isDraft);
        post(route('aar.store'));
    };

    const selectedOpportunity = myOpportunities.find(o => String(o.id) === data.opportunity_id);
    const canSelectStep = (s: number) => {
        if (s === 1) return true;
        if (!selectedOpportunity) return mode === 'new' ? s === 1 : false;
        return selectedOpportunity.current_process >= s - 1;
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">AARを記録する</h2>}>
            <Head title="AAR入力" />

            <div className="py-8 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <form onSubmit={e => handleSubmit(e)}>

                    {/* ① 案件選択 */}
                    <div className="bg-white border border-gray-200 rounded-xl p-5 mb-4">
                        <p className="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">① 案件</p>
                        <div className="flex gap-2 mb-3">
                            <button type="button" onClick={() => setMode('existing')}
                                className={`px-4 py-1.5 text-sm rounded-lg border transition-colors ${mode === 'existing' ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-600 border-gray-200'}`}>
                                既存案件に紐付ける
                            </button>
                            <button type="button" onClick={() => { setMode('new'); setData('opportunity_id', ''); }}
                                className={`px-4 py-1.5 text-sm rounded-lg border transition-colors ${mode === 'new' ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-600 border-gray-200'}`}>
                                新規案件を作成
                            </button>
                        </div>

                        {mode === 'existing' ? (
                            <select
                                value={data.opportunity_id}
                                onChange={e => setData('opportunity_id', e.target.value)}
                                className="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400 bg-gray-50"
                            >
                                <option value="">— 案件を選択 —</option>
                                {myOpportunities.map(o => (
                                    <option key={o.id} value={o.id}>
                                        {o.client_name ? `${o.client_name} / ` : ''}{o.title}（STEP{o.current_process}）
                                    </option>
                                ))}
                            </select>
                        ) : (
                            <div className="space-y-3">
                                <div>
                                    <label className="text-xs text-gray-500 mb-1 block">案件タイトル <span className="text-red-500">*</span></label>
                                    <input
                                        type="text"
                                        value={data.new_title}
                                        onChange={e => setData('new_title', e.target.value)}
                                        placeholder="例：ABC建設 新工場プラント導入"
                                        className="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400"
                                    />
                                </div>
                                <div className="grid grid-cols-2 gap-3">
                                    <div>
                                        <label className="text-xs text-gray-500 mb-1 block">取引先</label>
                                        <select
                                            value={data.client_id}
                                            onChange={e => setData('client_id', e.target.value)}
                                            className="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400 bg-gray-50"
                                        >
                                            <option value="">— 選択 —</option>
                                            {clients.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                                        </select>
                                    </div>
                                    <div>
                                        <label className="text-xs text-gray-500 mb-1 block">取引先担当者</label>
                                        <input
                                            type="text"
                                            value={data.contact_name}
                                            onChange={e => setData('contact_name', e.target.value)}
                                            placeholder="鈴木 一郎"
                                            className="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400"
                                        />
                                    </div>
                                    <div>
                                        <label className="text-xs text-gray-500 mb-1 block">エリア</label>
                                        <select
                                            value={data.area_id}
                                            onChange={e => setData('area_id', e.target.value)}
                                            className="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400 bg-gray-50"
                                        >
                                            <option value="">— 選択 —</option>
                                            {areas.map(a => <option key={a.id} value={a.id}>{a.name}</option>)}
                                        </select>
                                    </div>
                                    <div>
                                        <label className="text-xs text-gray-500 mb-1 block">案件種別</label>
                                        <select
                                            value={data.opportunity_type_id}
                                            onChange={e => setData('opportunity_type_id', e.target.value)}
                                            className="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400 bg-gray-50"
                                        >
                                            <option value="">— 選択 —</option>
                                            {opportunityTypes.map(t => <option key={t.id} value={t.id}>{t.name}</option>)}
                                        </select>
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>

                    {/* ② プロセスステップ選択 */}
                    <div className="bg-white border border-gray-200 rounded-xl p-5 mb-4">
                        <p className="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">② プロセスステップを選択</p>
                        <div className="grid grid-cols-3 gap-2">
                            {([1, 2, 3] as ProcessStep[]).map(s => (
                                <button
                                    key={s}
                                    type="button"
                                    disabled={!canSelectStep(s)}
                                    onClick={() => { setStep(s); setData('process_step', s); }}
                                    className={`p-3 border rounded-xl text-left transition-all ${
                                        data.process_step === s
                                            ? STEP_COLORS[s] + ' border-2'
                                            : !canSelectStep(s)
                                            ? 'border-gray-100 bg-gray-50 text-gray-300 cursor-not-allowed'
                                            : 'border-gray-200 bg-gray-50 text-gray-600 hover:border-gray-400 hover:bg-white'
                                    }`}
                                >
                                    <div className="text-[10px] font-bold mb-0.5">STEP {s}</div>
                                    <div className="text-sm font-medium">{STEP_LABELS[s]}</div>
                                    <div className="text-[10px] mt-0.5 opacity-70">{STEP_HINTS[s]}</div>
                                </button>
                            ))}
                        </div>
                        {errors.process_step && <p className="text-xs text-red-500 mt-2">{errors.process_step}</p>}
                    </div>

                    {/* ③ 活動日・金額 */}
                    {data.process_step && (
                        <div className="bg-white border border-gray-200 rounded-xl p-5 mb-4">
                            <p className="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">③ 活動情報</p>
                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="text-xs text-gray-500 mb-1 block">活動日 <span className="text-red-500">*</span></label>
                                    <input
                                        type="date"
                                        value={data.activity_date}
                                        onChange={e => setData('activity_date', e.target.value)}
                                        className="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400"
                                    />
                                </div>
                                {data.process_step >= 2 && (
                                    <div>
                                        <label className="text-xs text-gray-500 mb-1 block">
                                            {data.process_step === 3 ? '確定受注金額' : '見込金額'}
                                        </label>
                                        <div className="relative">
                                            <span className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">¥</span>
                                            <input
                                                type="number"
                                                min={0}
                                                value={data.process_step === 3 ? data.confirmed_amount : data.estimated_amount}
                                                onChange={e => setData(data.process_step === 3 ? 'confirmed_amount' : 'estimated_amount', e.target.value)}
                                                placeholder="0"
                                                className="w-full pl-7 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400"
                                            />
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>
                    )}

                    {/* ④ AARふり返り */}
                    {data.process_step && (
                        <div className="bg-white border border-gray-200 rounded-xl p-5 mb-4">
                            <p className="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">④ AARふり返り</p>
                            <p className="text-xs text-gray-400 mb-4">4つの問いに答えて今回の活動を振り返りましょう。</p>

                            {[
                                { id: 'q1_goal', n: 1, q: '何を達成しようとしたか？', hint: '目標・意図', placeholder: '例：担当者にA製品の優位性を伝え、次回デモの約束を取り付けたかった' },
                                { id: 'q2_result', n: 2, q: '実際に何が起きたか？', hint: '結果・事実', placeholder: '例：予算の話になり、競合他社との比較検討中であることが判明した' },
                                { id: 'q3_cause', n: 3, q: 'なぜ差異が生じたか？', hint: '原因・背景', placeholder: '例：事前に競合情報を把握せず、価格訴求の準備が不足していた' },
                                { id: 'q4_action', n: 4, q: '次回どう改善するか？', hint: 'アクション', placeholder: '例：次回訪問前に競合比較表を準備し、コスト削減効果を数値で示す' },
                            ].map(({ id, n, q, hint, placeholder }) => (
                                <div key={id} className="mb-4 p-4 bg-gray-50 rounded-xl">
                                    <div className="flex items-center gap-2 mb-2">
                                        <span className="w-5 h-5 rounded-full bg-gray-200 text-gray-600 text-[10px] flex items-center justify-center font-medium">{n}</span>
                                        <span className="text-sm font-medium text-gray-700">{q}</span>
                                        <span className="text-xs text-gray-400">{hint}</span>
                                    </div>
                                    <textarea
                                        value={data[id as keyof typeof data] as string}
                                        onChange={e => setData(id as keyof typeof data, e.target.value)}
                                        placeholder={placeholder}
                                        rows={3}
                                        className="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400 bg-white resize-y"
                                    />
                                    {errors[id as keyof typeof errors] && (
                                        <p className="text-xs text-red-500 mt-1">{errors[id as keyof typeof errors]}</p>
                                    )}
                                </div>
                            ))}
                        </div>
                    )}

                    {/* ⑤ 結果 */}
                    {data.process_step && (
                        <div className="bg-white border border-gray-200 rounded-xl p-5 mb-4">
                            <p className="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">⑤ このプロセスの結果</p>
                            <div className="grid grid-cols-3 gap-2">
                                {([
                                    { value: 'success', label: '✓ 成功', active: 'bg-green-100 border-green-500 text-green-800' },
                                    { value: 'failure', label: '✗ 失敗', active: 'bg-red-100 border-red-500 text-red-800' },
                                    { value: 'ongoing', label: '→ 継続中', active: 'bg-yellow-100 border-yellow-500 text-yellow-800' },
                                ] as const).map(btn => (
                                    <button
                                        key={btn.value}
                                        type="button"
                                        onClick={() => setData('result', btn.value)}
                                        className={`py-3 text-sm font-medium rounded-xl border-2 transition-all ${
                                            data.result === btn.value ? btn.active : 'border-gray-200 bg-gray-50 text-gray-500 hover:border-gray-400'
                                        }`}
                                    >
                                        {btn.label}
                                    </button>
                                ))}
                            </div>
                            {errors.result && <p className="text-xs text-red-500 mt-2">{errors.result}</p>}
                        </div>
                    )}

                    {/* ⑥ フォローアップ */}
                    {data.process_step && (
                        <div className="bg-white border border-gray-200 rounded-xl p-5 mb-6">
                            <p className="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">⑥ フォローアップ <span className="font-normal text-gray-400">（任意）</span></p>
                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="text-xs text-gray-500 mb-1 block">次回アクション予定日</label>
                                    <input
                                        type="date"
                                        value={data.followup_date}
                                        onChange={e => setData('followup_date', e.target.value)}
                                        className="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400"
                                    />
                                </div>
                                <div>
                                    <label className="text-xs text-gray-500 mb-1 block">アクション内容</label>
                                    <input
                                        type="text"
                                        value={data.followup_description}
                                        onChange={e => setData('followup_description', e.target.value)}
                                        placeholder="例：競合比較表を作成して再訪"
                                        className="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400"
                                    />
                                </div>
                            </div>
                        </div>
                    )}

                    {/* 送信ボタン */}
                    <div className="flex gap-3 justify-end">
                        <button
                            type="button"
                            onClick={e => handleSubmit(e as unknown as FormEvent, true)}
                            disabled={processing}
                            className="px-5 py-2.5 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50"
                        >
                            下書き保存
                        </button>
                        <button
                            type="submit"
                            disabled={processing}
                            className="px-6 py-2.5 text-sm font-medium text-white bg-[#185FA5] rounded-lg hover:bg-[#0C447C] transition-colors disabled:opacity-50"
                        >
                            {processing ? '保存中...' : '保存する'}
                        </button>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
