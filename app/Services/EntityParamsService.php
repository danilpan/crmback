<?php
namespace App\Services;

use App\Repositories\EntityParamsRepository;
use App\Repositories\LnkRoleEntityParamsRepository;

class EntityParamsService
{ 
    protected $entityParamsRepository;
    protected $lnkREPRepository;
    
    public function __construct(
        EntityParamsRepository $entityParamsRepository,
        LnkRoleEntityParamsRepository $lnkREPRepository
    ){
        $this->entityParamsRepository = $entityParamsRepository;
        $this->lnkREPRepository = $lnkREPRepository;
    }

    public function getByRole($entity_id, $role_id){

        $selectedItems = $this->lnkREPRepository->findWhere([
            'role_id' => $role_id,
            'entity_id' => $entity_id
        ]);

        $items = $this->entityParamsRepository->findAllBy("entity_id",$entity_id);

        $list = [];

        foreach($items as $item){
            foreach($selectedItems as $sItem){
                if($sItem['entity_param_id'] == $item['id']){
                    $item['selected'] = true;
                }
            }

            if(!$item['selected'])
                $item['selected'] = false;
                $item['opened'] = false;
                $item['disabled'] = false;
                $item['loading'] = false;
            array_push($list, $item);
        }
        $data['data'] = $list;
        return $data; 
    }

    public function     getByRolePermitted($entity_id, $role_id, $other_role_id, $parent_id){
        
        $myItems = $this->lnkREPRepository->findWhere([
            'role_id' => $role_id,
            'entity_id' => $entity_id
        ]);
        $list = [];
        foreach($myItems as $item){
            $list[]=$item['entity_param_id'];
        }
        
        $myOtherItems = $this->lnkREPRepository->findWhere([
            'role_id' => $other_role_id,
            'entity_id' => $entity_id
        ]);

        $list = [];

        $items = $this->entityParamsRepository->findWhere([
                'parent_id' => $parent_id,
                'entity_id' => $entity_id
            ]);
           
        $is_parent_item_have_access = $this->IsParentTrue($parent_id, $myItems);
        $is_parent_item_selected = $this->IsParentTrue($parent_id, $myOtherItems);
        
        foreach($items as $item){

            $is_have_access = false;
            if($is_parent_item_have_access){
                $is_have_access = true;
            } else {
                foreach($myItems as $sItem)
                    if($sItem['entity_param_id'] == $item['id'])
                        $is_have_access = true;
            };

            $is_selected = false;
            if($is_parent_item_selected){
                $is_selected = true;
            }else{
                foreach($myOtherItems as $sItem)
                    if($sItem['entity_param_id'] == $item['id'])
                        $is_selected = true;    
            };

            if( $role_id == $other_role_id){
                $is_have_access = false;
            }

            $i =[
                'id' => $item['id'],
                'text' => $item['name'].' '.$item['id'],
                'value' => true,
                'selected' => $is_selected,
                'disabled'=> !$is_have_access,
                'isLeaf' => false
            ];

            $list[] = $i;
        };
        $data["data"] = $list;
        return $data;
    }

    public function IsParentTrue($parent_id, $items){
        if ($parent_id == null)
            return false;

        $parent_item = $this->entityParamsRepository->find($parent_id);
        
        foreach($items as $item)
            if($parent_item['id'] == $item['entity_param_id'])
                return true;
        
        if($parent_item['parent_id'] != null)
            return $this->IsParentTrue($parent_item['parent_id'], $items);

        return false;
    }

}
