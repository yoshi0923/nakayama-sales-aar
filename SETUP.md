# sales_aar セットアップ手順

## 前提条件
- XAMPP（MySQL）が起動していること
- PHP 8.2+, Composer, Node.js 18+ がインストール済み

---

## ① Azure AD アプリ登録（IT管理者が実施）

1. [Azure Portal](https://portal.azure.com) → Microsoft Entra ID → アプリの登録 → 新規登録
2. 名前：`中山鉄工所 営業AARシステム`
3. サポートされるアカウントの種類：**この組織ディレクトリのみのアカウント**
4. リダイレクトURI（Web）：`http://localhost:8001/auth/azure/callback`（本番は本番URLに変更）
5. 証明書とシークレット → 新しいクライアントシークレット → 値をコピー
6. APIのアクセス許可 → Microsoft Graph → `User.Read` が付与されていることを確認
7. 取得した値を `.env` に設定：

```env
AZURE_CLIENT_ID=（アプリケーション(クライアント)ID）
AZURE_CLIENT_SECRET=（クライアントシークレットの値）
AZURE_TENANT_ID=（ディレクトリ(テナント)ID）
```

---

## ② データベース作成

XAMPPのphpMyAdmin（http://localhost/phpmyadmin）で以下を実行：

```sql
CREATE DATABASE sales_aar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

または MySQL コマンドラインで：
```bash
mysql -u giken -p -e "CREATE DATABASE sales_aar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

---

## ③ Laravelセットアップ

```bash
cd C:\Users\KITAGAWA_Y\claude\sales_aar

# マイグレーション実行
php artisan migrate

# マスターデータ投入（部署・エリア・案件種別）
php artisan db:seed

# キャッシュクリア
php artisan config:clear
php artisan route:clear
```

---

## ④ 開発サーバー起動

ターミナルを2つ開いて：

**ターミナル1（Laravel）:**
```bash
cd C:\Users\KITAGAWA_Y\claude\sales_aar
php artisan serve --port=8001
```

**ターミナル2（Vite HMR）:**
```bash
cd C:\Users\KITAGAWA_Y\claude\sales_aar
npm run dev
```

ブラウザで http://localhost:8001 を開く。

---

## ⑤ 初回ログイン後の作業

1. ブラウザで http://localhost:8001 にアクセス
2. 「Microsoftアカウントでログイン」をクリック
3. 初回ログインで自動的にユーザー登録される（ロール: sales）
4. 管理者ロールの付与が必要な場合はDBで直接更新：
   ```sql
   UPDATE users SET role = 'admin' WHERE email = 'your-email@nakayamairon.co.jp';
   ```

---

## ⑥ 本番ビルド

```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## プロジェクト構成

```
sales_aar/
├── app/
│   ├── Http/Controllers/
│   │   ├── Auth/AzureAuthController.php    # Microsoft SSO認証
│   │   ├── AarRecordController.php         # AAR記録 CRUD
│   │   ├── AnalysisController.php          # 分析ダッシュボード
│   │   └── DashboardController.php         # ダッシュボード
│   └── Models/
│       ├── User.php                        # azure_id カラム（パスワードなし）
│       ├── Department.php                  # 部署・支店
│       ├── Area.php                        # エリア
│       ├── OpportunityType.php             # 案件種別
│       ├── Client.php                      # 取引先
│       ├── Opportunity.php                 # 案件
│       ├── AarRecord.php                   # AARレコード
│       ├── FollowupAction.php              # フォローアップ
│       └── AuditLog.php                    # 監査ログ
├── database/migrations/                    # 全テーブルのマイグレーション
├── resources/js/
│   ├── Pages/
│   │   ├── Auth/Login.tsx                  # Microsoftログインボタンのみ
│   │   ├── Dashboard.tsx                   # ダッシュボード
│   │   ├── Aar/Create.tsx                  # AAR入力フォーム
│   │   ├── MyRecords/Index.tsx             # マイ記録一覧
│   │   └── TeamDb/Index.tsx               # チームDB
│   ├── Components/Aar/RecordCard.tsx       # AARカードコンポーネント
│   └── types/index.ts                      # TypeScript型定義
└── routes/web.php                          # ルーティング（SSO含む）
```
