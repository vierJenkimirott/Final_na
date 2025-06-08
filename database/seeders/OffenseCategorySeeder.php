<?php

namespace Database\Seeders;

use App\Models\OffenseCategory;
use Illuminate\Database\Seeder;

class OffenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'category_name' => 'General Behavior',
                'description' => 'Violations related to general behavior and conduct'
            ],
            [
                'category_name' => 'Dress Code',
                'description' => 'Violations related to uniform and dress code'
            ],
            [
                'category_name' => 'Room Rules',
                'description' => 'Violations related to dormitory and room regulations'
            ],
            [
                'category_name' => 'Schedule',
                'description' => 'Violations related to timing and attendance'
            ],
            [
                'category_name' => 'Equipment',
                'description' => 'Violations related to use of equipment and facilities'
            ],
            [
                'category_name' => 'Center Tasking',
                'description' => 'Violations related to center duties and responsibilities'
            ],
        ];

        foreach ($categories as $category) {
            OffenseCategory::updateOrCreate(['category_name' => $category['category_name']], $category);
        }
    }
}
