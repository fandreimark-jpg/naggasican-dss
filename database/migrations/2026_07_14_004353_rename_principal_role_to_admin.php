<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Renames the 'principal' role to 'admin' throughout the database.
 *
 * Done in 3 steps because MySQL enum columns can't just have a value
 * swapped out directly — if we removed 'principal' from the allowed
 * list while existing rows still had 'principal' saved, those rows
 * would break. So:
 *   1. Temporarily allow BOTH 'principal' and 'admin' as valid values.
 *   2. Update every existing row that says 'principal' to say 'admin'.
 *   3. Now that no row uses 'principal' anymore, remove it from the
 *      allowed list for good.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Step 1: widen the enum temporarily to accept both old and new values
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('adviser','principal','admin') NOT NULL");

        // Step 2: migrate existing data
        DB::table('users')->where('role', 'principal')->update(['role' => 'admin']);

        // Step 3: narrow the enum back down — 'principal' is no longer valid
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('adviser','admin') NOT NULL");
    }

    public function down(): void
    {
        // Reverse the same 3 steps, in case this migration ever needs to be rolled back
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('adviser','admin','principal') NOT NULL");
        DB::table('users')->where('role', 'admin')->update(['role' => 'principal']);
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('adviser','principal') NOT NULL");
    }
};
