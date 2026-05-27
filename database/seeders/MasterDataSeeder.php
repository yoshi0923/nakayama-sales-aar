<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Department;
use App\Models\OpportunityType;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 部署・支店
        $departments = [
            ['name' => '本社営業部', 'code' => 'HQ-SALES'],
            ['name' => '東北支店', 'code' => 'TOHOKU'],
            ['name' => '関東支店', 'code' => 'KANTO'],
            ['name' => '中部支店', 'code' => 'CHUBU'],
            ['name' => '関西支店', 'code' => 'KANSAI'],
            ['name' => '九州支店', 'code' => 'KYUSHU'],
        ];

        foreach ($departments as $i => $dept) {
            Department::firstOrCreate(['code' => $dept['code']], array_merge($dept, ['sort_order' => $i + 1]));
        }

        // エリア（領域）
        $areas = [
            ['name' => '砕石', 'code' => 'CRUSHED-STONE'],
            ['name' => 'リサイクル', 'code' => 'RECYCLE'],
            ['name' => 'その他', 'code' => 'OTHER'],
        ];

        foreach ($areas as $i => $area) {
            Area::firstOrCreate(['code' => $area['code']], array_merge($area, ['sort_order' => $i + 1]));
        }

        // 案件種別
        $types = [
            ['name' => 'プラント', 'code' => 'PLANT'],
            ['name' => '製品', 'code' => 'PRODUCT'],
            ['name' => '商品', 'code' => 'GOODS'],
            ['name' => '部品', 'code' => 'PARTS'],
            ['name' => 'メンテナンス', 'code' => 'MAINTENANCE'],
        ];

        foreach ($types as $i => $type) {
            OpportunityType::firstOrCreate(['code' => $type['code']], array_merge($type, ['sort_order' => $i + 1]));
        }
    }
}
