<?php
namespace App\Repositories;

use App\Models\SmsRule;

class SmsRulesRepository extends Repository
{
    public function model()
    {
        return SmsRule::class;
    }
}