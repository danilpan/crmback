<?php
namespace App\Services;

use App\Models\History;
use App\Models\UserImage;
use Auth;
use Hash;

use App\Repositories\UsersRepository;
use App\Services\OrganizationsService;
use App\Services\ProjectsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Auth\Guard;
use App\Queries\PermissionQuery;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UsersService extends Service
{

    protected $permissionQuery;
    protected $usersRepository;
    public function __construct(
        UsersRepository $usersRepository, 
        OrganizationsService $organizationsService,
        PermissionQuery $permissionQuery,
        ProjectsService $projectsService)
    {
        $this->usersRepository = $usersRepository;
        $this->organizationsService = $organizationsService;
        $this->projectsService = $projectsService;
        $this->permissionQuery = $permissionQuery;
    }

    public function create($data, $reindex = false)
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user = $this->usersRepository->create($data);

        if ($user) {
            if ($reindex) {
                $this->usersRepository->reindexModel($user, true);
            }
        }

        unset($user['password']);
        return $user;
    }

    public function update($id, $data, $reindex = false)
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        $user = $this->usersRepository->update($data, $id);
        $result = null;
        if ($user) {
            if ($reindex) {
                $result = $this->usersRepository->reindexModel($user, true);
            }else{
                $result = $user;
            }
        }

        unset($user['password']);
        return $result;
    }

    public function getProfile(){
        $id = Auth::user()->id;
        $user = $this->usersRepository->find($id);
        $user = $this->prepareHistory($user);

        return $user;
    }

    public function updateProfile($data, $reindex = false){
        $id = Auth::user()->id;
        $user = $this->usersRepository->update($data, $id);

        $result = null;

        if ($user) {
            if ($reindex) {
                $result = $this->usersRepository->reindexModel($user, true);
            } else {
                $result = $user;
            }
        }

        if (isset($data['user_images']) && !empty($data['user_images'])) {
            $this->add_images($user, $data['user_images']);
        }

        $user = $this->prepareHistory($user);

        return $user;
    }

    public function updatePassword($id, $data)
    {
        $current_password = $data['current_password'];
        $new_password = $data['new_password'];
        $user = $this->usersRepository->find(auth()->user()->id);
        $result = null;

        if (!Hash::check($current_password, $user->password)) {
            throw ValidationException::withMessages(["error" => "wrong current password"]);
        } else {
            $data['password'] = Hash::make($new_password);
            $user = $this->usersRepository->update($data, $user->id);
            $result = $this->usersRepository->reindexModel($user, true);
        }

        return $result;
    }

    public function add_images($user, $images){
        $images_ids = [];

        foreach($images as $image){
            //если картинка уже в базе
            if(isset($image['id']) && !empty($image['id'])){
                if(!empty($image['is_main']) && $image['is_main'] == true) {
                    $image_temp = UserImage::find($image['id']);
                    $this->unset_main_image($user->id);
                    $image_temp->is_main = true;
                    $image_temp->save();
                    $this->imageHistory($image_temp);
                }
                $images_ids[] = $image['id'];
            }
            //если картинки нет в базе
            else{
                if(!empty($image['image_upload'])) {
                    $image_temp = $this->upload_image($user->id, $image);
                    $this->imageHistory($image_temp);
                    $images_ids[] = $image_temp->id;
                }
            }

        }
        $delete_user_images = $user->images->whereNotIn('id', $images_ids);
        $delete_images_count = $user->images()->whereNotIn('id', $images_ids)->delete();
        if($delete_images_count > 0){
            foreach ($delete_user_images as $d_image) {
                $this->imageHistory($d_image, true);
            }
        }
    }

    public function upload_image($user_id, $image){
        $user = User::find($user_id);
        $user_folder_name = trim($user->first_name."_".$user->last_name);
        $image_link = Storage::disk('public_uploads')->putFile("avatars/$user_folder_name", $image['image_upload']);
        $model = new UserImage();
        $model->user_id = $user_id;
        $model->image = $image_link;
        $model->image_type_id = $image['image_type_id'];
        if($image['image_type_id'] == 2){
            shell_exec("cd /home/dev/PycharmProjects/diplom; python3 skeleton_rec/encode_skeleton.py $image_link $user_id");
        }

        if(!empty($image['is_main'])) {
            if($image['is_main'] == true) {
                $this->unset_main_image($user_id);
            }
            $model->is_main = $image['is_main'];
        }
        $model->save();

        return $model;

    }

    public function unset_main_image($user_id)
    {
        $user_images = UserImage::where('user_id', $user_id)->get();
        foreach ($user_images as $image){
            $image->is_main = false;
            $image->update();
        }
    }

    public function imageHistory($image, $delete = false){
        $image_db = $image->toArray();
        if($image->wasRecentlyCreated == 1){
            $image_db['type'] = 'new';
        }elseif(!empty($image->getChanges())){
            $image_db['type'] = 'change';
        }elseif($delete){
            $image_db['type'] = 'delete';
        }

        unset($image_db['updated_at']);
        unset($image_db['created_at']);

        if(isset(Auth::user()->id)){
            $user_id = Auth::user()->id;
        }else{
            $user_id = 1;
        }

        if(isset($image_db['type']))
            History::create([
                'reference_table' => $this->usersRepository->model(),
                'reference_id'    => $image->user_id,
                'actor_id'        => $user_id,
                'body'            => json_encode(['user_images' => $image_db],JSON_UNESCAPED_UNICODE)
            ]);
    }

    public function prepareHistory($user){
        if ($user->history()) {
            $user->history_с = $user->history()->get()->map(function ($item) {
                $history_body = json_decode($item['body']);
                $item['body'] = $history_body;
                return $item;
            });
        }
        return $user;
    }

    public function searchUsers($q, $organization_id, $user, $page = 1, $size = 20, $is_work = 0){
        $q = mb_strtolower($q,'UTF-8');
        $main = [];
        // es файл в database/users-users.es    

        // Основной запрос
        if($q){
            $query['should'][]['wildcard']['first_name'] = '*'.$q.'*';
            $query['should'][]['wildcard']['last_name'] = $q;
            $query['should'][]['wildcard']['middle_name'] = '*'.$q.'*';
            $query['should'][]['wildcard']['login'] = '*'.$q.'*';
            $query['should'][]['wildcard']['atsUsers.login'] = '*'.$q.'*';
            $main['constant_score']['filter']['bool']['should'] = $query['should'];
        }
        //Доступ
        $opByRole = $this->projectsService->getOrganizationsProjectsByRole($user['organization']->role_id);
        $opByRole['organizations'][] = $user['organization_id'];
        $opByRole['organizations'] = array_merge($opByRole['organizations'], $this->organizationsService->getOrganizations($user['organization_id'])->pluck('id')->toArray());
        //Фильтр
        $filter['must']['bool']['should']['terms']['organization_id'] = $opByRole['organizations'];
        if(isset($organization_id))
            $filter['must']['bool']['must'][]['terms']['organization_id'][] = intval($organization_id);
        
        if($is_work)    
            $filter['must']['bool']['must'][]['terms']['is_work'][] = true;
        
        $main['constant_score']['filter']['bool']['must'] = $filter['must'];
        return $this->usersRepository->searchByParams($main, ['id'=>'asc'], $page, $size, true);
    }

    public function getPermissions($organization_id)
    {
        $permissions = DB::table('entity_params')
            ->join('lnk_role__entity_param', 'entity_params.id', '=', 'lnk_role__entity_param.entity_param_id')
            ->join('organizations', function ($join) use($organization_id) {
                $join->on('lnk_role__entity_param.role_id', '=', 'organizations.role_id')
                    ->where('organizations.id', '=', $organization_id);
            })
            ->select('entity_params.parameter')
            ->get();
            
        $list = [];
        foreach($permissions as $p){
            array_push($list, $p->parameter);
        };

        $permissions  = array_fill_keys($list, true);
        
        return $permissions;
    }

    public function can($permission, $organization_id)
    {
        $permissions = $this->getPermissions($organization_id);
        $keywords = explode(".", $permission);
        $item = "";       

        for($i=0; $i < count($keywords); $i++){
            if($i==0){
                $item = $keywords[$i];
            }else{
                $item .= ".".$keywords[$i];
            }

            if(isset($permissions[$item]))
                return true;
        }
        return false;
    }

    public function updateAllUserCompany(){
        $all = $this->usersRepository->all();
        foreach($all as $user){
            $data['company_id'] =  $this->organizationsService->getMyCompany($user['organization_id'])['id'];
            $this->update($user['id'], $data, true);
        }
    }

    public function setShowIsWork($id, $data, $reindex){
        $user = $this->usersRepository->update($data, $id);

        if ($user) {
            if ($reindex) {
                $this->usersRepository->reindexModel($user, true);
            }
        }

        unset($user['password']);
        return $user;
    }

    public function logoutByRoleChange($role_id){
        $user_ids = DB::table('users')
            ->join('organizations', 'users.organization_id', '=', 'organizations.id')
            ->where('organizations.role_id','=',$role_id)
            ->select('users.id')
            ->get()->pluck('id')->toArray();

        $user_from_role = DB::table('users')
            ->whereIn('id', $user_ids)
            ->update(['pseudo_session'=>null]);

        return $user_from_role;
    }

    public function searchUsersByOrgs($orgs){        
        $temp = [];
        foreach ($orgs as $org) {
            $temp[] = [
                'term'=>['organization_id'=>$org]
            ];    
        }
        
        $filter['must']['bool']['must'][]['bool']['should'] = $temp; 
        
        
        $main['constant_score']['filter']['bool']['must'] = $filter['must'];


        return $this->usersRepository->searchByParams($main, ['id'=>'asc'],1,10000,false);
    }


    protected function addSearchConditions(User $user=null,array $filters=null)
    {
        return $filters;
    }

    protected function getSearchRepository()
    {
        return $this->usersRepository;
    }

    protected function getPermissionQuery(){
        return $this->permissionQuery;
    }

    protected function getExportToExcelLib(){
        return null;
    }
    
}
