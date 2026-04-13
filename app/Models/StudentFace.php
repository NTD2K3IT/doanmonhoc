<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentFace extends Model
{
    protected $table = 'student_faces';

    protected $fillable = [
        'id',
        'maSV',
        'rekognition_face_id',
        'external_image_id',
        'collection_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'maSV', 'maSV');
    }
}