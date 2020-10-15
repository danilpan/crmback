<?php
namespace App\Collections;

use App\Models\ModelIterface as Model;

class SuggestResultCollection extends SearchResultCollection
{

    public function __construct($items = [], Model $model = null, $searchData = [], $relations = [])
    {
        $this->model    = $model;

        if(!empty($searchData) && !empty($searchData[0]['options'])) {
            foreach ($searchData[0]['options'] as $itemData) {
                $item       = $this->createFromSearchData($this->model, $itemData['_source'], $relations);

                $this->loadRelations($item, $itemData['_source'], $relations);


                $items[]    = $item;
            }
        }

        $this->total    = count($items);


        parent::__construct($items);
    }

    public function getTotal()
    {
        return count($this->items);
    }
}