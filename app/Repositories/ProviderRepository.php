<?php
namespace App\Repositories;

use App\Models\Provider;

class ProviderRepository extends Repository
{
    public function model()
    {
        return Provider::class;
    }
    
    public function prepareSearchData($model)
    {
        $data   = [
            'id' => $model->id,
            'name' => $model->name,
            'comment' => $model->comment,
            'img' => $model->img,
        ];
        
        return $data;
    }
}