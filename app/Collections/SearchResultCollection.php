<?php
namespace App\Collections;

use Illuminate\Database\Eloquent\Collection;
use App\Models\ModelIterface as Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Exception;

class SearchResultCollection extends Collection
{
    protected $total;

    protected $model;

    protected $relations;

    public function __construct($items = [], Model $model = null, $searchData = [], $relations = [], $key = null)
    {
        $this->total        = 0;
        $this->model        = $model;

        if($model && !empty($searchData)) {
            $this->total    = $searchData['hits']['total'];

            foreach ($searchData['hits']['hits'] as $itemData) {
                $newItemData    = array_merge(
                    array_get($itemData, '_source', []),
                    array_get($itemData, 'highlight', [])
                );

                if($key!=null && isset($newItemData[$key]))
                    $newItemData["key"] = $newItemData[$key];

                $item       = $this->createFromSearchData($this->model, $newItemData, $relations);

                $this->loadRelations($item, $itemData['_source'], $relations);

                $items[]    = $item;
            }
        }

        parent::__construct($items);
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function getTotalData(){
        return $this->totalData;
    }

    protected function createFromSearchData($model, $data, $relations)
    {
        array_forget($data, $relations);

        $new    = $model->newFromBuilder($data);

        return $new;
    }

    protected function loadRelations($model, $data, $relations)
    {
        foreach($relations as $relationKey) {
            $relationData   = array_get($data, $relationKey);
            if($relationData === null) {
                continue;
            }

            $this->loadRelation($model, $relationKey, $relationData, $relations);
        }
    }

    protected function loadRelation($model, $relationKey, $relationData, $relations)
    {
        $parts              = explode('.', $relationKey);
        $workModel          = $model;
        $currentRelation    = end($parts);


        foreach ($parts as $part) {
            if($workModel->relationLoaded($part)) {
                $workModel  = $workModel->$part;
            }
            else {
                if($part != $currentRelation) {
                    $workModel  = null;
                }

                break;
            }
        }

        if(!$workModel) {
            return;
        }

        $newRelations   = $this->getNewRelations($parts, $relations);
        $rel            = $workModel->$currentRelation();
        $relationModel  = $rel->getQuery()->getModel();


        if($rel instanceof HasOne || $rel instanceof BelongsTo) {
            $item   = $this->createFromSearchData($relationModel, $relationData, $newRelations);
            $workModel->setRelation($currentRelation, $item);
        }
        elseif($rel instanceof BelongsToMany || $rel instanceof HasMany) {
            $relCollection = new Collection();
            if(count($relationData)) {
                foreach ($relationData as $rd) {
                    $item   = $this->createFromSearchData($relationModel, $rd, $newRelations);
                    $relCollection->push($item);
                }
            }

            $workModel->setRelation($currentRelation, $relCollection);
        }
        else {
            throw new Exception('unknown relation type:' . get_class($rel));
        }
    }

    protected function getNewRelations($relationKeyParts, $relations)
    {
        foreach ($relationKeyParts as $keyPart) {
            $newRelations   = [];
            foreach ($relations as $relation) {
                $currentParts   = explode('.', $relation);

                if($currentParts[0] == $keyPart) {
                    array_forget($currentParts, 0);
                }

                if(!empty($currentParts)) {
                    $newRelations[] = implode('.', $currentParts);
                }
            }

            $relations  = $newRelations;
        }

        return $relations;
    }
}