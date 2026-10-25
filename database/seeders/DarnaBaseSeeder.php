<?php

namespace Database\Seeders;

use App\Models\Governorate;
use App\Models\Office;
use App\Models\Owner;
use App\Models\User;
use App\Models\Property;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DarnaBaseSeeder extends Seeder
{
    public function run(): void
    {
        $governorates = [
            'دمشق',
            'ريف دمشق',
            'حلب',
            'حمص',
            'حماة',
            'اللاذقية',
            'طرطوس',
            'إدلب',
            'درعا',
            'السويداء',
        ];

        foreach ($governorates as $name) {
            Governorate::firstOrCreate(['name' => $name]);
        }

        $officesData = [
            ['name' => 'مكتب دمشق العقاري', 'email' => 'office1@darna.test'],
            ['name' => 'مكتب اللاذقية العقاري', 'email' => 'office2@darna.test'],
            ['name' => 'مكتب حلب العقاري', 'email' => 'office3@darna.test'],
        ];

        foreach ($officesData as $index => $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                ]
            );

            Office::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $data['name'],
                    'phone' => '099000000' . ($index + 1),
                    'address' => $governorates[$index],
                ]
            );
        }

        for ($i = 1; $i <= 20; $i++) {
            Owner::firstOrCreate([
                'name' => 'مالك تجريبي ' . $i,
            ]);
        }
        $targetCount = 3000;

        $currentCount = Property::withoutGlobalScopes()->count();

        $missingCount = max(0, $targetCount - $currentCount);

        if ($missingCount > 0) {
            Property::factory()
                ->count($missingCount)
                ->create();
        }
            }
}
