import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { router, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';

interface ClientItem {
    id: number;
    name: string;
    name_kana: string | null;
    area_id: number | null;
    area_name: string | null;
    contact_name: string | null;
    contact_email: string | null;
    contact_phone: string | null;
    industry: string | null;
    notes: string | null;
    is_active: boolean;
}

interface Props extends PageProps {
    clients: ClientItem[];
    areas: { id: number; name: string }[];
}

type FormData = {
    name: string;
    name_kana: string;
    area_id: number | string;
    contact_name: string;
    contact_email: string;
    contact_phone: string;
    industry: string;
    notes: string;
    is_active: boolean;
};

const blankForm: FormData = {
    name: '',
    name_kana: '',
    area_id: '',
    contact_name: '',
    contact_email: '',
    contact_phone: '',
    industry: '',
    notes: '',
    is_active: true,
};

export default function Clients({ clients, areas }: Props) {
    const { flash } = usePage<PageProps>().props;
    const [modal, setModal] = useState<{ mode: 'create' | 'edit'; client?: ClientItem } | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<ClientItem | null>(null);
    const [showInactive, setShowInactive] = useState(false);

    const form = useForm<FormData>(blankForm);

    const openCreate = () => {
        form.reset();
        form.clearErrors();
        setModal({ mode: 'create' });
    };

    const openEdit = (client: ClientItem) => {
        form.clearErrors();
        form.setData({
            name: client.name,
            name_kana: client.name_kana ?? '',
            area_id: client.area_id ?? '',
            contact_name: client.contact_name ?? '',
            contact_email: client.contact_email ?? '',
            contact_phone: client.contact_phone ?? '',
            industry: client.industry ?? '',
            notes: client.notes ?? '',
            is_active: client.is_active,
        });
        setModal({ mode: 'edit', client });
    };

    const handleSubmit = () => {
        if (modal?.mode === 'create') {
            form.post(route('master.clients.store'), {
                onSuccess: () => setModal(null),
            });
        } else if (modal?.mode === 'edit' && modal.client) {
            form.put(route('master.clients.update', modal.client.id), {
                onSuccess: () => setModal(null),
            });
        }
    };

    const handleDelete = () => {
        if (!deleteTarget) return;
        router.delete(route('master.clients.destroy', deleteTarget.id), {
            onSuccess: () => setDeleteTarget(null),
        });
    };

    const filtered = showInactive ? clients : clients.filter(c => c.is_active);

    return (
        <AuthenticatedLayout header={<h1 className="text-lg font-semibold text-gray-800">取引先マスター</h1>}>
            <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

                {flash?.success && (
                    <div className="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                        {flash.success}
                    </div>
                )}
                {flash?.error && (
                    <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                        {flash.error}
                    </div>
                )}

                <div className="flex items-center justify-between mb-4">
                    <label className="flex items-center gap-2 text-sm text-gray-600 cursor-pointer select-none">
                        <input
                            type="checkbox"
                            checked={showInactive}
                            onChange={e => setShowInactive(e.target.checked)}
                            className="rounded border-gray-300 text-blue-600"
                        />
                        無効を表示
                    </label>
                    <button
                        onClick={openCreate}
                        className="px-4 py-2 text-sm text-white rounded-lg bg-[#185FA5] hover:bg-[#0C447C] transition-colors"
                    >
                        ＋ 新規登録
                    </button>
                </div>

                <div className="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <table className="w-full text-sm">
                        <thead className="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th className="text-left px-4 py-3 text-xs font-medium text-gray-500">取引先名</th>
                                <th className="text-left px-4 py-3 text-xs font-medium text-gray-500">フリガナ</th>
                                <th className="text-left px-4 py-3 text-xs font-medium text-gray-500">エリア</th>
                                <th className="text-left px-4 py-3 text-xs font-medium text-gray-500">担当者</th>
                                <th className="text-left px-4 py-3 text-xs font-medium text-gray-500">業種</th>
                                <th className="text-center px-4 py-3 text-xs font-medium text-gray-500">状態</th>
                                <th className="px-4 py-3 w-24"></th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100">
                            {filtered.length === 0 ? (
                                <tr>
                                    <td colSpan={7} className="px-4 py-10 text-center text-gray-400 text-sm">
                                        取引先がありません
                                    </td>
                                </tr>
                            ) : filtered.map(client => (
                                <tr key={client.id} className="hover:bg-gray-50">
                                    <td className="px-4 py-3 font-medium text-gray-800">{client.name}</td>
                                    <td className="px-4 py-3 text-gray-500">{client.name_kana || '—'}</td>
                                    <td className="px-4 py-3 text-gray-600">{client.area_name || '—'}</td>
                                    <td className="px-4 py-3 text-gray-600">{client.contact_name || '—'}</td>
                                    <td className="px-4 py-3 text-gray-600">{client.industry || '—'}</td>
                                    <td className="px-4 py-3 text-center">
                                        {client.is_active ? (
                                            <span className="text-xs px-2 py-0.5 rounded-full bg-green-50 text-green-700">有効</span>
                                        ) : (
                                            <span className="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">無効</span>
                                        )}
                                    </td>
                                    <td className="px-4 py-3 text-right whitespace-nowrap">
                                        <button
                                            onClick={() => openEdit(client)}
                                            className="text-xs text-blue-600 hover:text-blue-800 mr-3"
                                        >
                                            編集
                                        </button>
                                        <button
                                            onClick={() => setDeleteTarget(client)}
                                            className="text-xs text-red-500 hover:text-red-700"
                                        >
                                            削除
                                        </button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
                <p className="mt-2 text-xs text-gray-400">{filtered.length} 件</p>
            </div>

            {/* 新規登録 / 編集モーダル */}
            {modal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                    <div className="bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] flex flex-col">
                        <div className="flex items-center justify-between px-6 py-4 border-b border-gray-100 shrink-0">
                            <h2 className="font-semibold text-gray-800">
                                {modal.mode === 'create' ? '取引先を新規登録' : '取引先を編集'}
                            </h2>
                            <button onClick={() => setModal(null)} className="text-gray-400 hover:text-gray-600 text-lg leading-none">✕</button>
                        </div>

                        <div className="px-6 py-4 space-y-4 overflow-y-auto">
                            <div>
                                <label className="block text-xs font-medium text-gray-600 mb-1">
                                    取引先名 <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    value={form.data.name}
                                    onChange={e => form.setData('name', e.target.value)}
                                    className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                                    placeholder="例：株式会社○○製作所"
                                />
                                {form.errors.name && <p className="mt-1 text-xs text-red-500">{form.errors.name}</p>}
                            </div>

                            <div>
                                <label className="block text-xs font-medium text-gray-600 mb-1">フリガナ</label>
                                <input
                                    type="text"
                                    value={form.data.name_kana}
                                    onChange={e => form.setData('name_kana', e.target.value)}
                                    className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                                    placeholder="カブシキガイシャ〇〇セイサクショ"
                                />
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-medium text-gray-600 mb-1">エリア</label>
                                    <select
                                        value={form.data.area_id}
                                        onChange={e => form.setData('area_id', e.target.value ? Number(e.target.value) : '')}
                                        className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                                    >
                                        <option value="">未設定</option>
                                        {areas.map(a => <option key={a.id} value={a.id}>{a.name}</option>)}
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-xs font-medium text-gray-600 mb-1">状態</label>
                                    <select
                                        value={form.data.is_active ? '1' : '0'}
                                        onChange={e => form.setData('is_active', e.target.value === '1')}
                                        className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                                    >
                                        <option value="1">有効</option>
                                        <option value="0">無効</option>
                                    </select>
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-medium text-gray-600 mb-1">担当者名</label>
                                    <input
                                        type="text"
                                        value={form.data.contact_name}
                                        onChange={e => form.setData('contact_name', e.target.value)}
                                        className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                                        placeholder="山田 太郎"
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs font-medium text-gray-600 mb-1">電話番号</label>
                                    <input
                                        type="text"
                                        value={form.data.contact_phone}
                                        onChange={e => form.setData('contact_phone', e.target.value)}
                                        className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                                        placeholder="06-0000-0000"
                                    />
                                </div>
                            </div>

                            <div>
                                <label className="block text-xs font-medium text-gray-600 mb-1">メールアドレス</label>
                                <input
                                    type="email"
                                    value={form.data.contact_email}
                                    onChange={e => form.setData('contact_email', e.target.value)}
                                    className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                                    placeholder="yamada@example.com"
                                />
                                {form.errors.contact_email && <p className="mt-1 text-xs text-red-500">{form.errors.contact_email}</p>}
                            </div>

                            <div>
                                <label className="block text-xs font-medium text-gray-600 mb-1">業種</label>
                                <input
                                    type="text"
                                    value={form.data.industry}
                                    onChange={e => form.setData('industry', e.target.value)}
                                    className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                                    placeholder="製造業"
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-medium text-gray-600 mb-1">備考</label>
                                <textarea
                                    value={form.data.notes}
                                    onChange={e => form.setData('notes', e.target.value)}
                                    rows={3}
                                    className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none resize-none"
                                />
                            </div>
                        </div>

                        <div className="flex justify-end gap-3 px-6 py-4 border-t border-gray-100 shrink-0">
                            <button
                                onClick={() => setModal(null)}
                                className="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50"
                            >
                                キャンセル
                            </button>
                            <button
                                onClick={handleSubmit}
                                disabled={form.processing}
                                className="px-4 py-2 text-sm text-white rounded-lg bg-[#185FA5] hover:bg-[#0C447C] disabled:opacity-50 transition-colors"
                            >
                                {form.processing ? '保存中...' : (modal.mode === 'create' ? '登録する' : '更新する')}
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* 削除確認ダイアログ */}
            {deleteTarget && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                    <div className="bg-white rounded-xl shadow-xl w-full max-w-sm p-6">
                        <h2 className="font-semibold text-gray-800 mb-2">取引先を削除しますか？</h2>
                        <p className="text-sm text-gray-500 mb-6">
                            「{deleteTarget.name}」を削除します。<br />
                            案件が紐づいている場合は削除できません。
                        </p>
                        <div className="flex justify-end gap-3">
                            <button
                                onClick={() => setDeleteTarget(null)}
                                className="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50"
                            >
                                キャンセル
                            </button>
                            <button
                                onClick={handleDelete}
                                className="px-4 py-2 text-sm text-white bg-red-500 hover:bg-red-600 rounded-lg transition-colors"
                            >
                                削除する
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
