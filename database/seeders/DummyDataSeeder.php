<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\AarRecord;
use App\Models\Client;
use App\Models\Department;
use App\Models\FollowupAction;
use App\Models\Opportunity;
use App\Models\OpportunityType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // ── ①マスターデータ取得 ────────────────────────────────
        $deptHQ      = Department::where('code', 'HQ-SALES')->first();
        $deptTohoku  = Department::where('code', 'TOHOKU')->first();
        $deptKanto   = Department::where('code', 'KANTO')->first();
        $deptChubu   = Department::where('code', 'CHUBU')->first();
        $deptKansai  = Department::where('code', 'KANSAI')->first();
        $deptKyushu  = Department::where('code', 'KYUSHU')->first();

        $areaStone   = Area::where('code', 'CRUSHED-STONE')->first();
        $areaRecycle = Area::where('code', 'RECYCLE')->first();
        $areaOther   = Area::where('code', 'OTHER')->first();

        $typePlant   = OpportunityType::where('code', 'PLANT')->first();
        $typeProduct = OpportunityType::where('code', 'PRODUCT')->first();
        $typeGoods   = OpportunityType::where('code', 'GOODS')->first();
        $typeParts   = OpportunityType::where('code', 'PARTS')->first();
        $typeMaint   = OpportunityType::where('code', 'MAINTENANCE')->first();

        // ── ②ユーザー作成 ──────────────────────────────────────
        $users = [];

        $userData = [
            [
                'azure_id'      => 'aaaaaaaa-0001-0001-0001-000000000001',
                'name'          => '北川 裕二',
                'email'         => 'ntrd@nakayamairon.co.jp',
                'role'          => 'admin',
                'department_id' => $deptHQ->id,
            ],
            [
                'azure_id'      => 'aaaaaaaa-0002-0002-0002-000000000002',
                'name'          => '中山 健一',
                'email'         => 'nakayama.k@nakayamairon.co.jp',
                'role'          => 'admin',
                'department_id' => $deptHQ->id,
            ],
            [
                'azure_id'      => 'aaaaaaaa-0003-0003-0003-000000000003',
                'name'          => '山田 誠',
                'email'         => 'yamada.m@nakayamairon.co.jp',
                'role'          => 'manager',
                'department_id' => $deptHQ->id,
            ],
            [
                'azure_id'      => 'aaaaaaaa-0004-0004-0004-000000000004',
                'name'          => '田中 和彦',
                'email'         => 'tanaka.k@nakayamairon.co.jp',
                'role'          => 'manager',
                'department_id' => $deptKanto->id,
            ],
            [
                'azure_id'      => 'aaaaaaaa-0005-0005-0005-000000000005',
                'name'          => '佐藤 大輔',
                'email'         => 'sato.d@nakayamairon.co.jp',
                'role'          => 'sales',
                'department_id' => $deptHQ->id,
            ],
            [
                'azure_id'      => 'aaaaaaaa-0006-0006-0006-000000000006',
                'name'          => '鈴木 健太',
                'email'         => 'suzuki.k@nakayamairon.co.jp',
                'role'          => 'sales',
                'department_id' => $deptKanto->id,
            ],
            [
                'azure_id'      => 'aaaaaaaa-0007-0007-0007-000000000007',
                'name'          => '高橋 明',
                'email'         => 'takahashi.a@nakayamairon.co.jp',
                'role'          => 'sales',
                'department_id' => $deptTohoku->id,
            ],
            [
                'azure_id'      => 'aaaaaaaa-0008-0008-0008-000000000008',
                'name'          => '渡辺 正',
                'email'         => 'watanabe.t@nakayamairon.co.jp',
                'role'          => 'sales',
                'department_id' => $deptChubu->id,
            ],
            [
                'azure_id'      => 'aaaaaaaa-0009-0009-0009-000000000009',
                'name'          => '伊藤 恵子',
                'email'         => 'ito.k@nakayamairon.co.jp',
                'role'          => 'viewer',
                'department_id' => $deptHQ->id,
            ],
        ];

        foreach ($userData as $data) {
            $users[] = User::firstOrCreate(
                ['email' => $data['email']],
                array_merge($data, [
                    'is_active'     => true,
                    'last_login_at' => Carbon::now()->subDays(rand(0, 30)),
                ])
            );
        }

        // ── ③取引先作成 ──────────────────────────────────────────
        $clientData = [
            ['name' => '株式会社山岡産業',         'contact_name' => '山岡 信一', 'contact_email' => 'yamaoka@yamaoka-sangyo.co.jp',         'contact_phone' => '022-222-1111', 'industry' => '砕石・建材'],
            ['name' => '東北建設株式会社',         'contact_name' => '小林 隆',   'contact_email' => 'kobayashi@tohoku-kensetsu.co.jp',       'contact_phone' => '022-333-2222', 'industry' => '建設'],
            ['name' => '関東土木株式会社',         'contact_name' => '松本 哲',   'contact_email' => 'matsumoto@kanto-doboku.co.jp',          'contact_phone' => '03-4444-3333', 'industry' => '土木'],
            ['name' => '中部採石工業株式会社',     'contact_name' => '加藤 誠一', 'contact_email' => 'kato@chubu-saiseki.co.jp',              'contact_phone' => '052-555-4444', 'industry' => '採石'],
            ['name' => '阪神リサイクル株式会社',   'contact_name' => '木村 義一', 'contact_email' => 'kimura@hanshin-recycle.co.jp',          'contact_phone' => '06-6666-5555', 'industry' => 'リサイクル'],
            ['name' => '北九州資材株式会社',       'contact_name' => '橋本 弘',   'contact_email' => 'hashimoto@kitakyushu-shizai.co.jp',     'contact_phone' => '093-777-6666', 'industry' => '資材'],
            ['name' => '大和工業株式会社',         'contact_name' => '石田 正樹', 'contact_email' => 'ishida@yamato-kogyo.co.jp',             'contact_phone' => '045-888-7777', 'industry' => '工業'],
            ['name' => '太平洋建設株式会社',       'contact_name' => '前田 剛',   'contact_email' => 'maeda@taiheiyou-kensetsu.co.jp',        'contact_phone' => '03-9999-8888', 'industry' => '建設'],
            ['name' => '東亜産業株式会社',         'contact_name' => '後藤 健二', 'contact_email' => 'goto@toa-sangyo.co.jp',                 'contact_phone' => '022-000-9999', 'industry' => '産業機械'],
            ['name' => '日本砕石株式会社',         'contact_name' => '藤原 稔',   'contact_email' => 'fujiwara@nihon-saiseki.co.jp',          'contact_phone' => '011-111-0000', 'industry' => '砕石'],
            ['name' => '西日本リサイクル株式会社', 'contact_name' => '中村 幸雄', 'contact_email' => 'nakamura@nishi-recycle.co.jp',          'contact_phone' => '092-222-1111', 'industry' => 'リサイクル'],
            ['name' => '東日本建材株式会社',       'contact_name' => '岡田 義明', 'contact_email' => 'okada@higashi-kenzai.co.jp',            'contact_phone' => '022-333-2222', 'industry' => '建材'],
        ];

        $clients = [];
        foreach ($clientData as $data) {
            $clients[] = Client::firstOrCreate(['name' => $data['name']], $data);
        }

        // ── ④案件＆AARレコード作成 ─────────────────────────────
        // 担当営業のサブセット
        $salesUsers = array_filter($users, fn($u) => in_array($u->role, ['sales', 'manager']));
        $salesUsers = array_values($salesUsers);

        // 案件定義 [title, client_idx, area, type, dept, user_idx, estimated, confirmed, status, fiscal_year, process_step, records]
        $opportunityDefs = [
            // ── 受注済み（won）案件 ──────────────────────────
            [
                'title'     => '東北支店向け砕石プラント設備更新',
                'client'    => $clients[0], // 山岡産業
                'area'      => $areaStone,
                'type'      => $typePlant,
                'dept'      => $deptTohoku,
                'user'      => $users[6], // 高橋
                'estimated' => 45000000,
                'confirmed' => 42000000,
                'status'    => 'won',
                'fiscal'    => 2024,
                'process'   => 3,
                'records'   => [
                    ['step' => 1, 'date' => '2024-06-10', 'result' => 'success',
                        'q1' => '顧客の設備更新ニーズと予算規模を把握する',
                        'q2' => '設備の老朽化と更新予算4000万円規模を確認できた',
                        'q3' => '事前に業界紙で設備投資動向を調査し、具体的な提案資料を持参したことが効果的だった',
                        'q4' => '次回は技術仕様書を持参し、競合他社との差別化ポイントを詳細に説明する'],
                    ['step' => 2, 'date' => '2024-08-20', 'result' => 'success',
                        'q1' => '設備仕様と納期・保証条件について合意を得る',
                        'q2' => '仕様・納期ともに合意。価格交渉で5%値引きを要求された',
                        'q3' => '技術部門の同席が信頼感を高め、競合提案より優位に立てた。値引き要求は想定内だった',
                        'q4' => '次回の受注商談では保証条件の上積みを条件に値引き幅を最小化する'],
                    ['step' => 3, 'date' => '2024-10-05', 'result' => 'success',
                        'q1' => '契約締結と初回入金を完了させる',
                        'q2' => '契約締結・入金確認完了。4200万円で受注',
                        'q3' => '担当窓口との信頼関係が功を奏した。法務確認を先に済ませておいたことで締結がスムーズだった',
                        'q4' => '同社の第2工場案件も視野に、アフターフォローを継続的に実施する'],
                ],
                'followup'  => ['action' => '第2工場設備点検の打合せ設定', 'date' => '2025-02-01', 'status' => 'completed'],
            ],
            [
                'title'     => '関東地区リサイクル設備一式納入',
                'client'    => $clients[2], // 関東土木
                'area'      => $areaRecycle,
                'type'      => $typeProduct,
                'dept'      => $deptKanto,
                'user'      => $users[5], // 鈴木
                'estimated' => 18000000,
                'confirmed' => 17500000,
                'status'    => 'won',
                'fiscal'    => 2024,
                'process'   => 3,
                'records'   => [
                    ['step' => 1, 'date' => '2024-07-15', 'result' => 'success',
                        'q1' => 'リサイクル設備の導入ニーズと意思決定プロセスを確認する',
                        'q2' => '導入意向を確認。購買委員会での承認が必要と判明',
                        'q3' => '担当者との良好な関係が情報収集に役立った',
                        'q4' => '購買委員会向けの費用対効果資料を準備する'],
                    ['step' => 2, 'date' => '2024-09-10', 'result' => 'success',
                        'q1' => '購買委員会で承認を得る',
                        'q2' => '委員会で正式承認。競合2社を退け受注候補No.1に',
                        'q3' => 'コスト削減シミュレーション資料が評価された',
                        'q4' => '契約条件の詳細詰めを早期に実施する'],
                    ['step' => 3, 'date' => '2024-11-01', 'result' => 'success',
                        'q1' => '納品・検収完了まで完結させる',
                        'q2' => '納品・検収完了。1750万円受注確定',
                        'q3' => '納期を1週間前倒しできたことで顧客満足度が高かった',
                        'q4' => 'メンテナンス契約の提案タイミングを検討する'],
                ],
                'followup'  => ['action' => '定期メンテナンス契約の提案', 'date' => '2025-05-01', 'status' => 'pending'],
            ],
            // ── 商談中（active）案件 ──────────────────────────
            [
                'title'     => '中部採石プラント部品交換案件',
                'client'    => $clients[3], // 中部採石
                'area'      => $areaStone,
                'type'      => $typeParts,
                'dept'      => $deptChubu,
                'user'      => $users[7], // 渡辺
                'estimated' => 8500000,
                'confirmed' => null,
                'status'    => 'active',
                'fiscal'    => 2025,
                'process'   => 2,
                'records'   => [
                    ['step' => 1, 'date' => '2025-04-08', 'result' => 'success',
                        'q1' => '設備の現状把握と部品交換が必要な箇所を特定する',
                        'q2' => '主要3箇所の劣化を確認。推定交換費用850万円規模と算出',
                        'q3' => '技術スタッフを同行させたことで現地調査が詳細に実施できた',
                        'q4' => '見積書作成にあたり、技術部門と詳細仕様を詰める'],
                    ['step' => 2, 'date' => '2025-05-20', 'result' => 'ongoing',
                        'q1' => '見積提出と競合状況を確認し、優位性を固める',
                        'q2' => '見積提出済み。競合1社と比較検討中',
                        'q3' => '価格競争力より納期・品質保証を訴求することが効果的と判断',
                        'q4' => '次週中に技術部長を交えた詳細説明会を設定する'],
                ],
                'followup'  => ['action' => '技術部長向け説明会の日程調整', 'date' => '2025-06-05', 'status' => 'pending'],
            ],
            [
                'title'     => '阪神リサイクル設備メンテナンス契約',
                'client'    => $clients[4], // 阪神リサイクル
                'area'      => $areaRecycle,
                'type'      => $typeMaint,
                'dept'      => $deptKansai,
                'user'      => $users[4], // 佐藤
                'estimated' => 3600000,
                'confirmed' => null,
                'status'    => 'active',
                'fiscal'    => 2025,
                'process'   => 1,
                'records'   => [
                    ['step' => 1, 'date' => '2025-05-12', 'result' => 'ongoing',
                        'q1' => '定期メンテナンス契約の必要性と契約範囲を確認する',
                        'q2' => '担当者は関心を示しているが、決裁権は部長にある',
                        'q3' => '担当者レベルでの初訪だったため情報が限定的だった',
                        'q4' => '部長同席の商談を設定する。社内稟議フローも確認する'],
                ],
                'followup'  => null,
            ],
            [
                'title'     => '大和工業向け砕石製品大口契約',
                'client'    => $clients[6], // 大和工業
                'area'      => $areaStone,
                'type'      => $typeProduct,
                'dept'      => $deptKanto,
                'user'      => $users[5], // 鈴木
                'estimated' => 25000000,
                'confirmed' => null,
                'status'    => 'active',
                'fiscal'    => 2025,
                'process'   => 2,
                'records'   => [
                    ['step' => 1, 'date' => '2025-03-15', 'result' => 'success',
                        'q1' => '年間調達計画と砕石製品の品質要件を把握する',
                        'q2' => '年間2500万円規模の調達計画を確認。品質基準も明確化できた',
                        'q3' => '事前に建設業界の動向データを資料化していたことが話題のきっかけになった',
                        'q4' => 'サンプル品と品質保証書を準備して再訪する'],
                    ['step' => 2, 'date' => '2025-04-28', 'result' => 'ongoing',
                        'q1' => 'サンプル品を評価してもらい、価格交渉に入る',
                        'q2' => 'サンプル品質は高評価。価格条件の社内検討中',
                        'q3' => '品質評価は想定以上に良好だったが、価格帯で競合と僅差',
                        'q4' => '社内で付加価値提案（輸送コスト込みパッケージ）を検討する'],
                ],
                'followup'  => ['action' => '輸送込みパッケージ価格の社内調整', 'date' => '2025-06-10', 'status' => 'pending'],
            ],
            [
                'title'     => '太平洋建設 新工場向けプラント提案',
                'client'    => $clients[7], // 太平洋建設
                'area'      => $areaStone,
                'type'      => $typePlant,
                'dept'      => $deptHQ,
                'user'      => $users[4], // 佐藤
                'estimated' => 120000000,
                'confirmed' => null,
                'status'    => 'active',
                'fiscal'    => 2025,
                'process'   => 1,
                'records'   => [
                    ['step' => 1, 'date' => '2025-05-08', 'result' => 'success',
                        'q1' => '新工場プロジェクトの全体像と意思決定者を把握する',
                        'q2' => '工場建設計画（2026年完成予定）を確認。設備予算は12億円規模',
                        'q3' => '経営企画部長を紹介してもらえたことが大きな進展。展示会での接点が活きた',
                        'q4' => '技術部門と共同でプラント仕様の提案書を作成し、月内に提出する'],
                ],
                'followup'  => ['action' => 'プラント仕様提案書の作成（技術部連携）', 'date' => '2025-06-15', 'status' => 'pending'],
            ],
            // ── 失注（lost）案件 ──────────────────────────────
            [
                'title'     => '北九州資材 部品供給契約',
                'client'    => $clients[5], // 北九州資材
                'area'      => $areaOther,
                'type'      => $typeParts,
                'dept'      => $deptKyushu,
                'user'      => $users[4], // 佐藤
                'estimated' => 12000000,
                'confirmed' => null,
                'status'    => 'lost',
                'fiscal'    => 2024,
                'process'   => 2,
                'records'   => [
                    ['step' => 1, 'date' => '2024-09-20', 'result' => 'success',
                        'q1' => '調達先切り替えの可能性と意思決定プロセスを確認する',
                        'q2' => '現調達先への不満を確認。切り替え検討中と判明',
                        'q3' => '紹介経由のためスムーズにキーマンと面談できた',
                        'q4' => '品質と価格の比較資料を作成し、競合優位性を示す'],
                    ['step' => 2, 'date' => '2024-11-15', 'result' => 'failure',
                        'q1' => '最終的に自社を選んでもらう',
                        'q2' => '競合他社に決定。価格で約8%差をつけられた',
                        'q3' => '価格競争力が不足していた。品質訴求だけでは不十分だった',
                        'q4' => '九州エリアの価格体系を見直す。物流コスト削減策を検討する'],
                ],
                'followup'  => null,
            ],
            [
                'title'     => '東亜産業 商品定期発注切り替え',
                'client'    => $clients[8], // 東亜産業
                'area'      => $areaOther,
                'type'      => $typeGoods,
                'dept'      => $deptTohoku,
                'user'      => $users[6], // 高橋
                'estimated' => 6000000,
                'confirmed' => null,
                'status'    => 'lost',
                'fiscal'    => 2025,
                'process'   => 2,
                'records'   => [
                    ['step' => 1, 'date' => '2025-03-22', 'result' => 'success',
                        'q1' => '現在の調達状況と切り替え意欲を確認する',
                        'q2' => '年間600万円規模の定期発注を確認。検討に前向きな姿勢',
                        'q3' => '担当者が以前の取引で良い印象を持ってくれていた',
                        'q4' => '価格と納期の具体的な条件提示を準備する'],
                    ['step' => 2, 'date' => '2025-05-01', 'result' => 'failure',
                        'q1' => '正式発注の合意を得る',
                        'q2' => '現取引先との既存契約が3年間のため切り替え不可と判明',
                        'q3' => '契約期間の確認を初回ヒアリング時に行うべきだった',
                        'q4' => '3年後の契約更新時期に再アプローチ。定期的な情報提供を継続する'],
                ],
                'followup'  => ['action' => '2028年3月に再提案スケジュールをカレンダー登録', 'date' => '2028-03-01', 'status' => 'pending'],
            ],
            // ── 保留（hold）案件 ──────────────────────────────
            [
                'title'     => '日本砕石 北海道工場向け製品供給',
                'client'    => $clients[9], // 日本砕石
                'area'      => $areaStone,
                'type'      => $typeProduct,
                'dept'      => $deptHQ,
                'user'      => $users[3], // 田中（manager）
                'estimated' => 30000000,
                'confirmed' => null,
                'status'    => 'hold',
                'fiscal'    => 2025,
                'process'   => 1,
                'records'   => [
                    ['step' => 1, 'date' => '2025-04-03', 'result' => 'ongoing',
                        'q1' => '北海道工場の設備稼働計画と調達ニーズを把握する',
                        'q2' => '工場稼働が半年遅延（当初10月→翌4月予定）と判明',
                        'q3' => '工場建設遅延は外部要因（建材不足）で先方もコントロール不能',
                        'q4' => '稼働時期が確定したら即座に動けるよう、仕様・価格条件を固めておく'],
                ],
                'followup'  => ['action' => '工場稼働時期の確認（10月目安）', 'date' => '2025-10-01', 'status' => 'pending'],
            ],
            // ── 追加の商談中案件（ダッシュボード充実のため）──
            [
                'title'     => '関東土木 砕石ゴミ処理設備一式',
                'client'    => $clients[2], // 関東土木
                'area'      => $areaStone,
                'type'      => $typeProduct,
                'dept'      => $deptKanto,
                'user'      => $users[5], // 鈴木
                'estimated' => 9800000,
                'confirmed' => null,
                'status'    => 'active',
                'fiscal'    => 2025,
                'process'   => 1,
                'records'   => [
                    ['step' => 1, 'date' => '2025-05-19', 'result' => 'ongoing',
                        'q1' => '廃材処理設備の選定基準と導入スケジュールを把握する',
                        'q2' => '初回訪問完了。担当者は好意的だが決裁者への接触がこれから',
                        'q3' => '担当者との信頼関係構築には成功したが、上位層へのアプローチが課題',
                        'q4' => '担当者から部長への紹介を依頼する。業績事例集を資料として提供する'],
                ],
                'followup'  => null,
            ],
            [
                'title'     => '東北建設 リサイクル商品定期供給',
                'client'    => $clients[1], // 東北建設
                'area'      => $areaRecycle,
                'type'      => $typeGoods,
                'dept'      => $deptTohoku,
                'user'      => $users[6], // 高橋
                'estimated' => 7200000,
                'confirmed' => null,
                'status'    => 'active',
                'fiscal'    => 2025,
                'process'   => 2,
                'records'   => [
                    ['step' => 1, 'date' => '2025-04-14', 'result' => 'success',
                        'q1' => 'リサイクル商品の調達ニーズと品質基準を明確化する',
                        'q2' => '年間720万円の調達ポテンシャルを確認。品質基準もクリアできる仕様',
                        'q3' => '過去の東北建設との取引実績が信頼の土台になっていた',
                        'q4' => '具体的な商品サンプルと価格表を揃えて提案に臨む'],
                    ['step' => 2, 'date' => '2025-05-26', 'result' => 'ongoing',
                        'q1' => '年間取引の基本契約書に署名してもらう',
                        'q2' => '価格・納期条件は合意済み。法務確認待ちで契約書最終稿を調整中',
                        'q3' => '事前に契約書ひな型を法務と確認してあったためスムーズに進んでいる',
                        'q4' => '法務確認完了後、速やかに署名式を設定する'],
                ],
                'followup'  => ['action' => '契約書最終稿の法務確認と署名日程の調整', 'date' => '2025-06-03', 'status' => 'pending'],
            ],
            [
                'title'     => '西日本リサイクル 部品メンテナンス一括契約',
                'client'    => $clients[10], // 西日本リサイクル
                'area'      => $areaRecycle,
                'type'      => $typeMaint,
                'dept'      => $deptKansai,
                'user'      => $users[2], // 山田（manager）
                'estimated' => 15000000,
                'confirmed' => null,
                'status'    => 'active',
                'fiscal'    => 2025,
                'process'   => 1,
                'records'   => [
                    ['step' => 1, 'date' => '2025-05-22', 'result' => 'success',
                        'q1' => '既存設備のメンテナンス課題と一括契約への関心を確認する',
                        'q2' => '複数設備のメンテナンスが分散しており一括化ニーズを確認。意思決定者とも面談できた',
                        'q3' => '展示会でのデモ効果が大きく、技術信頼性を最初から訴求できた',
                        'q4' => '設備台帳を基にしたメンテナンス計画書を提案書として作成する'],
                ],
                'followup'  => ['action' => 'メンテナンス計画書（提案書）の作成', 'date' => '2025-06-08', 'status' => 'pending'],
            ],
        ];

        // ── ⑤案件・AARレコード・フォローアップの一括投入 ─────
        foreach ($opportunityDefs as $def) {
            // 案件番号採番
            $oppNo = Opportunity::generateOpportunityNo();

            $opp = Opportunity::firstOrCreate(
                ['title' => $def['title'], 'fiscal_year' => $def['fiscal']],
                [
                    'opportunity_no'      => $oppNo,
                    'client_id'           => $def['client']->id,
                    'area_id'             => $def['area']->id,
                    'opportunity_type_id' => $def['type']->id,
                    'department_id'       => $def['dept']->id,
                    'assigned_user_id'    => $def['user']->id,
                    'estimated_amount'    => $def['estimated'],
                    'confirmed_amount'    => $def['confirmed'],
                    'current_process'     => $def['process'],
                    'status'              => $def['status'],
                    'fiscal_year'         => $def['fiscal'],
                ]
            );

            // AARレコード投入
            $lastAarRecord = null;
            foreach ($def['records'] as $rec) {
                $actDate = Carbon::parse($rec['date']);
                $aar = AarRecord::firstOrCreate(
                    ['opportunity_id' => $opp->id, 'process_step' => $rec['step'], 'activity_date' => $actDate->toDateString()],
                    [
                        'user_id'      => $def['user']->id,
                        'result'       => $rec['result'],
                        'q1_goal'      => $rec['q1'],
                        'q2_result'    => $rec['q2'],
                        'q3_cause'     => $rec['q3'],
                        'q4_action'    => $rec['q4'],
                        'is_draft'     => false,
                        'submitted_at' => $actDate->copy()->addHours(rand(1, 8)),
                    ]
                );
                $lastAarRecord = $aar;
            }

            // フォローアップ（最後のAARレコードに紐付け）
            if ($def['followup'] && $lastAarRecord) {
                FollowupAction::firstOrCreate(
                    ['aar_record_id' => $lastAarRecord->id],
                    [
                        'opportunity_id'     => $opp->id,
                        'user_id'            => $def['user']->id,
                        'action_description' => $def['followup']['action'],
                        'scheduled_date'     => $def['followup']['date'],
                        'status'             => $def['followup']['status'],
                        'completed_at'       => $def['followup']['status'] === 'completed' ? Carbon::parse($def['followup']['date'])->subDays(3) : null,
                    ]
                );
            }
        }

        $this->command->info('✅ ダミーデータ投入完了:');
        $this->command->info('   ユーザー     : ' . User::count() . '名');
        $this->command->info('   取引先       : ' . Client::count() . '社');
        $this->command->info('   案件         : ' . Opportunity::count() . '件');
        $this->command->info('   AARレコード  : ' . AarRecord::count() . '件');
        $this->command->info('   フォローアップ: ' . FollowupAction::count() . '件');
    }
}
