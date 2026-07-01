<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Kreiranje dozvola
        $permissions = [
            // Kvizovi
            ['name' => 'quiz.view', 'description' => 'Pregledavanje kvizova'],
            ['name' => 'quiz.create', 'description' => 'Kreiranje kvizova'],
            ['name' => 'quiz.edit', 'description' => 'Uređivanje kvizova'],
            ['name' => 'quiz.delete', 'description' => 'Brisanje kvizova'],
            ['name' => 'quiz.take', 'description' => 'Rješavanje kvizova'],

            // Pitanja i opcije
            ['name' => 'question.create', 'description' => 'Kreiranje pitanja'],
            ['name' => 'question.edit', 'description' => 'Uređivanje pitanja'],
            ['name' => 'question.delete', 'description' => 'Brisanje pitanja'],
            ['name' => 'question.reorder', 'description' => 'Promjena redoslijeda pitanja'],

            // Korisnici
            ['name' => 'user.view', 'description' => 'Pregledavanje korisnika'],
            ['name' => 'user.create', 'description' => 'Kreiranje korisnika'],
            ['name' => 'user.edit', 'description' => 'Uređivanje korisnika'],
            ['name' => 'user.delete', 'description' => 'Brisanje korisnika'],
            ['name' => 'user.manage_roles', 'description' => 'Upravljanje ulogama korisnika'],

            // Administracija
            ['name' => 'admin.access', 'description' => 'Pristup admin nadzornoj ploči'],
            ['name' => 'admin.create_admins', 'description' => 'Kreiranje administratora'],

            // Upload
            ['name' => 'upload.image', 'description' => 'Upload slika'],
            ['name' => 'upload.pdf', 'description' => 'Upload PDF datoteka'],

            // Multiplayer
            ['name' => 'lobby.create', 'description' => 'Kreiranje predvorja'],
            ['name' => 'lobby.join', 'description' => 'Pridruživanje predvorju'],

            // Rezultati
            ['name' => 'results.view_own', 'description' => 'Pregled vlastitih rezultata'],
            ['name' => 'results.view_all', 'description' => 'Pregled svih rezultata'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm['name']], $perm);
        }

        // Kreiranje uloga
        $superAdmin = Role::firstOrCreate(
            ['name' => 'super_admin'],
            ['description' => 'Super administrator - puni pristup sustavu i kreiranje administratora']
        );

        $admin = Role::firstOrCreate(
            ['name' => 'admin'],
            ['description' => 'Administrator - upravljanje korisnicima i sadržajem']
        );

        $teacher = Role::firstOrCreate(
            ['name' => 'teacher'],
            ['description' => 'Profesor - kreiranje i upravljanje kvizovima']
        );

        $student = Role::firstOrCreate(
            ['name' => 'student'],
            ['description' => 'Student - rješavanje kvizova i pregled rezultata']
        );

        // Dodjela dozvola ulogama

        // Super Admin - sve dozvole
        $allPermissions = Permission::all();
        $superAdmin->permissions()->sync($allPermissions->pluck('id'));

        // Admin - sve osim kreiranje admina
        $adminPermissions = Permission::whereNot('name', 'admin.create_admins')->get();
        $admin->permissions()->sync($adminPermissions->pluck('id'));

        // Profesor
        $teacherPerms = Permission::whereIn('name', [
            'quiz.view', 'quiz.create', 'quiz.edit', 'quiz.delete', 'quiz.take',
            'question.create', 'question.edit', 'question.delete', 'question.reorder',
            'upload.image', 'upload.pdf',
            'lobby.create', 'lobby.join',
            'results.view_own', 'results.view_all',
        ])->get();
        $teacher->permissions()->sync($teacherPerms->pluck('id'));

        // Student
        $studentPerms = Permission::whereIn('name', [
            'quiz.view', 'quiz.take',
            'lobby.join',
            'results.view_own',
        ])->get();
        $student->permissions()->sync($studentPerms->pluck('id'));

        // Dodjela uloga postojećim korisnicima na temelju user_type
        $users = User::all();
        foreach ($users as $user) {
            $role = Role::where('name', $user->user_type)->first();
            if ($role) {
                $user->roles()->syncWithoutDetaching([$role->id]);
            }
        }

        // Kreiranje Super Admin korisnika ako ne postoji
        $superAdminUser = User::firstOrCreate(
            ['email' => 'superadmin@quizapp.com'],
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'password' => Hash::make('superadmin123'),
                'user_type' => 'super_admin',
            ]
        );
        $superAdminUser->roles()->syncWithoutDetaching([$superAdmin->id]);
    }
}
