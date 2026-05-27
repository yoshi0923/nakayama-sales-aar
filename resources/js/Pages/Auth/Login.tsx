import { Head } from '@inertiajs/react';

interface Props {
    errors?: { message?: string };
}

export default function Login({ errors }: Props) {
    return (
        <>
            <Head title="ログイン" />
            <div className="min-h-screen flex items-center justify-center bg-gray-50">
                <div className="max-w-md w-full mx-auto px-4">
                    <div className="text-center mb-8">
                        <h1 className="text-2xl font-semibold text-gray-800 mb-1">
                            営業AARシステム
                        </h1>
                        <p className="text-sm text-gray-500">
                            中山鉄工所 営業プロセス振り返り・分析・評価システム
                        </p>
                    </div>

                    <div className="bg-white border border-gray-200 rounded-xl p-8 shadow-sm">
                        {errors?.message && (
                            <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                                {errors.message}
                            </div>
                        )}

                        <p className="text-sm text-gray-600 mb-6 text-center">
                            会社のMicrosoftアカウントでログインしてください。
                        </p>

                        <a
                            href={route('azure.redirect')}
                            className="w-full flex items-center justify-center gap-3 px-4 py-3 bg-[#185FA5] hover:bg-[#0C447C] text-white font-medium rounded-lg transition-colors"
                        >
                            {/* Microsoft ロゴ */}
                            <svg width="20" height="20" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10 0H0v10h10V0z" fill="#F25022"/>
                                <path d="M21 0H11v10h10V0z" fill="#7FBA00"/>
                                <path d="M10 11H0v10h10V11z" fill="#00A4EF"/>
                                <path d="M21 11H11v10h10V11z" fill="#FFB900"/>
                            </svg>
                            Microsoftアカウントでログイン
                        </a>

                        <p className="mt-6 text-xs text-center text-gray-400">
                            ※ 中山鉄工所のMicrosoft 365アカウントをお持ちの方のみご利用いただけます
                        </p>
                    </div>
                </div>
            </div>
        </>
    );
}
