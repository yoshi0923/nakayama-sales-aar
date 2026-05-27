import NavLink from '@/Components/NavLink';
import { Link, router, usePage } from '@inertiajs/react';
import { PropsWithChildren, ReactNode, useState } from 'react';
import { PageProps } from '@/types';

export default function Authenticated({ header, children }: PropsWithChildren<{ header?: ReactNode }>) {
    const { auth } = usePage<PageProps>().props;
    const user = auth.user;
    const [mobileOpen, setMobileOpen] = useState(false);

    const navItems = [
        { label: 'ダッシュボード', route: 'dashboard' },
        { label: 'AAR記録', route: 'aar.create' },
        { label: 'マイ記録', route: 'my-records.index' },
        { label: 'チームDB', route: 'team-db.index' },
        ...(user.role !== 'sales' ? [{ label: '分析', route: 'analysis.index' }] : []),
        ...(user.role === 'admin' ? [{ label: '管理', route: 'admin.index' }] : []),
    ];

    return (
        <div className="min-h-screen bg-gray-50">
            <nav className="bg-white border-b border-gray-200">
                <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex h-14 items-center justify-between">
                        {/* ロゴ + ナビ */}
                        <div className="flex items-center gap-6">
                            <Link href={route('dashboard')} className="text-sm font-bold text-gray-800 whitespace-nowrap">
                                営業AAR
                            </Link>
                            <div className="hidden sm:flex gap-1">
                                {navItems.map(item => (
                                    <NavLink
                                        key={item.route}
                                        href={route(item.route)}
                                        active={route().current(item.route)}
                                    >
                                        {item.label}
                                    </NavLink>
                                ))}
                            </div>
                        </div>

                        {/* ユーザーメニュー */}
                        <div className="hidden sm:flex items-center gap-3">
                            <span className="text-sm text-gray-500">{user.name}</span>
                            <span className="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">{user.role}</span>
                            <button
                                onClick={() => router.post(route('logout'))}
                                className="text-xs text-gray-500 hover:text-gray-700 px-2 py-1 rounded hover:bg-gray-100 transition-colors"
                            >
                                ログアウト
                            </button>
                        </div>

                        {/* モバイルメニューボタン */}
                        <button
                            onClick={() => setMobileOpen(v => !v)}
                            className="sm:hidden p-2 text-gray-500 rounded-lg hover:bg-gray-100"
                        >
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                                    d={mobileOpen ? 'M6 18L18 6M6 6l12 12' : 'M4 6h16M4 12h16M4 18h16'} />
                            </svg>
                        </button>
                    </div>
                </div>

                {/* モバイルメニュー */}
                {mobileOpen && (
                    <div className="sm:hidden border-t border-gray-100 py-2 px-4 space-y-1">
                        {navItems.map(item => (
                            <Link
                                key={item.route}
                                href={route(item.route)}
                                className="block py-2 text-sm text-gray-700 hover:text-gray-900"
                                onClick={() => setMobileOpen(false)}
                            >
                                {item.label}
                            </Link>
                        ))}
                        <div className="pt-2 border-t border-gray-100 flex items-center justify-between">
                            <span className="text-xs text-gray-400">{user.name}</span>
                            <button
                                onClick={() => router.post(route('logout'))}
                                className="text-xs text-gray-500 hover:text-gray-700"
                            >
                                ログアウト
                            </button>
                        </div>
                    </div>
                )}
            </nav>

            {header && (
                <header className="bg-white border-b border-gray-100">
                    <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                        {header}
                    </div>
                </header>
            )}

            <main>{children}</main>
        </div>
    );
}
