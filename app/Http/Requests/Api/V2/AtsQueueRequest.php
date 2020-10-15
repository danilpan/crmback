<?php

namespace App\Http\Requests\Api\V2;

class AtsQueueRequest extends Request
{
    protected $types = [
        'in',
        'auto',
    ];
    
    protected $strategies = [
        'ringall',
        'leastrecent',
        'fewestcalls',
        'random',
        'rrmemory',
        'linear',
        'wrandom',
    ];
    
    /**
     * Настройка экземпляра валидатора.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->sometimes(['unload_id', 'last_status'], 'required', function ($queue) {
            return $queue->type == 'auto';
        });
        
        $validator->sometimes('how_call', 'min:1', function ($queue) {
            return $queue->type == 'auto';
        });
    }
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type' => "required|string|max:32|in:".implode(',', $this->types),
            'name' => "required|string|max:80",
            'comment' => "required|max:255",
            'steps1' => "required|json",
            'steps2' => "required|json",
            'off_time1' => "required",
            'off_time2' => "required",
            'how_call' => "required|integer|max:128",
            'strategy' => "required|string|max:32|in:".implode(',', $this->strategies),
            'check_wbt' => "boolean",
            'unload_id' => "sometimes|nullable|integer|exists:unloads,id|unique:ats_queues,unload_id",
            'ats_group_id' => "required|integer|exists:ats_groups,id",
            'is_work' => "boolean",
            'last_status' => "sometimes|nullable|exists:statuses,id",
            'dsday' => "sometimes|nullable|exists:statuses,id",
            'avr' => '',
        ];
    }
}
