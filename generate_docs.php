<?php
/**
 * 中山営業AARシステム ドキュメント生成スクリプト
 * 実行: php generate_docs.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\JcTable;
use PhpOffice\PhpWord\Style\Table;

$phpWord = new PhpWord();
$phpWord->setDefaultFontName('MS Gothic');
$phpWord->setDefaultFontSize(10.5);

// ── スタイル定義 ────────────────────────────────────────────
$phpWord->addTitleStyle(1, ['bold' => true, 'size' => 18, 'color' => '185FA5', 'name' => 'MS Gothic']);
$phpWord->addTitleStyle(2, ['bold' => true, 'size' => 14, 'color' => '1e3a5f', 'name' => 'MS Gothic']);
$phpWord->addTitleStyle(3, ['bold' => true, 'size' => 11.5, 'color' => '374151', 'name' => 'MS Gothic']);
$phpWord->addTitleStyle(4, ['bold' => true, 'size' => 10.5, 'color' => '374151', 'name' => 'MS Gothic']);

$h1Font   = ['bold' => true, 'size' => 18, 'color' => '185FA5', 'name' => 'MS Gothic'];
$h2Font   = ['bold' => true, 'size' => 14, 'color' => '1e3a5f', 'name' => 'MS Gothic'];
$h3Font   = ['bold' => true, 'size' => 11.5, 'name' => 'MS Gothic'];
$h4Font   = ['bold' => true, 'size' => 10.5, 'name' => 'MS Gothic'];
$bodyFont = ['size' => 10.5, 'name' => 'MS Gothic'];
$noteFont = ['size' => 9.5, 'color' => '6b7280', 'name' => 'MS Gothic', 'italic' => true];
$boldBody = ['bold' => true, 'size' => 10.5, 'name' => 'MS Gothic'];

$indentPara  = ['indent' => 360, 'spaceBefore' => 60, 'spaceAfter' => 60];
$normalPara  = ['spaceBefore' => 80, 'spaceAfter' => 80];
$tightPara   = ['spaceBefore' => 40, 'spaceAfter' => 40];
$h2Para      = ['spaceBefore' => 320, 'spaceAfter' => 120];
$h3Para      = ['spaceBefore' => 240, 'spaceAfter' => 80];

$tableStyle = [
    'borderSize' => 6, 'borderColor' => 'e5e7eb',
    'cellMarginTop' => 80, 'cellMarginBottom' => 80,
    'cellMarginLeft' => 120, 'cellMarginRight' => 120,
];
$theadFont = ['bold' => true, 'size' => 10, 'color' => 'ffffff', 'name' => 'MS Gothic'];
$theadCell = ['bgColor' => '185FA5'];
$theadCell2 = ['bgColor' => '374151'];
$altCell   = ['bgColor' => 'f8fafc'];

// ── ヘルパー ────────────────────────────────────────────────
function addH2(object $s, string $text, array $para = []): void {
    global $h2Font, $h2Para;
    $s->addText($text, $h2Font, array_merge($h2Para, $para));
}
function addH3(object $s, string $text): void {
    global $h3Font, $h3Para;
    $s->addText($text, $h3Font, $h3Para);
}
function addH4(object $s, string $text): void {
    global $h4Font, $tightPara;
    $s->addText($text, $h4Font, $tightPara);
}
function addBody(object $s, string $text, array $para = []): void {
    global $bodyFont, $normalPara;
    $s->addText($text, $bodyFont, array_merge($normalPara, $para));
}
function addNote(object $s, string $text): void {
    global $noteFont, $indentPara;
    $s->addText('※ ' . $text, $noteFont, $indentPara);
}
function addBullet(object $s, string $text, int $depth = 0): void {
    global $bodyFont;
    $indent = 360 * ($depth + 1);
    $s->addListItem($text, $depth, $bodyFont, ['listType' => \PhpOffice\PhpWord\Style\ListItem::TYPE_BULLET_FILLED]);
}
function addTR(object $table, array $cells, array $widths, bool $isHead = false, bool $isAlt = false): void {
    global $bodyFont, $boldBody, $theadFont, $theadCell, $altCell;
    $row = $table->addRow();
    foreach ($cells as $i => $text) {
        $cellStyle = $isHead ? $theadCell : ($isAlt ? $altCell : []);
        $font = $isHead ? $theadFont : $bodyFont;
        $cell = $row->addCell($widths[$i] ?? 2000, $cellStyle);
        $cell->addText($text, $font);
    }
}
function addTR2(object $table, array $cells, array $widths): void {
    global $bodyFont, $theadFont, $theadCell2;
    $row = $table->addRow();
    foreach ($cells as $i => $text) {
        $cell = $row->addCell($widths[$i] ?? 2000, $theadCell2);
        $cell->addText($text, $theadFont);
    }
}

// ========================================================
// 表紙
// ========================================================
$cover = $phpWord->addSection(['paperSize' => 'A4', 'marginTop' => 2800, 'marginBottom' => 1400]);
$cover->addText('', [], ['spaceBefore' => 2000]);
$cover->addText('中山営業AARシステム', ['bold' => true, 'size' => 28, 'color' => '185FA5', 'name' => 'MS Gothic'], ['alignment' => 'center', 'spaceBefore' => 0]);
$cover->addTextBreak(1);
$cover->addText('操作説明書 / システム仕様書', ['bold' => true, 'size' => 18, 'color' => '374151', 'name' => 'MS Gothic'], ['alignment' => 'center']);
$cover->addTextBreak(3);
$cover->addText('バージョン：1.0', $bodyFont, ['alignment' => 'center']);
$cover->addText('作成日：' . date('Y年m月d日'), $bodyFont, ['alignment' => 'center']);
$cover->addText('対象：中山鉄工所 全営業担当', $bodyFont, ['alignment' => 'center']);

// ========================================================
// PART 1：操作説明書
// ========================================================
$s = $phpWord->addSection(['paperSize' => 'A4', 'marginTop' => 1400, 'marginBottom' => 1400, 'marginLeft' => 1400, 'marginRight' => 1400]);

$s->addText('PART 1　操作説明書', $h1Font, ['spaceBefore' => 0, 'spaceAfter' => 200]);

// ── 1. システム概要 ──────────────────────────────────────────
addH2($s, '1. システム概要');
addBody($s, '本システム「中山営業AARシステム」は、営業担当者が商談活動の振り返り（After Action Review: AAR）を記録・共有・分析するための社内 Web システムです。Microsoft Azure AD（SSO）で認証を行い、ブラウザのみで利用できます。');
$s->addTextBreak(1);

addH3($s, '主な機能');
addBullet($s, 'AAR記録：商談活動の4問（目標・結果・原因・改善）を入力し、プロセスステップ・成否を記録');
addBullet($s, 'マイ記録：自分の過去記録を一覧・フィルタで確認');
addBullet($s, 'チームDB：全員の記録を横断検索・閲覧');
addBullet($s, '分析ダッシュボード：KPI・受注推移・ファネル・部署別・メンバー別をグラフで可視化');
addBullet($s, '取引先マスター：案件に紐づく取引先の登録・編集・削除');
addBullet($s, '管理（admin専用）：部署・社員の登録・編集・削除・ロール変更');
$s->addTextBreak(1);

addH3($s, 'アクセス URL');
addBody($s, '本番URL：http://localhost:8001　（本番環境では別URLに変更）');

// ── 2. ロールと権限 ──────────────────────────────────────────
addH2($s, '2. ロールと権限');
addBody($s, '全ユーザーは以下の4種類のロールのいずれかを持ちます。ロールは管理者が管理ページから変更できます。');
$s->addTextBreak(1);

$tbl = $s->addTable(array_merge($tableStyle, ['width' => 9200, 'unit' => 'dxa']));
addTR($tbl, ['ロール', '表示名', '説明'], [1200, 1500, 6500], true);
$rows = [
    ['admin',   '管理者',        '全機能利用可。部署・社員・取引先の管理、分析閲覧。'],
    ['manager', 'マネージャー',  '管理ページ以外の全機能。分析・チームDB・取引先管理が可能。'],
    ['sales',   '営業',          'AAR記録・マイ記録・チームDB・分析・取引先管理が可能。'],
    ['viewer',  '閲覧者',        'AAR記録の新規作成は不可。ダッシュボード・チームDB・分析の閲覧のみ。'],
];
foreach ($rows as $i => $row) {
    addTR($tbl, $row, [1200, 1500, 6500], false, $i % 2 === 1);
}
$s->addTextBreak(1);

addH3($s, 'メニュー表示とロール');
$tbl2 = $s->addTable(array_merge($tableStyle, ['width' => 9200, 'unit' => 'dxa']));
addTR($tbl2, ['メニュー', 'admin', 'manager', 'sales', 'viewer'], [3200, 1500, 1500, 1500, 1500], true);
$menuRows = [
    ['ダッシュボード', '○', '○', '○', '○'],
    ['AAR記録',        '○', '○', '○', '×'],
    ['マイ記録',       '○', '○', '○', '○'],
    ['チームDB',       '○', '○', '○', '○'],
    ['分析',           '○', '○', '○', '○'],
    ['取引先',         '○', '○', '○', '○'],
    ['管理',           '○', '×', '×', '×'],
];
foreach ($menuRows as $i => $row) {
    addTR($tbl2, $row, [3200, 1500, 1500, 1500, 1500], false, $i % 2 === 1);
}

// ── 3. ログイン・ログアウト ────────────────────────────────────
addH2($s, '3. ログイン・ログアウト');
addH3($s, 'ログイン手順');
addBullet($s, 'ブラウザでシステム URL にアクセスすると、ログイン画面が表示されます。');
addBullet($s, '「Microsoft アカウントでログイン」ボタンをクリックします。');
addBullet($s, 'Microsoft の認証画面（Azure AD）でメールアドレスとパスワードを入力します。');
addBullet($s, '認証完了後、ダッシュボードに自動遷移します。');
addNote($s, '初回ログイン時はアカウントが自動登録されます。ロールは初期値「sales」で登録されます。必要に応じて管理者がロールを変更してください。');

$s->addTextBreak(1);
addH3($s, 'ログアウト手順');
addBullet($s, '画面右上のユーザー名横にある「ログアウト」ボタンをクリックします。');
addBullet($s, 'ログイン画面に戻ります。');

// ── 4. ダッシュボード ────────────────────────────────────────
addH2($s, '4. ダッシュボード');
addBody($s, 'ログイン後に表示されるトップページです。自分の活動状況とチームの動きが一目で確認できます。');
$s->addTextBreak(1);

addH3($s, '表示内容');
$tbl3 = $s->addTable(array_merge($tableStyle, ['width' => 9200, 'unit' => 'dxa']));
addTR($tbl3, ['ウィジェット', '内容'], [3000, 6200], true);
$dashRows = [
    ['今月の KPI',           '今月の AAR カード総数・成功件数・失敗件数・成功率'],
    ['フォローアップアラート', '期日が3日以内または期日超過のフォローアップを赤・黄で表示'],
    ['直近のAAR記録',         '自分の最新5件の記録カードを表示'],
    ['チームランキング',       'マネージャー以上には今月の成功件数ランキングを表示'],
    ['クイックアクション',     '「AAR記録を入力」ボタンからすぐに入力フォームへ遷移'],
];
foreach ($dashRows as $i => $row) {
    addTR($tbl3, $row, [3000, 6200], false, $i % 2 === 1);
}

// ── 5. AAR記録 ───────────────────────────────────────────────
addH2($s, '5. AAR記録（入力）');
addBody($s, 'ヘッダーの「AAR記録」メニューまたはダッシュボードのクイックアクションボタンから入力します。入力は6つのステップで構成されます。');
$s->addTextBreak(1);

addH3($s, '入力ステップ');
$tbl4 = $s->addTable(array_merge($tableStyle, ['width' => 9200, 'unit' => 'dxa']));
addTR($tbl4, ['ステップ', '項目', '説明'], [1200, 2500, 5500], true);
$stepRows = [
    ['①', '案件選択',         '既存の進行中案件を選択、または「新規案件として登録」を選択します。'],
    ['②', 'プロセスステップ', 'STEP1（ヒアリング）/ STEP2（提案〜フォロー）/ STEP3（受注〜売上）から選択します。'],
    ['③', '活動日・金額',     '活動日（必須）・見込金額・確定金額（STEP2以降）を入力します。'],
    ['④', 'AAR 4問',          '①目標 ②実績・結果 ③うまくいった点・課題 ④次回改善アクションを記入します。'],
    ['⑤', '結果',             '「成功」「失敗」「継続中」のいずれかを選択します。'],
    ['⑥', 'フォローアップ',   '次回予定のアクション内容と期日を設定します（任意）。'],
];
foreach ($stepRows as $i => $row) {
    addTR($tbl4, $row, [1200, 2500, 5500], false, $i % 2 === 1);
}
$s->addTextBreak(1);

addH3($s, '保存');
addBullet($s, '「下書き保存」：入力中のデータを一時保存します。後から続きを入力できます。');
addBullet($s, '「提出する」：記録を確定し、チームに公開されます。');
addNote($s, '案件を「失敗」または STEP3「成功」で提出すると、案件ステータスが自動更新されます。');
addNote($s, '新規案件として登録した場合、案件マスターに自動追加されます。');

// ── 6. マイ記録 ────────────────────────────────────────────
addH2($s, '6. マイ記録');
addBody($s, '自分が入力した AAR 記録の一覧ページです。');
$s->addTextBreak(1);

addH3($s, '機能');
addBullet($s, '記録一覧：日付・取引先・プロセスステップ・結果・下書き状態を表示');
addBullet($s, 'フィルタ：プロセスステップ・結果・下書き・期間で絞り込み');
addBullet($s, 'ページネーション：20件ずつ表示');
addBullet($s, 'KPI統計：自分の累計件数・成功率・プロセス別成功率グラフ');
addBullet($s, '記録削除：自分の記録を削除できます（管理者は全件削除可能）');

// ── 7. チームDB ────────────────────────────────────────────
addH2($s, '7. チームDB');
addBody($s, '全メンバーの確定済み AAR 記録を横断的に検索・閲覧できます。');
$s->addTextBreak(1);

addH3($s, '検索・フィルタ');
addBullet($s, 'キーワード検索：担当者名・取引先名・案件名でフリーワード検索');
addBullet($s, 'プロセスステップ・結果・期間で絞り込み');
addBullet($s, '20件/ページのページネーション付き');
$s->addTextBreak(1);

addH3($s, '記録カード');
addBullet($s, '各カードに記入者・部署・プロセス・日付・結果・AAR4問の内容が表示されます。');

// ── 8. 分析ダッシュボード ──────────────────────────────────
addH2($s, '8. 分析ダッシュボード');
addBody($s, '全ロールが閲覧できる BI ツールです。チーム全体のパフォーマンスを多角的に分析できます。');
$s->addTextBreak(1);

addH3($s, '表示ウィジェット');
$tbl5 = $s->addTable(array_merge($tableStyle, ['width' => 9200, 'unit' => 'dxa']));
addTR($tbl5, ['ウィジェット', '説明'], [3000, 6200], true);
$anaRows = [
    ['KPIカード（4枚）',         '総AARカード数・全体成功率・参加メンバー数・累計受注金額'],
    ['受注金額推移',             '年度切替ボタン付き。月次受注棒グラフ＋累計折れ線の複合チャート'],
    ['プロセス別成功率',         'STEP1〜3 各ステップの件数と成功率をプログレスバーで表示'],
    ['案件ファネル',             'ヒアリング→提案→受注→完了の件数を横棒グラフで視覚化'],
    ['月次AARカード推移',        '直近12ヶ月のSTEP別積み上げ棒グラフ＋成功/失敗の折れ線'],
    ['部署別受注金額',           '部署ごとの受注金額横棒グラフ（ホバーで件数も表示）'],
    ['部署別プロセス内訳テーブル', '部署×STEP別カード数と成功率'],
    ['メンバー別成績ランキング', '全メンバーの総件数・成功件数・成功率（成功件数順）'],
];
foreach ($anaRows as $i => $row) {
    addTR($tbl5, $row, [3000, 6200], false, $i % 2 === 1);
}
$s->addTextBreak(1);
addNote($s, '受注金額は「受注完了（won）」ステータスの案件の確定金額を集計しています。');

// ── 9. 取引先マスター管理 ──────────────────────────────────
addH2($s, '9. 取引先マスター管理');
addBody($s, 'ヘッダーの「取引先」メニューから操作します。全ロールが利用できます。');
$s->addTextBreak(1);

addH3($s, '機能');
addBullet($s, '取引先一覧：取引先名・フリガナ・エリア・担当者・業種・状態を一覧表示');
addBullet($s, '新規登録：「＋ 新規登録」ボタンからモーダルを開いて登録');
addBullet($s, '編集：各行の「編集」ボタンでモーダルを開いて更新');
addBullet($s, '削除：案件が紐づいていない取引先を削除');
addBullet($s, '「無効を表示」チェックで有効フラグが OFF の取引先も表示');
$s->addTextBreak(1);

addH3($s, '入力項目');
$tbl6 = $s->addTable(array_merge($tableStyle, ['width' => 9200, 'unit' => 'dxa']));
addTR($tbl6, ['項目', '必須', '説明'], [2500, 800, 5900], true);
$clientRows = [
    ['取引先名',       '必須', '正式な取引先名称'],
    ['フリガナ',       '任意', 'カタカナ表記'],
    ['エリア',         '任意', 'マスターに登録されたエリアから選択'],
    ['状態',           '任意', '有効 / 無効（無効にすると AAR 入力の選択肢から非表示）'],
    ['担当者名',       '任意', '取引先窓口の担当者名'],
    ['電話番号',       '任意', '取引先の電話番号'],
    ['メールアドレス', '任意', '担当者のメールアドレス'],
    ['業種',           '任意', '製造業・建設業 など'],
    ['備考',           '任意', '自由記述'],
];
foreach ($clientRows as $i => $row) {
    addTR($tbl6, $row, [2500, 800, 5900], false, $i % 2 === 1);
}

// ── 10. 管理（admin 専用）──────────────────────────────────
addH2($s, '10. 管理（admin 専用）');
addBody($s, 'ヘッダーの「管理」メニューは admin ロールのみ表示されます。部署管理と社員管理の2タブで構成されます。');
$s->addTextBreak(1);

addH3($s, '部署管理タブ');
addBullet($s, '部署一覧：部署名・コード・表示順・所属人数・状態を表示');
addBullet($s, '新規登録・編集：部署名（必須）・コード（必須・一意）・表示順・状態を設定');
addBullet($s, '削除：所属社員がいない部署のみ削除可能');
$s->addTextBreak(1);

addH3($s, '社員管理タブ');
addBullet($s, '社員一覧：氏名・メール・ロール・部署・最終ログイン・状態を表示');
addBullet($s, '新規登録：氏名・メール・ロール・部署・状態を設定（Azure SSO 初回ログイン時に紐づけ）');
addBullet($s, '編集：ロール変更・部署変更・有効/無効の切り替え');
addBullet($s, '削除：自分自身は削除不可。「無効を表示」チェックで無効社員も表示');
addNote($s, '自分自身のロールを admin 以外に変更することはできません。');
addNote($s, '「無効」にした社員はログインできなくなりますが、記録データは保持されます。');

// ========================================================
// PART 2：システム仕様書
// ========================================================
$s2 = $phpWord->addSection(['paperSize' => 'A4', 'marginTop' => 1400, 'marginBottom' => 1400, 'marginLeft' => 1400, 'marginRight' => 1400]);

$s2->addText('PART 2　システム仕様書', $h1Font, ['spaceBefore' => 0, 'spaceAfter' => 200]);

// ── 11. 技術構成 ──────────────────────────────────────────
addH2($s2, '11. 技術構成');
$tbl7 = $s2->addTable(array_merge($tableStyle, ['width' => 9200, 'unit' => 'dxa']));
addTR($tbl7, ['カテゴリ', '技術/バージョン', '用途'], [2000, 2500, 4700], true);
$techRows = [
    ['バックエンド', 'PHP 8.2 / Laravel 12', 'APIルーティング・ビジネスロジック・DB操作'],
    ['フロントエンド', 'React 18 / TypeScript 5', 'SPA コンポーネント、型安全な実装'],
    ['SSR/SPA 連携', 'Inertia.js 2.0', 'Laravel ↔ React のサーバーサイドルーティング'],
    ['スタイル', 'Tailwind CSS 3', 'ユーティリティファーストCSS'],
    ['グラフ', 'Recharts', '分析ダッシュボードのチャートライブラリ'],
    ['ビルド', 'Vite 7', 'フロントエンドバンドル・HMR'],
    ['認証', 'Microsoft Azure AD (SSO)', 'Laravel Socialite + socialiteproviders/microsoft-azure'],
    ['DB', 'MySQL 8.0+', 'RDS (本番) / XAMPP (ローカル開発)'],
    ['ルート生成', 'Ziggy (tightenco/ziggy)', 'PHP ルートを JavaScript で利用'],
    ['ドキュメント生成', 'PHPOffice/PhpWord', '本ドキュメントの生成'],
];
foreach ($techRows as $i => $row) {
    addTR($tbl7, $row, [2000, 2500, 4700], false, $i % 2 === 1);
}

// ── 12. DB テーブル定義 ──────────────────────────────────
addH2($s2, '12. データベーステーブル定義');

$tables = [
    [
        'name' => 'users（ユーザー）',
        'cols' => [
            ['id',            'bigint', 'PK', '自動採番'],
            ['azure_id',      'varchar(255)', 'nullable, unique', 'Microsoft Entra ID オブジェクトID'],
            ['name',          'varchar(255)', 'NOT NULL', '氏名'],
            ['email',         'varchar(255)', 'NOT NULL, unique', 'メールアドレス'],
            ['role',          "enum('admin','manager','sales','viewer')", 'default: sales', 'ロール'],
            ['department_id', 'bigint', 'nullable, FK', '部署ID（departments.id）'],
            ['is_active',     'tinyint(1)', 'default: 1', '有効フラグ'],
            ['last_login_at', 'timestamp', 'nullable', '最終ログイン日時'],
            ['deleted_at',    'timestamp', 'nullable', 'ソフトデリート'],
            ['created_at / updated_at', 'timestamp', '', '作成・更新日時'],
        ],
    ],
    [
        'name' => 'departments（部署）',
        'cols' => [
            ['id',         'bigint', 'PK', '自動採番'],
            ['name',       'varchar(255)', 'NOT NULL', '部署名'],
            ['code',       'varchar(255)', 'NOT NULL, unique', '部署コード'],
            ['sort_order', 'int', 'default: 0', '表示順'],
            ['is_active',  'tinyint(1)', 'default: 1', '有効フラグ'],
            ['created_at / updated_at', 'timestamp', '', '作成・更新日時'],
        ],
    ],
    [
        'name' => 'areas（エリア）',
        'cols' => [
            ['id',         'bigint', 'PK', '自動採番'],
            ['name',       'varchar(255)', 'NOT NULL', 'エリア名'],
            ['code',       'varchar(255)', 'NOT NULL, unique', 'エリアコード'],
            ['sort_order', 'int', 'default: 0', '表示順'],
            ['is_active',  'tinyint(1)', 'default: 1', '有効フラグ'],
        ],
    ],
    [
        'name' => 'clients（取引先）',
        'cols' => [
            ['id',            'bigint', 'PK', '自動採番'],
            ['name',          'varchar(255)', 'NOT NULL', '取引先名'],
            ['name_kana',     'varchar(255)', 'nullable', 'フリガナ'],
            ['area_id',       'bigint', 'nullable, FK', 'エリアID'],
            ['contact_name',  'varchar(255)', 'nullable', '担当者名'],
            ['contact_email', 'varchar(255)', 'nullable', '担当者メール'],
            ['contact_phone', 'varchar(255)', 'nullable', '電話番号'],
            ['industry',      'varchar(255)', 'nullable', '業種'],
            ['notes',         'text', 'nullable', '備考'],
            ['is_active',     'tinyint(1)', 'default: 1', '有効フラグ'],
            ['deleted_at',    'timestamp', 'nullable', 'ソフトデリート'],
        ],
    ],
    [
        'name' => 'opportunities（案件）',
        'cols' => [
            ['id',                 'bigint', 'PK', '自動採番'],
            ['opportunity_no',     'varchar(255)', 'NOT NULL, unique', '案件番号（自動採番）'],
            ['title',              'varchar(255)', 'NOT NULL', '案件名'],
            ['client_id',          'bigint', 'nullable, FK', '取引先ID'],
            ['contact_name',       'varchar(255)', 'nullable', '取引先担当者名'],
            ['area_id',            'bigint', 'nullable, FK', 'エリアID'],
            ['opportunity_type_id','bigint', 'nullable, FK', '案件種別ID'],
            ['assigned_user_id',   'bigint', 'NOT NULL, FK', '担当者ユーザーID'],
            ['department_id',      'bigint', 'nullable, FK', '担当部署ID'],
            ['estimated_amount',   'decimal(15,2)', 'nullable', '見込金額'],
            ['confirmed_amount',   'decimal(15,2)', 'nullable', '確定金額'],
            ['fiscal_year',        'int', 'NOT NULL', '会計年度'],
            ['status',             "enum('active','won','lost','hold')", 'default: active', '案件ステータス'],
            ['current_process',    'tinyint', 'default: 1', '現在のプロセスステップ'],
            ['deleted_at',         'timestamp', 'nullable', 'ソフトデリート'],
        ],
    ],
    [
        'name' => 'aar_records（AAR記録）',
        'cols' => [
            ['id',             'bigint', 'PK', '自動採番'],
            ['opportunity_id', 'bigint', 'NOT NULL, FK', '案件ID'],
            ['user_id',        'bigint', 'NOT NULL, FK', '記入者ユーザーID'],
            ['process_step',   'tinyint', 'NOT NULL', 'プロセスステップ（1/2/3）'],
            ['activity_date',  'date', 'NOT NULL', '活動日'],
            ['result',         "enum('success','failure','ongoing')", 'nullable', '結果'],
            ['q1_goal',        'text', 'NOT NULL', '①目標'],
            ['q2_result',      'text', 'NOT NULL', '②実績・結果'],
            ['q3_cause',       'text', 'NOT NULL', '③原因・振り返り'],
            ['q4_action',      'text', 'NOT NULL', '④次回改善アクション'],
            ['is_draft',       'tinyint(1)', 'default: 0', '下書きフラグ'],
            ['submitted_at',   'timestamp', 'nullable', '提出日時'],
            ['deleted_at',     'timestamp', 'nullable', 'ソフトデリート'],
        ],
    ],
    [
        'name' => 'followup_actions（フォローアップ）',
        'cols' => [
            ['id',                 'bigint', 'PK', '自動採番'],
            ['aar_record_id',      'bigint', 'NOT NULL, FK', 'AAR記録ID'],
            ['opportunity_id',     'bigint', 'NOT NULL, FK', '案件ID'],
            ['user_id',            'bigint', 'NOT NULL, FK', 'ユーザーID'],
            ['action_description', 'text', 'NOT NULL', 'アクション内容'],
            ['scheduled_date',     'date', 'NOT NULL', '予定日'],
            ['status',             "enum('pending','completed','cancelled')", 'default: pending', 'ステータス'],
        ],
    ],
];

foreach ($tables as $tblDef) {
    addH3($s2, $tblDef['name']);
    $t = $s2->addTable(array_merge($tableStyle, ['width' => 9200, 'unit' => 'dxa']));
    addTR($t, ['カラム名', 'データ型', '制約', '説明'], [2000, 2500, 2000, 2700], true);
    foreach ($tblDef['cols'] as $i => $col) {
        addTR($t, $col, [2000, 2500, 2000, 2700], false, $i % 2 === 1);
    }
    $s2->addTextBreak(1);
}

// ── 13. ルート一覧 ──────────────────────────────────────────
addH2($s2, '13. ルート一覧');
$tbl8 = $s2->addTable(array_merge($tableStyle, ['width' => 9200, 'unit' => 'dxa']));
addTR($tbl8, ['メソッド', 'URL', 'ルート名', '説明', '権限'], [900, 2500, 2300, 2300, 1200], true);
$routes = [
    ['GET',    '/',                          '—',                       'ルートリダイレクト',           '全員'],
    ['GET',    '/login',                     'login',                   'ログイン画面',                 'ゲスト'],
    ['GET',    '/auth/azure/redirect',       'azure.redirect',          'Azure SSO リダイレクト',       '全員'],
    ['GET',    '/auth/azure/callback',       'azure.callback',          'Azure SSO コールバック',       '全員'],
    ['POST',   '/logout',                    'logout',                  'ログアウト',                   '認証済'],
    ['GET',    '/dashboard',                 'dashboard',               'ダッシュボード',               '認証済'],
    ['GET',    '/aar/create',                'aar.create',              'AAR入力フォーム',              '認証済'],
    ['POST',   '/aar',                       'aar.store',               'AAR保存',                      '認証済'],
    ['GET',    '/aar/{id}',                  'aar.show',                'AAR詳細（JSON）',              '認証済'],
    ['DELETE', '/aar/{id}',                  'aar.destroy',             'AAR削除',                      '認証済'],
    ['GET',    '/my-records',                'my-records.index',        'マイ記録',                     '認証済'],
    ['GET',    '/team-db',                   'team-db.index',           'チームDB',                     '認証済'],
    ['GET',    '/analysis',                  'analysis.index',          '分析ダッシュボード',           '認証済'],
    ['GET',    '/master/clients',            'master.clients.index',    '取引先一覧',                   '認証済'],
    ['POST',   '/master/clients',            'master.clients.store',    '取引先登録',                   '認証済'],
    ['PUT',    '/master/clients/{id}',       'master.clients.update',   '取引先更新',                   '認証済'],
    ['DELETE', '/master/clients/{id}',       'master.clients.destroy',  '取引先削除',                   '認証済'],
    ['GET',    '/admin',                     'admin.index',             '管理トップ',                   'admin'],
    ['POST',   '/admin/departments',         'admin.departments.store', '部署登録',                     'admin'],
    ['PUT',    '/admin/departments/{id}',    'admin.departments.update','部署更新',                     'admin'],
    ['DELETE', '/admin/departments/{id}',    'admin.departments.destroy','部署削除',                    'admin'],
    ['POST',   '/admin/users',               'admin.users.store',       '社員登録',                     'admin'],
    ['PUT',    '/admin/users/{id}',          'admin.users.update',      '社員更新',                     'admin'],
    ['DELETE', '/admin/users/{id}',          'admin.users.destroy',     '社員削除',                     'admin'],
];
foreach ($routes as $i => $row) {
    addTR($tbl8, $row, [900, 2500, 2300, 2300, 1200], false, $i % 2 === 1);
}

// ── 14. ロール・権限マトリクス ────────────────────────────
addH2($s2, '14. ロール・権限マトリクス');
$tbl9 = $s2->addTable(array_merge($tableStyle, ['width' => 9200, 'unit' => 'dxa']));
addTR($tbl9, ['操作', 'admin', 'manager', 'sales', 'viewer'], [4000, 1300, 1300, 1300, 1300], true);
$permRows = [
    ['ダッシュボード閲覧',          '○', '○', '○', '○'],
    ['AAR記録 作成・保存',          '○', '○', '○', '×'],
    ['自分の記録 削除',             '○', '○', '○', '×'],
    ['他人の記録 削除',             '○', '×', '×', '×'],
    ['チームDB 閲覧',               '○', '○', '○', '○'],
    ['分析ダッシュボード 閲覧',     '○', '○', '○', '○'],
    ['取引先 閲覧',                 '○', '○', '○', '○'],
    ['取引先 登録・編集・削除',     '○', '○', '○', '○'],
    ['管理ページ アクセス',         '○', '×', '×', '×'],
    ['部署 登録・編集・削除',       '○', '×', '×', '×'],
    ['社員 登録・編集・削除',       '○', '×', '×', '×'],
    ['社員 ロール変更',             '○', '×', '×', '×'],
];
foreach ($permRows as $i => $row) {
    addTR($tbl9, $row, [4000, 1300, 1300, 1300, 1300], false, $i % 2 === 1);
}

// ── フッター ────────────────────────────────────────────────
$s2->addTextBreak(2);
$s2->addText('本ドキュメントは ' . date('Y年m月d日') . ' 時点の仕様に基づいています。', $noteFont, ['alignment' => 'center']);
$s2->addText('中山鉄工所 内部資料　©' . date('Y') . ' Nakayama Iron Works', $noteFont, ['alignment' => 'center']);

// ========================================================
// 保存
// ========================================================
$outputPath = __DIR__ . '/中山営業AARシステム_ドキュメント.docx';
$writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
$writer->save($outputPath);

echo "✅ 生成完了: " . $outputPath . PHP_EOL;
echo "   ファイルサイズ: " . round(filesize($outputPath) / 1024) . " KB" . PHP_EOL;
