<?php
namespace App\Models;

class UserImage extends Model
{
    protected $table = 'user_images';

    protected $fillable = [
        'user_id',
        'image',
        'is_main',
        'image_type_id'
    ];

    public function ImageType()
    {
        return $this->belongsTo(ImageType::class, 'image_type_id');
    }
}