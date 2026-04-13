<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('student_faces', function (Blueprint $table) {
            $table->id();
            $table->string('maSV', 20);
            $table->string('rekognition_face_id')->unique();
            $table->string('external_image_id')->nullable();
            $table->string('collection_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('maSV');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_faces');
    }
};
