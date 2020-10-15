<?php
namespace App\Repositories;

use App\Models\Currency;
use Bosnadev\Repositories\Eloquent\Repository as BaseRepository;


class CurrencyRepository extends BaseRepository
{
    public function model()
    {
        return Currency::class;
    }

  
}
