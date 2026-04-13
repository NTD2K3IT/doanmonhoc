<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL-compatible
        DB::statement('ALTER TABLE student_faces MODIFY COLUMN id BIGINT NOT NULL AUTO_INCREMENT');
    }

    public function down(): void
    {
        // rollback cơ bản: bỏ auto_increment
        DB::statement('ALTER TABLE student_faces MODIFY COLUMN id BIGINT NOT NULL');
    }
};