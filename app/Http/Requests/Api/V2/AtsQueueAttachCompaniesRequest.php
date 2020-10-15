<?php

namespace App\Http\Requests\Api\V2;

class AtsQueueAttachCompaniesRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'companies' => "sometimes|array",
            'companies.*' => 'integer|exists:organizations,id',
        ];
    }
}
