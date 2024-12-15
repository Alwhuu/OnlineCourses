<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolepermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        //membuat beberapa role
        //membuat default user untuk super admin

        $ownerRole = Role::create([
            'name' => 'owner'
        ]);

        $studentRole = Role::create([
            'name' => 'student'
        ]);

        $teacherRole = Role::create([
            'name' => 'teacher'
        ]);

        //akun super admin untuk mengelola data awal
        $userOwner = User::create([
            'name' => 'Alwi Owner',
            'occupation' => 'Educator',
            'avatar' => 'image/default-avatar.png',
            'email' => 'alwi@owner.com',
            'password' => bcrypt('123123123')
        ]);

        $userOwner->assignRole($ownerRole);
    }
}