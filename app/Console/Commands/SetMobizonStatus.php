<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\SmsService;

use App\Repositories\SmsRepository;

use DB;

class SetMobizonStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SetMobizonStatus:set';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проверка статусов оператора Mobizon';

    protected $smsService;
    protected $smsRepository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        SmsService $smsService,                          
        SmsRepository $smsRepository                         
    )
    {
        parent::__construct();
        $this->smsService = $smsService;                                           
        $this->smsRepository = $smsRepository;   
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        
        $sms_arr_raw = DB::table('sms')
            ->join('sms_providers', 'sms.sms_provider_id', '=', 'sms_providers.id')
            ->select('sms.id', 'sms.service_id', 'sms.phone', 'sms.sms_provider_id', 'sms_providers.data', 'sms_providers.sms_provider')            
            ->where('sms.service_id', '>', 0)
            ->where('sms_providers.sms_provider', 1)
            ->whereIn('sms.status',['send','wait']) 
            ->orderBy('sms.service_id', 'desc')                      
            ->get()->toArray();

        $sms_arr = [];    
        $api_data_arr = [];

        foreach ($sms_arr_raw as $sms) {
            $api_data_arr[$sms->sms_provider_id] = $sms->data;   
            $sms_arr[$sms->sms_provider_id][] = $sms->service_id;            
        }                  

        foreach ($sms_arr as $sms_provider_id => $ids_block) {
            
            try {
                
                $api_key = '';

                $api_data = json_decode($api_data_arr[$sms_provider_id]);                  
                
                $api_key = $api_data->api;

                $provider = $this->smsService->getMobizon();                    
                
                $provider->status($api_key, $sms_provider_id, $ids_block);

            }catch(\Throwable $e){                                             
                $this->set_error($e->getMessage(), $sms_provider_id, $ids_block);                
            }
        }              
    }

    public function set_error(string $error, $sms_provider_id, $service_data){
        foreach ($service_data as $service_id) {
            $sms = $this->smsRepository->findWhere(['sms_provider_id'=>$sms_provider_id, 'service_id'=>$service_id])->first();             
            $this->smsRepository->update(['status'=>'wait','sms_provider_status'=>$error], $sms->id);                      
        }
    }

}
