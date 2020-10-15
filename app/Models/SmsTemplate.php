<?php
namespace App\Models;

class SmsTemplate extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'sms_text',
        'is_work'
    ];

    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'sms_templates_organization', 'sms_template_id', 'organization_id');
    }
}