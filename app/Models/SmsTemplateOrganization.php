<?php
namespace App\Models;

class SmsTemplateOrganization extends Model
{
    protected $table = 'sms_templates_organization';

    protected $primaryKey = 'id';

    protected $fillable = [
      'organization_id',
      'sms_template_id'
    ];

    public $timestamps = false;

}