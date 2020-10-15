<?php
namespace App\Repositories;

use App\Models\Site;

class SiteRepository extends Repository
{
    public function model()
    {
        return Site::class;
    }
}