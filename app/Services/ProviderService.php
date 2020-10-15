<?php
namespace App\Services;

use App\Repositories\ProviderRepository;
use App\Models\Provider;
use App\Models\User;
use RuntimeException;
use Auth;
use App\Queries\PermissionQuery;

class ProviderService extends Service
{
    protected $repository;
    protected $permissionQuery;

    public function __construct(ProviderRepository $repository, PermissionQuery $permissionQuery)
    {
        $this->repository = $repository;
        $this->permissionQuery = $permissionQuery;
    }
    
    public function index($request){
        $list = $this->dxSearch($request);
        return $list;
    }
    
    public function create($data, $reindex = false)
    {
        $provider = $this->repository->create($data);

        if ($provider) {
            if ($reindex) {
                $this->repository->reindexModel($provider, true);
            }
            return $provider;
        }
        return false;
    }

    public function update($id, $data, $reindex = false)
    {
        $provider = null;
        
        $data = $this->repository->update($data, $id);
        if ($data) {
            $provider = $this->repository->find($id);
            
            if ($reindex) {
                $this->repository->reindexModel($provider, true);
            }
        }
        
        return $data;
    }
    
    public function getImageExtension($file)
    {
        $extensions = ["png"=>"png", "jpeg"=>"jpg", "gif"=>"gif"];
        $finfo = finfo_open(FILEINFO_MIME);
        $mime = explode("/", explode(";",finfo_file($finfo, $file->getPathName()))[0]);
        if ($mime[0] == "image" && array_key_exists($mime[1], $extensions)) {
            return $extensions[$mime[1]];
        } else {
            return false;
        }
    }
    
    public function resizeImage($file, $ext, $w, $h, $crop = false)
    {
        list($width, $height) = getimagesize($file);
        $r = $width / $height;
        if ($crop) {
            if ($width > $height) {
                $width = ceil($width-($width*abs($r-$w/$h)));
            } else {
                $height = ceil($height-($height*abs($r-$w/$h)));
            }
            $newwidth = $w;
            $newheight = $h;
        } else {
            if ($w/$h > $r) {
                $newwidth = $h*$r;
                $newheight = $h;
            } else {
                $newheight = $w/$r;
                $newwidth = $w;
            }
        }
        switch ($ext) {
            case 'jpg':
                $src = imagecreatefromjpeg($file);
                break;
            
            case 'png':
                $src = imagecreatefrompng($file);
                break;
            
            case 'gif':
                $src = imagecreatefromgif($file);
                break;
        }
        
        $dst = imagecreatetruecolor($newwidth, $newheight);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

        return $dst;
    }

    protected function getSearchRepository()
    {
        return $this->repository;
    }

    protected function addSearchConditions(User $user=null, array $filters=null)
    {
        return $filters;
    }

    protected function getPermissionQuery(){
        return $this->permissionQuery;
    }

    protected function getExportToExcelLib(){
        return null;
    }
}