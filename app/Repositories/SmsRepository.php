<?php
namespace App\Repositories;

use App\Models\Sms;

class SmsRepository extends Repository
{
    public function model()
    {
        return Sms::class;
    }
}