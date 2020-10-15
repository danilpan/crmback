<?php
namespace App\Services;

use App\Libraries\ExportToExcel;
use App\Models\Attempt;
use App\Models\User;
use App\Queries\PermissionQuery;
use App\Repositories\AttemptsRepository;
use App\Repositories\UsersRepository;
use Auth;
use Illuminate\Support\Facades\Storage;

class AttemptsService extends Service
{
    protected $attemptsRepository;
    protected $permissionQuery;
    protected $exportToExcel;
    protected $usersService;
    protected $rolesService;

    public function __construct(
        AttemptsRepository $attemptsRepository,
        RolesService $rolesService,
        PermissionQuery $permissionQuery,
        UsersService $usersService,
        ExportToExcel $exportToExcel,
        UsersRepository $usersRepository
    )
    {
        $this->attemptsRepository = $attemptsRepository;
        $this->permissionQuery = $permissionQuery;
        $this->exportToExcel = $exportToExcel;
        $this->usersService = $usersService;
        $this->rolesService = $rolesService;
    }

    public function create($data, $reindex = false)
    {
        $image_name = "attempts/" . trim($data['source']) . "/" . str_random(10) . '.jpg';
        Storage::disk('public_uploads')->put($image_name, base64_decode($data['image']));
        $data['image'] = $image_name;
        $attempt = $this->attemptsRepository->create($data);

        if ($attempt) {
            if ($reindex) {
                $this->attemptsRepository->reindexModel($attempt, true);
            }

            return $attempt;
        }

        return false;
    }

    public function getById($id)
    {
        return $this->attemptsRepository->find($id);
    }

    public function isPlateRegistered($num)
    {
        $registered = 'false';

        $user = User::where('plates', 'like', '%'.$num.'%')->first();

        if($user)
            $registered = 'true';

        return $registered;
    }

    public function delete(int $id)
    {
        $attempt = $this->attemptsRepository->find($id);
        $attempt->organizations()->delete();
        if ($attempt) {
            $this->attemptsRepository->deleteFromIndex($attempt);

            return $this->attemptsRepository->delete($id);
        }

        return false;
    }

    protected function getSearchRepository()
    {
        return $this->attemptsRepository;
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

    public function upload_image($user_id, $image){
        $user = User::find($user_id);
        $user_folder_name = trim($user->first_name."_".$user->last_name);
        $image_link = Storage::disk('public_uploads')->putFile("avatars/$user_folder_name", $image['image_upload']);
        $model = new UserImage();
        $model->user_id = $user_id;
        $model->image = $image_link;
        $model->image_type_id = $image['image_type_id'];

        if(!empty($image['is_main'])) {
            if($image['is_main'] == true) {
                $this->unset_main_image($user_id);
            }
            $model->is_main = $image['is_main'];
        }
        $model->save();

        return $model;

    }
}