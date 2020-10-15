<?php
namespace App\Services;
use App\Models\OrdersDialSteps;
use App\Repositories\OrdersRepository;


class OrdersDialStepsService 
{
    protected $ordersRepository;
    public function __construct(
        OrdersRepository $ordersRepository
	){
        $this->ordersRepository = $ordersRepository;

	}

    public function createOrUpdate($data)
    {
        $result = OrdersDialSteps::updateOrCreate(
		    ['queue_id' => $data['queue_id'], 'order_id' => $data['order_id']],
		    ['dial_step' => $data['dial_step'], 'dial_time' => $data['dial_time']]);

        $order  = $this->ordersRepository->find($data['order_id']);
        $this->ordersRepository->reindexModel($order, true);

        return $result;
    }

}
