<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class AzureAuthController extends Controller
{
    /**
     * Microsoft 認証画面へリダイレクト
     */
    public function redirect()
    {
        return Socialite::driver('azure')->redirect();
    }

    /**
     * Microsoft からのコールバック処理
     */
    public function callback()
    {
        try {
            $azureUser = Socialite::driver('azure')->user();
        } catch (\Exception $e) {
            Log::error('Azure OAuth error', [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
            ]);
            return redirect()->route('login')->withErrors(['message' => '認証に失敗しました。再度お試しください。']);
        }

        // azure_id でユーザーを検索、なければ自動登録
        $user = User::withTrashed()->where('azure_id', $azureUser->getId())->first()
            ?? User::where('email', $azureUser->getEmail())->first();

        if ($user && $user->trashed()) {
            return redirect()->route('login')->withErrors(['message' => 'このアカウントは無効化されています。管理者にお問い合わせください。']);
        }

        if ($user && !$user->is_active) {
            return redirect()->route('login')->withErrors(['message' => 'このアカウントは無効化されています。管理者にお問い合わせください。']);
        }

        if (!$user) {
            // 初回ログイン：自動登録（初期ロール: sales）
            $user = User::create([
                'azure_id' => $azureUser->getId(),
                'name'     => $azureUser->getName(),
                'email'    => $azureUser->getEmail(),
                'role'     => 'sales',
            ]);

            AuditLog::record('create', $user->id, 'users', $user->id, null, $user->toArray());
        } else {
            // 既存ユーザー：azure_id・名前を最新化
            $user->update([
                'azure_id' => $azureUser->getId(),
                'name'     => $azureUser->getName(),
            ]);
        }

        $user->update(['last_login_at' => now()]);

        Auth::login($user, true);

        AuditLog::record('login', $user->id);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * ログアウト
     */
    public function logout()
    {
        $userId = auth()->id();
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        AuditLog::record('logout', $userId);

        return redirect()->route('login');
    }
}
