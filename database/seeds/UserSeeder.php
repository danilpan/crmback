<?php

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        
        $user = User::where('login','root')->first();                
        if(!$user){
        	$pass   = Hash::make('BBzmc8nu3MfDwANg');
        	User::create([
	            'first_name'        => 'root',
                'last_name'         => 'root',
                'login'             => 'root',
	            'password'          => $pass,
	            'organization_id'   => 1
        	]);
    	}
        
    }
}