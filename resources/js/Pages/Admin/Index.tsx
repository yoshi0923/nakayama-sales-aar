import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps, Role } from '@/types';
import { router, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';

interface DeptItem {
    id: number;
    name: string;
    code: string;
    sort_order: number;
    is_active: boolean;
    users_count: number;
}

interface UserItem {
    id: number;
    name: string;
    email: string;
    role: Role;
    department_id: number | null;
    department_name: string | null;
    is_active: boolean;
    last_login_at: string | null;
}

interface Props extends PageProps {
    departments: DeptItem[];
    users: UserItem[];
}

type DeptForm = { name: string; code: string; sort_order: number; is_active: boolean };
type UserForm = { name: string; email: string; role: string; department_id: number | string; is_active: boolean };

const ROLE_LABELS: Record<Role, string> = {
    admin: '管理者',
    manager: 'マネージャー',
    sales: '営業',
    viewer: '閲覧者',
};

const ROLE_COLORS: Record<Role, string> = {
    admin:   'bg-red-50 text-red-700',
    manager: 'bg-blue-50 text-blue-700',
    sales:   'bg-green-50 text-green-700',
    viewer:  'bg-gray-100 text-gray-500',
};

function Flash() {
    const { flash } = usePage<PageProps>().props;
    if (!flash) return null;
    return (
        <>
            {flash.success && (
                <div className="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                    {flash.success}
                </div>
            )}
            {flash.error && (
                <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                    {flash.error}
                </div>
            )}
        </>
    );
}

export default function Index({ departments, users }: Props) {
    const { auth } = usePage<PageProps>().props;
    const me = auth.user;
    const [tab, setTab] = useState<'departments' | 'users'>('departments');
    const [showInactiveUsers, setShowInactiveUsers] = useState(false);

    // ── 部署 state ─────────────────────────────────────────────
    const [deptModal, setDeptModal] = useState<{ mode: 'create' | 'edit'; item?: DeptItem } | null>(null);
    const [deptDelete, setDeptDelete] = useState<DeptItem | null>(null);
    const deptForm = useForm<DeptForm>({ name: '', code: '', sort_order: 0, is_active: true });

    const openDeptCreate = () => {
        deptForm.reset();
        deptForm.clearErrors();
        setDeptModal({ mode: 'create' });
    };

    const openDeptEdit = (item: DeptItem) => {
        deptForm.clearErrors();
        deptForm.setData({ name: item.name, code: item.code, sort_order: item.sort_order, is_active: item.is_active });
        setDeptModal({ mode: 'edit', item });
    };

    const submitDept = () => {
        if (deptModal?.mode === 'create') {
            deptForm.post(route('admin.departments.store'), { onSuccess: () => setDeptModal(null) });
        } else if (deptModal?.mode === 'edit' && deptModal.item) {
            deptForm.put(route('admin.departments.update', deptModal.item.id), { onSuccess: () => setDeptModal(null) });
        }
    };

    // ── 社員 state ─────────────────────────────────────────────
    const [userModal, setUserModal] = useState<{ mode: 'create' | 'edit'; item?: UserItem } | null>(null);
    const [userDelete, setUserDelete] = useState<UserItem | null>(null);
    const userForm = useForm<UserForm>({ name: '', email: '', role: 'sales', department_id: '', is_active: true });

    const openUserCreate = () => {
        userForm.reset();
        userForm.clearErrors();
        setUserModal({ mode: 'create' });
    };

    const openUserEdit = (item: UserItem) => {
        userForm.clearErrors();
        userForm.setData({
            name: item.name,
            email: item.email,
            role: item.role,
            department_id: item.department_id ?? '',
            is_active: item.is_active,
        });
        setUserModal({ mode: 'edit', item });
    };

    const submitUser = () => {
        if (userModal?.mode === 'create') {
            userForm.post(route('admin.users.store'), { onSuccess: () => setUserModal(null) });
        } else if (userModal?.mode === 'edit' && userModal.item) {
            userForm.put(route('admin.users.update', userModal.item.id), { onSuccess: () => setUserModal(null) });
        }
    };

    const filteredUsers = showInactiveUsers ? users : users.filter(u => u.is_active);

    const tabClass = (t: string) =>
        tab === t
            ? 'px-4 py-2 text-sm font-medium border-b-2 border-[#185FA5] text-[#185FA5]'
            : 'px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700';

    return (
        <AuthenticatedLayout header={<h1 className="text-lg font-semibold text-gray-800">管理</h1>}>
            <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <Flash />

                {/* タブ */}
                <div className="flex border-b border-gray-200 mb-6">
                    <button onClick={() => setTab('departments')} className={tabClass('departments')}>
                        部署管理
                    </button>
                    <button onClick={() => setTab('users')} className={tabClass('users')}>
                        社員管理
                    </button>
                </div>

                {/* ── 部署タブ ── */}
                {tab === 'departments' && (
                    <>
                        <div className="flex justify-end mb-4">
                            <button onClick={openDeptCreate}
                                className="px-4 py-2 text-sm text-white rounded-lg bg-[#185FA5] hover:bg-[#0C447C] transition-colors">
                                ＋ 部署を追加
                            </button>
                        </div>
                        <div className="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                            <table className="w-full text-sm">
                                <thead className="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th className="text-left px-4 py-3 text-xs font-medium text-gray-500">部署名</th>
                                        <th className="text-left px-4 py-3 text-xs font-medium text-gray-500">コード</th>
                                        <th className="text-center px-4 py-3 text-xs font-medium text-gray-500">表示順</th>
                                        <th className="text-center px-4 py-3 text-xs font-medium text-gray-500">所属人数</th>
                                        <th className="text-center px-4 py-3 text-xs font-medium text-gray-500">状態</th>
                                        <th className="px-4 py-3 w-24"></th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100">
                                    {departments.length === 0 ? (
                                        <tr><td colSpan={6} className="px-4 py-10 text-center text-gray-400 text-sm">部署がありません</td></tr>
                                    ) : departments.map(d => (
                                        <tr key={d.id} className="hover:bg-gray-50">
                                            <td className="px-4 py-3 font-medium text-gray-800">{d.name}</td>
                                            <td className="px-4 py-3 text-gray-500 font-mono text-xs">{d.code}</td>
                                            <td className="px-4 py-3 text-center text-gray-600">{d.sort_order}</td>
                                            <td className="px-4 py-3 text-center text-gray-600">{d.users_count} 名</td>
                                            <td className="px-4 py-3 text-center">
                                                {d.is_active
                                                    ? <span className="text-xs px-2 py-0.5 rounded-full bg-green-50 text-green-700">有効</span>
                                                    : <span className="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">無効</span>}
                                            </td>
                                            <td className="px-4 py-3 text-right whitespace-nowrap">
                                                <button onClick={() => openDeptEdit(d)}
                                                    className="text-xs text-blue-600 hover:text-blue-800 mr-3">編集</button>
                                                <button onClick={() => setDeptDelete(d)}
                                                    className="text-xs text-red-500 hover:text-red-700">削除</button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        <p className="mt-2 text-xs text-gray-400">{departments.length} 件</p>
                    </>
                )}

                {/* ── 社員タブ ── */}
                {tab === 'users' && (
                    <>
                        <div className="flex items-center justify-between mb-4">
                            <label className="flex items-center gap-2 text-sm text-gray-600 cursor-pointer select-none">
                                <input type="checkbox" checked={showInactiveUsers}
                                    onChange={e => setShowInactiveUsers(e.target.checked)}
                                    className="rounded border-gray-300 text-blue-600" />
                                無効を表示
                            </label>
                            <button onClick={openUserCreate}
                                className="px-4 py-2 text-sm text-white rounded-lg bg-[#185FA5] hover:bg-[#0C447C] transition-colors">
                                ＋ 社員を追加
                            </button>
                        </div>
                        <div className="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                            <table className="w-full text-sm">
                                <thead className="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th className="text-left px-4 py-3 text-xs font-medium text-gray-500">氏名</th>
                                        <th className="text-left px-4 py-3 text-xs font-medium text-gray-500">メール</th>
                                        <th className="text-left px-4 py-3 text-xs font-medium text-gray-500">ロール</th>
                                        <th className="text-left px-4 py-3 text-xs font-medium text-gray-500">部署</th>
                                        <th className="text-left px-4 py-3 text-xs font-medium text-gray-500">最終ログイン</th>
                                        <th className="text-center px-4 py-3 text-xs font-medium text-gray-500">状態</th>
                                        <th className="px-4 py-3 w-24"></th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100">
                                    {filteredUsers.length === 0 ? (
                                        <tr><td colSpan={7} className="px-4 py-10 text-center text-gray-400 text-sm">社員がいません</td></tr>
                                    ) : filteredUsers.map(u => (
                                        <tr key={u.id} className="hover:bg-gray-50">
                                            <td className="px-4 py-3 font-medium text-gray-800">
                                                {u.name}
                                                {u.id === me.id && <span className="ml-1 text-xs text-gray-400">(自分)</span>}
                                            </td>
                                            <td className="px-4 py-3 text-gray-500 text-xs">{u.email}</td>
                                            <td className="px-4 py-3">
                                                <span className={`text-xs px-2 py-0.5 rounded-full ${ROLE_COLORS[u.role]}`}>
                                                    {ROLE_LABELS[u.role]}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-gray-600">{u.department_name || '—'}</td>
                                            <td className="px-4 py-3 text-gray-500 text-xs">{u.last_login_at || '未ログイン'}</td>
                                            <td className="px-4 py-3 text-center">
                                                {u.is_active
                                                    ? <span className="text-xs px-2 py-0.5 rounded-full bg-green-50 text-green-700">有効</span>
                                                    : <span className="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">無効</span>}
                                            </td>
                                            <td className="px-4 py-3 text-right whitespace-nowrap">
                                                <button onClick={() => openUserEdit(u)}
                                                    className="text-xs text-blue-600 hover:text-blue-800 mr-3">編集</button>
                                                <button onClick={() => setUserDelete(u)}
                                                    disabled={u.id === me.id}
                                                    className="text-xs text-red-500 hover:text-red-700 disabled:opacity-30 disabled:cursor-not-allowed">
                                                    削除
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        <p className="mt-2 text-xs text-gray-400">{filteredUsers.length} 件</p>
                    </>
                )}
            </div>

            {/* ── 部署モーダル ── */}
            {deptModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                    <div className="bg-white rounded-xl shadow-xl w-full max-w-md">
                        <div className="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                            <h2 className="font-semibold text-gray-800">
                                {deptModal.mode === 'create' ? '部署を新規登録' : '部署を編集'}
                            </h2>
                            <button onClick={() => setDeptModal(null)} className="text-gray-400 hover:text-gray-600 text-lg leading-none">✕</button>
                        </div>
                        <div className="px-6 py-4 space-y-4">
                            <div>
                                <label className="block text-xs font-medium text-gray-600 mb-1">
                                    部署名 <span className="text-red-500">*</span>
                                </label>
                                <input type="text" value={deptForm.data.name}
                                    onChange={e => deptForm.setData('name', e.target.value)}
                                    className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                                    placeholder="例：大阪営業部" />
                                {deptForm.errors.name && <p className="mt-1 text-xs text-red-500">{deptForm.errors.name}</p>}
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-medium text-gray-600 mb-1">
                                        コード <span className="text-red-500">*</span>
                                    </label>
                                    <input type="text" value={deptForm.data.code}
                                        onChange={e => deptForm.setData('code', e.target.value)}
                                        className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none font-mono"
                                        placeholder="例：OSAKA" />
                                    {deptForm.errors.code && <p className="mt-1 text-xs text-red-500">{deptForm.errors.code}</p>}
                                </div>
                                <div>
                                    <label className="block text-xs font-medium text-gray-600 mb-1">表示順</label>
                                    <input type="number" min={0} value={deptForm.data.sort_order}
                                        onChange={e => deptForm.setData('sort_order', parseInt(e.target.value) || 0)}
                                        className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" />
                                </div>
                            </div>
                            <div>
                                <label className="block text-xs font-medium text-gray-600 mb-1">状態</label>
                                <select value={deptForm.data.is_active ? '1' : '0'}
                                    onChange={e => deptForm.setData('is_active', e.target.value === '1')}
                                    className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                                    <option value="1">有効</option>
                                    <option value="0">無効</option>
                                </select>
                            </div>
                        </div>
                        <div className="flex justify-end gap-3 px-6 py-4 border-t border-gray-100">
                            <button onClick={() => setDeptModal(null)}
                                className="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                                キャンセル
                            </button>
                            <button onClick={submitDept} disabled={deptForm.processing}
                                className="px-4 py-2 text-sm text-white rounded-lg bg-[#185FA5] hover:bg-[#0C447C] disabled:opacity-50 transition-colors">
                                {deptForm.processing ? '保存中...' : (deptModal.mode === 'create' ? '登録する' : '更新する')}
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* ── 社員モーダル ── */}
            {userModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                    <div className="bg-white rounded-xl shadow-xl w-full max-w-md">
                        <div className="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                            <h2 className="font-semibold text-gray-800">
                                {userModal.mode === 'create' ? '社員を新規登録' : '社員情報を編集'}
                            </h2>
                            <button onClick={() => setUserModal(null)} className="text-gray-400 hover:text-gray-600 text-lg leading-none">✕</button>
                        </div>
                        <div className="px-6 py-4 space-y-4">
                            <div>
                                <label className="block text-xs font-medium text-gray-600 mb-1">
                                    氏名 <span className="text-red-500">*</span>
                                </label>
                                <input type="text" value={userForm.data.name}
                                    onChange={e => userForm.setData('name', e.target.value)}
                                    className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                                    placeholder="例：山田 太郎" />
                                {userForm.errors.name && <p className="mt-1 text-xs text-red-500">{userForm.errors.name}</p>}
                            </div>
                            <div>
                                <label className="block text-xs font-medium text-gray-600 mb-1">
                                    メールアドレス <span className="text-red-500">*</span>
                                </label>
                                <input type="email" value={userForm.data.email}
                                    onChange={e => userForm.setData('email', e.target.value)}
                                    className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                                    placeholder="yamada@nakayamairon.co.jp" />
                                {userForm.errors.email && <p className="mt-1 text-xs text-red-500">{userForm.errors.email}</p>}
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-medium text-gray-600 mb-1">
                                        ロール <span className="text-red-500">*</span>
                                    </label>
                                    <select value={userForm.data.role}
                                        onChange={e => userForm.setData('role', e.target.value)}
                                        disabled={userModal.item?.id === me.id}
                                        className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none disabled:bg-gray-50 disabled:text-gray-400">
                                        <option value="admin">管理者</option>
                                        <option value="manager">マネージャー</option>
                                        <option value="sales">営業</option>
                                        <option value="viewer">閲覧者</option>
                                    </select>
                                    {userModal.item?.id === me.id && (
                                        <p className="mt-1 text-xs text-gray-400">自分のロールは変更できません</p>
                                    )}
                                </div>
                                <div>
                                    <label className="block text-xs font-medium text-gray-600 mb-1">状態</label>
                                    <select value={userForm.data.is_active ? '1' : '0'}
                                        onChange={e => userForm.setData('is_active', e.target.value === '1')}
                                        className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                                        <option value="1">有効</option>
                                        <option value="0">無効</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label className="block text-xs font-medium text-gray-600 mb-1">部署</label>
                                <select value={userForm.data.department_id}
                                    onChange={e => userForm.setData('department_id', e.target.value ? Number(e.target.value) : '')}
                                    className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                                    <option value="">未設定</option>
                                    {departments.filter(d => d.is_active).map(d => (
                                        <option key={d.id} value={d.id}>{d.name}</option>
                                    ))}
                                </select>
                            </div>
                        </div>
                        <div className="flex justify-end gap-3 px-6 py-4 border-t border-gray-100">
                            <button onClick={() => setUserModal(null)}
                                className="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                                キャンセル
                            </button>
                            <button onClick={submitUser} disabled={userForm.processing}
                                className="px-4 py-2 text-sm text-white rounded-lg bg-[#185FA5] hover:bg-[#0C447C] disabled:opacity-50 transition-colors">
                                {userForm.processing ? '保存中...' : (userModal.mode === 'create' ? '登録する' : '更新する')}
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* ── 部署削除確認 ── */}
            {deptDelete && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                    <div className="bg-white rounded-xl shadow-xl w-full max-w-sm p-6">
                        <h2 className="font-semibold text-gray-800 mb-2">部署を削除しますか？</h2>
                        <p className="text-sm text-gray-500 mb-6">
                            「{deptDelete.name}」を削除します。<br />
                            所属社員がいる場合は削除できません。
                        </p>
                        <div className="flex justify-end gap-3">
                            <button onClick={() => setDeptDelete(null)}
                                className="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                                キャンセル
                            </button>
                            <button onClick={() => {
                                router.delete(route('admin.departments.destroy', deptDelete.id), {
                                    onSuccess: () => setDeptDelete(null),
                                });
                            }} className="px-4 py-2 text-sm text-white bg-red-500 hover:bg-red-600 rounded-lg transition-colors">
                                削除する
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* ── 社員削除確認 ── */}
            {userDelete && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                    <div className="bg-white rounded-xl shadow-xl w-full max-w-sm p-6">
                        <h2 className="font-semibold text-gray-800 mb-2">社員を削除しますか？</h2>
                        <p className="text-sm text-gray-500 mb-6">
                            「{userDelete.name}」を削除します。<br />
                            この操作は取り消せません。
                        </p>
                        <div className="flex justify-end gap-3">
                            <button onClick={() => setUserDelete(null)}
                                className="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                                キャンセル
                            </button>
                            <button onClick={() => {
                                router.delete(route('admin.users.destroy', userDelete.id), {
                                    onSuccess: () => setUserDelete(null),
                                });
                            }} className="px-4 py-2 text-sm text-white bg-red-500 hover:bg-red-600 rounded-lg transition-colors">
                                削除する
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
