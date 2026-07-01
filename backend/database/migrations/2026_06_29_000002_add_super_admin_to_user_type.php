<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite ne podržava ALTER COLUMN za enum, pa koristimo workaround
        // Laravel enum je zapravo string s CHECK constraint-om
        Schema::table('users', function (Blueprint $table) {
            $table->string('user_type_new')->default('student')->after('user_type');
        });

        // Kopiraj postojeće vrijednosti
        DB::table('users')->update(['user_type_new' => DB::raw('user_type')]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('user_type');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('user_type', ['super_admin', 'admin', 'teacher', 'student'])->default('student')->after('email');
        });

        // Kopiraj natrag
        DB::table('users')->update(['user_type' => DB::raw('user_type_new')]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('user_type_new');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('user_type_new')->default('student')->after('user_type');
        });

        DB::table('users')->update(['user_type_new' => DB::raw('user_type')]);
        DB::table('users')->where('user_type_new', 'super_admin')->update(['user_type_new' => 'admin']);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('user_type');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('user_type', ['admin', 'teacher', 'student'])->default('student')->after('email');
        });

        DB::table('users')->update(['user_type' => DB::raw('user_type_new')]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('user_type_new');
        });
    }
};
