<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles if they don't exist
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $educatorRole = Role::firstOrCreate(['name' => 'educator']);
        $studentRole = Role::firstOrCreate(['name' => 'student']);

        // Create admin user if it doesn't exist
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'sex' => 'male', // Default sex for admin
                'gender' => 'male', // For backward compatibility
                'password' => Hash::make('password123')
            ]
        );
        if (!$admin->roles()->where('name', 'admin')->exists()) {
            $admin->roles()->attach($adminRole);
        }

        // Create educator users
        $educator1 = User::firstOrCreate(
            ['email' => 'educator1@example.com'],
            [
                'name' => 'Charwel Giera',
                'fname' => 'Charwel',
                'lname' => 'Giera',
                'educator_id' => 'E2025001', // Add educator ID
                'sex' => 'male',
                'gender' => 'male',
                'password' => Hash::make('password123')
            ]
        );
        if (!$educator1->roles()->where('name', 'educator')->exists()) {
            $educator1->roles()->attach($educatorRole);
        }

        $educator2 = User::firstOrCreate(
            ['email' => 'educator2@example.com'],
            [
                'name' => 'Jane Tumulak',
                'fname' => 'Jane',
                'lname' => 'Tumulak',
                'educator_id' => 'E2025002', // Add educator ID
                'sex' => 'female',
                'gender' => 'female',
                'password' => Hash::make('password123')
            ]
        );
        if (!$educator2->roles()->where('name', 'educator')->exists()) {
            $educator2->roles()->attach($educatorRole);
        }

        // Create student users
        $students = [
            [
                'name' => 'Jenvier Montano',
                'fname' => 'Jenvier',
                'lname' => 'Montano',
                'student_id' => '2025010001C1',
                'email' => 'jenvier@example.com',
                'password' => 'password123',
                'sex' => 'female',
                'gender' => 'female' // For backward compatibility
            ],
            [
                'name' => 'Angelo Parrocho',
                'fname' => 'Angelo',
                'lname' => 'Parrocho',
                'student_id' => '2025010002C1',
                'email' => 'angelo@example.com',
                'password' => 'password123',
                'sex' => 'male',
                'gender' => 'male' // For backward compatibility
            ],
            [
                'name' => 'Jasper Drake',
                'fname' => 'Jasper',
                'lname' => 'Drake',
                'student_id' => '2025010003C1',
                'email' => 'jasper@example.com',
                'password' => 'password123',
                'sex' => 'male',
                'gender' => 'male' // For backward compatibility
            ],
            [
                'name' => 'Sarah Jomuad',
                'fname' => 'Sarah',
                'lname' => 'Jomuad',
                'student_id' => '2025010004C1',
                'email' => 'sarah@example.com',
                'password' => 'password123',
                'sex' => 'female',
                'gender' => 'female' // For backward compatibility
            ],
            [
                'name' => 'Dion Paner',
                'fname' => 'Dion',
                'lname' => 'Paner',
                'student_id' => '2025010005C1',
                'email' => 'dion@example.com',
                'password' => 'password123',
                'sex' => 'male',
                'gender' => 'male' // For backward compatibility
            ],
            [
                'name' => 'Micheal Jovita',
                'fname' => 'Micheal',
                'lname' => 'Jovita',
                'student_id' => '2025010006C1',
                'email' => 'micheal@example.com',
                'password' => 'password123',
                'sex' => 'male',
                'gender' => 'male' // For backward compatibility
            ],
            [
                'name' => 'Junrel Ejurango',
                'fname' => 'Junrel',
                'lname' => 'Ejurango',
                'student_id' => '2025010007C1',
                'email' => 'junrel@example.com',
                'password' => 'password123',
                'sex' => 'male',
                'gender' => 'male' // For backward compatibility
            ],
            [
                'name' => 'Nicole Oco',
                'fname' => 'Nicole',
                'lname' => 'Oco',
                'student_id' => '2025010008C1',
                'email' => 'nicole@example.com',
                'password' => 'password123',
                'sex' => 'female',
                'gender' => 'female' // For backward compatibility
            ],
            [
                'name' => 'Lotchene Balcorza',
                'fname' => 'Lotchene',
                'lname' => 'Balcorza',
                'student_id' => '2025010009C1',
                'email' => 'lotchene@example.com',
                'password' => 'password123',
                'sex' => 'female',
                'gender' => 'female' // For backward compatibility
            ],
            [
                'name' => 'Marie Dasian',
                'fname' => 'Marie',
                'lname' => 'Dasian',
                'student_id' => '2025010010C1',
                'email' => 'marie@example.com',
                'password' => 'password123',
                'sex' => 'female',
                'gender' => 'female' // For backward compatibility
            ],
            [
                'name' => 'Jincent Caritan',
                'fname' => 'Jincent',
                'lname' => 'Caritan',
                'student_id' => '2026010001C1',
                'email' => 'jincent@example.com',
                'password' => 'password123',
                'sex' => 'male',
                'gender' => 'male' // For backward compatibility
            ],
            [
                'name' => 'Alfe Pagunsan',
                'fname' => 'Alfe',
                'lname' => 'Pagunsan',
                'student_id' => '2026010002C1',
                'email' => 'alfe2@example.com',
                'password' => 'password123',
                'sex' => 'male',
                'gender' => 'male' // For backward compatibility
            ],
            [
                'name' => 'Josh Calub',
                'fname' => 'Josh',
                'lname' => 'Calub',
                'student_id' => '2026010003C1',
                'email' => 'josh@example.com',
                'password' => 'password123',
                'sex' => 'male',
                'gender' => 'male' // For backward compatibility
            ],
            [
                'name' => 'Radel Agsalud',
                'fname' => 'Radel',
                'lname' => 'Agsalud',
                'student_id' => '2026010004C1',
                'email' => 'radel@example.com',
                'password' => 'password123',
                'sex' => 'male',
                'gender' => 'male' // For backward compatibility
            ],
            [
                'name' => 'Arnel Condez',
                'fname' => 'Arnel',
                'lname' => 'Condez',
                'student_id' => '2026010005C1',
                'email' => 'arnel@example.com',
                'password' => 'password123',
                'sex' => 'male',
                'gender' => 'male' // For backward compatibility
            ],
            [
                'name' => 'Cherry Tenepre',
                'fname' => 'Cherry',
                'lname' => 'Tenepre',
                'student_id' => '2026010006C1',
                'email' => 'cherry@example.com',
                'password' => 'password123',
                'sex' => 'female',
                'gender' => 'female' // For backward compatibility
            ],
            [
                'name' => 'Jane Ruben',
                'fname' => 'Jane',
                'lname' => 'Ruben',
                'student_id' => '2026010007C1',
                'email' => 'jane@example.com',
                'password' => 'password123',
                'sex' => 'female',
                'gender' => 'female' // For backward compatibility
            ],
            [
                'name' => 'Grace Bautista',
                'fname' => 'Grace',
                'lname' => 'Baustista',
                'student_id' => '2026010008C1',
                'email' => 'grace@example.com',
                'password' => 'password123',
                'sex' => 'female',
                'gender' => 'female' // For backward compatibility
            ],
            [
                'name' => 'Wendolyn Dante',
                'fname' => 'Wendolyn',
                'lname' => 'Dante',
                'student_id' => '2026010009C1',
                'email' => 'wendolyn@example.com',
                'password' => 'password123',
                'sex' => 'female',
                'gender' => 'female' // For backward compatibility
            ],
            [
                'name' => 'Glaiza Bejec',
                'fname' => 'Glaiza',
                'lname' => 'Bejec',
                'student_id' => '2026010010C1',
                'email' => 'glaiza@example.com',
                'password' => 'password123',
                'sex' => 'female',
                'gender' => 'female' // For backward compatibility
            ]

        ];

        foreach ($students as $studentData) {
            $student = User::firstOrCreate(
                ['email' => $studentData['email']],
                $studentData + ['password' => Hash::make($studentData['password'])]
            );
            
            if (!$student->roles()->where('name', 'student')->exists()) {
                $student->roles()->attach($studentRole);
            }
        }
    }
}

