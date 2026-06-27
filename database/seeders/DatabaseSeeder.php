<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // -------------------------------------------------------
        // 1. USERS — 1 principal + 2 advisers
        // -------------------------------------------------------
        DB::table('users')->insert([
            [
                'name'       => 'Maria Santos',
                'email'      => 'principal@naggasican.edu.ph',
                'password'   => Hash::make('password'),
                'role'       => 'principal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Juan Dela Cruz',
                'email'      => 'adviser1@naggasican.edu.ph',
                'password'   => Hash::make('password'),
                'role'       => 'adviser',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Ana Reyes',
                'email'      => 'adviser2@naggasican.edu.ph',
                'password'   => Hash::make('password'),
                'role'       => 'adviser',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // -------------------------------------------------------
        // 2. SECTIONS
        // -------------------------------------------------------
        DB::table('sections')->insert([
            [
                'name'        => 'Narra',
                'grade_level' => 7,
                'adviser_id'  => 2, // Juan Dela Cruz
                'school_year' => '2024-2025',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'Molave',
                'grade_level' => 8,
                'adviser_id'  => 3, // Ana Reyes
                'school_year' => '2024-2025',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);

        // -------------------------------------------------------
        // 3. SUBJECTS (DepEd Grade 7 & 8 core subjects)
        // -------------------------------------------------------
        DB::table('subjects')->insert([
            ['name' => 'Filipino',               'grade_level' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'English',                'grade_level' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mathematics',            'grade_level' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Science',                'grade_level' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Araling Panlipunan',     'grade_level' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MAPEH',                  'grade_level' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'TLE',                    'grade_level' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Edukasyon sa Pagpapakatao', 'grade_level' => 7, 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'Filipino',               'grade_level' => 8, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'English',                'grade_level' => 8, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mathematics',            'grade_level' => 8, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Science',                'grade_level' => 8, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Araling Panlipunan',     'grade_level' => 8, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MAPEH',                  'grade_level' => 8, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'TLE',                    'grade_level' => 8, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Edukasyon sa Pagpapakatao', 'grade_level' => 8, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // -------------------------------------------------------
        // 4. STUDENTS — 3 per section
        // -------------------------------------------------------
        DB::table('students')->insert([
            // Section 1 - Narra (Grade 7)
            ['lrn' => '100000000001', 'last_name' => 'Flores',  'first_name' => 'Pedro',   'middle_name' => 'M', 'section_id' => 1, 'gender' => 'male',   'birthdate' => '2012-03-15', 'created_at' => now(), 'updated_at' => now()],
            ['lrn' => '100000000002', 'last_name' => 'Garcia',  'first_name' => 'Maria',   'middle_name' => 'L', 'section_id' => 1, 'gender' => 'female', 'birthdate' => '2012-07-22', 'created_at' => now(), 'updated_at' => now()],
            ['lrn' => '100000000003', 'last_name' => 'Ramos',   'first_name' => 'Jose',    'middle_name' => 'A', 'section_id' => 1, 'gender' => 'male',   'birthdate' => '2012-11-01', 'created_at' => now(), 'updated_at' => now()],

            // Section 2 - Molave (Grade 8)
            ['lrn' => '100000000004', 'last_name' => 'Cruz',    'first_name' => 'Anna',    'middle_name' => 'B', 'section_id' => 2, 'gender' => 'female', 'birthdate' => '2011-05-10', 'created_at' => now(), 'updated_at' => now()],
            ['lrn' => '100000000005', 'last_name' => 'Santos',  'first_name' => 'Carlos',  'middle_name' => 'R', 'section_id' => 2, 'gender' => 'male',   'birthdate' => '2011-09-18', 'created_at' => now(), 'updated_at' => now()],
            ['lrn' => '100000000006', 'last_name' => 'Lim',     'first_name' => 'Patricia','middle_name' => 'S', 'section_id' => 2, 'gender' => 'female', 'birthdate' => '2011-01-25', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // -------------------------------------------------------
        // 5. SAMPLE GRADES (1st grading, Section 1 - Narra)
        // -------------------------------------------------------
        $gradeData = [
            // Student 1 (Pedro) - high performer
            [1, 1, 1, 2, 92.00], [1, 2, 1, 2, 90.00], [1, 3, 1, 2, 88.50],
            [1, 4, 1, 2, 91.00], [1, 5, 1, 2, 89.00], [1, 6, 1, 2, 93.00],
            [1, 7, 1, 2, 87.00], [1, 8, 1, 2, 90.50],

            // Student 2 (Maria) - average
            [2, 1, 1, 2, 78.00], [2, 2, 1, 2, 75.50], [2, 3, 1, 2, 77.00],
            [2, 4, 1, 2, 76.00], [2, 5, 1, 2, 79.00], [2, 6, 1, 2, 80.00],
            [2, 7, 1, 2, 74.00], [2, 8, 1, 2, 76.50],

            // Student 3 (Jose) - at risk
            [3, 1, 1, 2, 71.00], [3, 2, 1, 2, 68.00], [3, 3, 1, 2, 65.00],
            [3, 4, 1, 2, 70.00], [3, 5, 1, 2, 69.00], [3, 6, 1, 2, 72.00],
            [3, 7, 1, 2, 66.00], [3, 8, 1, 2, 67.50],
        ];

        foreach ($gradeData as $g) {
            DB::table('grades')->insert([
                'student_id'      => $g[0],
                'subject_id'      => $g[1],
                'section_id'      => $g[2],
                'grading_period'  => 1,
                'grade'           => $g[4],
                'school_year'     => '2024-2025',
                'encoded_by'      => $g[3],
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        // -------------------------------------------------------
        // 6. REPORT SUBMISSION (Section 1 already submitted)
        // -------------------------------------------------------
        DB::table('report_submissions')->insert([
            'section_id'      => 1,
            'submitted_by'    => 2,
            'grading_period'  => 1,
            'status'          => 'submitted',
            'school_year'     => '2024-2025',
            'submitted_at'    => now(),
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // -------------------------------------------------------
        // 7. RISK RESULTS (generated by Python analytics)
        // -------------------------------------------------------
        DB::table('risk_results')->insert([
            ['student_id' => 1, 'grading_period' => 1, 'average_grade' => 90.13, 'risk_level' => 'low',      'school_year' => '2024-2025', 'generated_at' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['student_id' => 2, 'grading_period' => 1, 'average_grade' => 77.00, 'risk_level' => 'moderate', 'school_year' => '2024-2025', 'generated_at' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['student_id' => 3, 'grading_period' => 1, 'average_grade' => 68.56, 'risk_level' => 'high',     'school_year' => '2024-2025', 'generated_at' => now(), 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}