<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;


abstract class Request extends FormRequest
{
    public function authorize()
    {
        return true;
    }


    abstract public function rules();



}
