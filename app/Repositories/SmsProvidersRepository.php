<?php
namespace App\Repositories;

use App\Models\SmsProvider;

class SmsProvidersRepository extends Repository
{
    public function model()
    {
        return SmsProvider::class;
    }
}