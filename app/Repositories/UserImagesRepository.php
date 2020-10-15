<?php
namespace App\Repositories;

use App\Models\UserImage;

class UserImagesRepository extends Repository
{
    public function model()
    {
        return UserImage::class;
    }
}