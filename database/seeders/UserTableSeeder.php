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
        $educators = [
            [
                'name' => 'Charwel Glera',
                'email' => 'educator1@example.com',
                'password' => 'password123',
                'sex' => 'male',
                'gender' => 'male' // For backward compatibility
            ],
            [
                'name' => 'Jane Tumulak',
                'email' => 'educator2@example.com',
                'password' => 'password123',
                'sex' => 'female',
                'gender' => 'female' // For backward compatibility
            ]
        ];

        foreach ($educators as $educator) {
            $user = User::firstOrCreate(
                ['email' => $educator['email']],
                [
                    'name' => $educator['name'],
                    'sex' => $educator['sex'],
                    'gender' => $educator['gender'], // For backward compatibility
                    'password' => Hash::make($educator['password'])
                ]
            );
            if (!$user->roles()->where('name', 'educator')->exists()) {
                $user->roles()->attach($educatorRole);
            }
        }

        // Create student users
        $students = [
            [
                'name' => 'Jenvier Montano',
                'fname' => 'Jenvier',
                'lname' => 'Montano',
                'student_id' => 'S2025001',
                'email' => 'student1@example.com',
                'password' => 'password123',
                'sex' => 'female',
                'gender' => 'female' // For backward compatibility
            ],
            [
                'name' => 'Angelo Parrocho',
                'fname' => 'Angelo',
                'lname' => 'Parrocho',
                'student_id' => 'S2025002',
                'email' => 'student2@example.com',
                'password' => 'password123',
                'sex' => 'male',
                'gender' => 'male' // For backward compatibility
            ],
            [
                'name' => 'Jasper Drake',
                'fname' => 'Jasper',
                'lname' => 'Drake',
                'student_id' => 'S2025003',
                'email' => 'student3@example.com',
                'password' => 'password123',
                'sex' => 'male',
                'gender' => 'male' // For backward compatibility
            ],
            [
                'name' => 'Sarah Jumuad',
                'fname' => 'Sarah',
                'lname' => 'Jumuad',
                'student_id' => 'S2025004',
                'email' => 'student4@example.com',
                'password' => 'password123',
                'sex' => 'female',
                'gender' => 'female' // For backward compatibility
            ],
            [
                'name' => 'Dion Paner',
                'fname' => 'Dion',
                'lname' => 'Paner',
                'student_id' => 'S2025005',
                'email' => 'student5@example.com',
                'password' => 'password123',
                'sex' => 'male',
                'gender' => 'male' // For backward compatibility
            ],
            [
                'name' => 'Micheal Jovita',
                'fname' => 'Micheal',
                'lname' => 'Jovita',
                'student_id' => 'S2025006',
                'email' => 'student6@example.com',
                'password' => 'password123',
                'sex' => 'male',
                'gender' => 'male' // For backward compatibility
            ],
            [
                'name' => 'Junrel Ejurango',
                'fname' => 'Junrel',
                'lname' => 'Ejurango',
                'student_id' => 'S2025007',
                'email' => 'student7@example.com',
                'password' => 'password123',
                'sex' => 'male',
                'gender' => 'male' // For backward compatibility
            ],
            [
                'name' => 'Nicole Oco',
                'fname' => 'Nicole',
                'lname' => 'Oco',
                'student_id' => 'S2025008',
                'email' => 'student8@example.com',
                'password' => 'password123',
                'sex' => 'female',
                'gender' => 'female' // For backward compatibility
            ],
            [
                'name' => 'Lotchene Balcorza',
                'fname' => 'Lotchene',
                'lname' => 'Balcorza',
                'student_id' => 'S2025009',
                'email' => 'student9@example.com',
                'password' => 'password123',
                'sex' => 'female',
                'gender' => 'female' // For backward compatibility
            ],
            [
                'name' => 'Marie Dasian',
                'fname' => 'Marie',
                'lname' => 'Dasian',
                'student_id' => 'S2025010',
                'email' => 'student10@example.com',
                'password' => 'password123',
                'sex' => 'female',
                'gender' => 'female' // For backward compatibility
            ]
        ];

        foreach ($students as $student) {
            $user = User::firstOrCreate(
                ['email' => $student['email']],
                [
                    'name' => $student['name'],
                    'fname' => $student['fname'],
                    'lname' => $student['lname'],
                    'student_id' => $student['student_id'],
                    'sex' => $student['sex'],
                    'gender' => $student['gender'], // For backward compatibility
                    'password' => Hash::make($student['password'])
                ]
            );
            if (!$user->roles()->where('name', 'student')->exists()) {
                $user->roles()->attach($studentRole);
            }
        }
    }
}