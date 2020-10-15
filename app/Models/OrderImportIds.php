<?php

namespace App\Models;

class OrderImportIds extends Model
{

	 protected $fillable = [
		'id',
		'order_id',
		'import_id'
	];

	protected $primaryKey = 'id';

    protected $table = 'order_import_ids';

}
