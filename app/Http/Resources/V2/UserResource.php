<?php
namespace App\Http\Resources\V2;

//use App\Models\Permission;

class UserResource extends Resource
{
    public function toArray($request)
    {
        $data   = [
            'id'                => $this->id,
            'login'             => $this->login,
            'mail'              => $this->mail,
            'first_name'        => $this->first_name,
            'last_name'         => $this->last_name,
            'middle_name'       => $this->middle_name,
            'name'              => $this->name,
            'phone'             => $this->phone,
            'phone_office'      => $this->phone_office,
            'ip'                => $this->ip,
            'last_online'       => $this->last_online,
            'speaker_status'    => $this->speaker_status,
            'organization_id'   => $this->organization_id,
            'is_work'           => $this->is_work,
            'out_calls'         => $this->out_calls,
            'permission'        => $this->permission,
            'company'           => $this->company()->get()->map(function ($organization) {return $organization->only(['id', 'title', 'role_id']);}),
            'organization'      => $this->organization()->get()->map(function ($organization) {return $organization->only(['id', 'title','parent_id', 'is_company']);}),
            'atsUsers'          => $this->atsUsers()->get(),
            'role'              => $this->organization->role()->get(),
            'pseudo_session'    => $this->pseudo_session,
            'is_show_work'      => $this->is_show_work,
            'mainlink'          => $this->mainlink,
            'manager'           => $this->manager,
            'history'           => ($this->history_с) ? OrderHistoryResource::collection($this->history_с) : [],
            'user_images'       => $this->images()->orderBy('is_main', 'desc')->get(),
            'telegram'          => $this->telegram,
            'plates'            => $this->plates

        ];
        
        /*// FIXME: Сделать аналогичное получение последнего статуса везде. Сейчас почти везде статусы отправляются всей пачкой.
        if ($data['atsUsers']) {
            $data['atsUsers'] = $data['atsUsers']->map(function($ats_user) {
                $last_status = $ats_user->status->last();
                unset($ats_user->status);
                $ats_user->status = $last_status;
                return $ats_user;
            });
        }*/

        // if($this->permission) {
        //     $all                = array_only($this->permission->toArray(), Permission::PERMISSION_FIELDS);
        //     $data['permission'] = array_dot($all);
        // }

        return $data;
    }
}
