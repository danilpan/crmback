<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Requests\Api\V2\CurrencyRequest;
use App\Repositories\CurrencyRepository;
use App\Http\Requests\Api\V2\SearchRequest;
use App\Models\Currency;

class CurrencyController extends Controller
{

    public function getList(SearchRequest $request, CurrencyRepository $repository){
        
        $items = $repository->all();

        return $items;
    }

    public function getById($id, CurrencyRepository $repository)
    {
        $item = $repository->find($id);

        return $item;
    }

    public function create(CurrencyRequest $request, CurrencyRepository $repository)
    {
        $data = $request->validated();
        $item = $repository->create($data);

        return $item;
    }

    public function update($id, CurrencyRequest $request, CurrencyRepository $repository)
    {
        $item = $repository->update( $request->validated(), $id, "id");
        return $item;
    }

    public function delete($id, CurrencyRepository $repository)
    {
        return $repository->delete($id);
    }
}
