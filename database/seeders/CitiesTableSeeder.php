<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\City;

class CitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     */

    public function run()
    {
        City::create(['name' => 'دمشق', 'code' => '11']);
        City::create(['name' => 'حلب', 'code' => '21']);
        City::create(['name' => 'حمص', 'code' => '31']);
        City::create(['name' => 'حماه', 'code' => '33']);
        City::create(['name' => 'اللاذقية', 'code' => '41']);
        City::create(['name' => 'طرطوس', 'code' => '43']);
        City::create(['name' => 'دير الزور', 'code' => '51']);
        City::create(['name' => 'الحسكة', 'code' => '52']);
        City::create(['name' => 'الرقة', 'code' => '22']);
        City::create(['name' => 'ادلب', 'code' => '23']);
        City::create(['name' => 'درعا', 'code' => '15']);
        City::create(['name' => 'السويداء', 'code' => '16']);
        City::create(['name' => 'القنيطرة', 'code' => '14']);
    }
}
