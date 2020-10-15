<?php
namespace App\Repositories;

use App\Models\LnkRoleEntityParam;
use Bosnadev\Repositories\Eloquent\Repository as BaseRepository;

class LnkRoleEntityParamsRepository extends BaseRepository
{
    public function model()
    {
        return LnkRoleEntityParam::class;
    }
}