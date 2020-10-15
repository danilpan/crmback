<?php
namespace App\Services;

use App\Repositories\BlackListRepository;
use Auth;
use App\Models\User;

class BlackListService extends Service
{

	protected $blackListRepository;
	protected $ordersRepository;

    public function __construct(    	
    	BlackListRepository $BlackListRepository    	
    )
    {
    	$this->blackListRepository = $BlackListRepository;        
    }

    public function create($request)
    {
   	
   		$data = $request->validated();

   		$data['user_id'] = Auth::user()->id;
        
        $item = $this->blackListRepository->create($data);

        if ($item) {
            $this->blackListRepository->reindexModel($item, true);            
        }         

        return response()->json([
            'message'   => 'Добавлено'
        ], 200);
		
    }   

    public function delete($id){ 	
        
      	$model = $this->blackListRepository->findBy('phone', $id);                

        $this->blackListRepository->deleteFromIndex($model);            

        return $this->blackListRepository->delete($model->id);

	}	  

	protected function getSearchRepository()
    {
        return [];
    }    

	protected function addSearchConditions(User $user=null,array $filters=null)
    {
        return $filters;
    }

    public function getPermissionQuery(){
        return $this->permissionQuery;
    }

    public function getExportToExcelLib(){
        return $this->exportToExcel;
    }

}