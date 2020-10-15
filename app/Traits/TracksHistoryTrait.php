<?php
namespace App\Traits;
use App\Models\History;
use Auth;
trait TracksHistoryTrait
{
    
    protected function track($model, callable $func = null, $class = null, $id = null)
    {
        // Allow for overriding of table if it's not the model table        
        $class = $class ?: get_class($model);
        // Allow for overriding of id if it's not the model id
        $id = $id ?: $model->id;               
        // Allow for customization of the history record if needed        
        // Get the dirty fields and run them through the custom function, then insert them into the history table
        $updates = $this->getUpdated($model)->toArray();        
             
        if(!empty($updates)){
            
            $user_id = null;
            // Если нет ID пользователя в системе то присваеваем ID 1 - загружен по API
            if(isset(Auth::user()->id)){
                $user_id = Auth::user()->id;
            }else{
                $user_id = 1;
            }

            if(isset($updates['is_double']))
                $user_id = 1;

            History::create([
                'reference_table' => $class,
                'reference_id'    => $id,
                'actor_id'        => $user_id,
                'body' => json_encode(['main' => $updates],JSON_UNESCAPED_UNICODE)
            ]);
        }
             
    }    
    
    protected function getUpdated($model)
    {
        return collect($model->getDirty())->filter(function ($value, $key) {
            // We don't care if timestamps are dirty, we're not tracking those
            return !in_array($key, ['created_at', 'updated_at']);
        });
    }

    /*protected function track($model, callable $func = null, $table = null, $id = null)
    {
        // Allow for overriding of table if it's not the model table
        $table = $table ?: $model->getTable();
        // Allow for overriding of id if it's not the model id
        $id = $id ?: $model->id;               
        // Allow for customization of the history record if needed
        $func = $func ?: [$this, 'getHistoryBody'];
        // Get the dirty fields and run them through the custom function, then insert them into the history table
        $this->getUpdated($model)
             ->map(function ($value, $field) use ($func) {
                return call_user_func_array($func, [$value, $field]);
             })
             ->each(function ($fields) use ($table, $id) {
                History::create([
                    'reference_table' => $table,
                    'reference_id'    => $id,
                    'actor_id'        => Auth::user()->id,
                ] + $fields);
             });
    }    
    protected function getHistoryBody($value, $field)
    {
        return [
            'body' => "Updated {$field} to ${value}",
        ];
    }
    protected function getUpdated($model)
    {
        return collect($model->getDirty())->filter(function ($value, $key) {
            // We don't care if timestamps are dirty, we're not tracking those
            return !in_array($key, ['created_at', 'updated_at']);
        })->mapWithKeys(function ($value, $key) {
            // Take the field names and convert them into human readable strings for the description of the action
            // e.g. first_name -> first name
            return [str_replace('_', ' ', $key) => $value];
        });
    }*/
}
