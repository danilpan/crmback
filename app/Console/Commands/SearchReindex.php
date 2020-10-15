<?php
namespace App\Console\Commands;

class SearchReindex extends SearchCreateIndex
{
    protected $signature = 'search:reindex {index?}';

    protected $description = 'Reindex all data on selected index';


    public function handle()
    {
        $index  = $this->argument('index');

        foreach ($this->repositories as $repo) {
            if(!$index || $index == $repo->getIndex()) {
                $this->reindex($repo);
            }
        }
    }

    protected function reindex($repo)
    {
        $model      = $repo->makeModel();
        $total      = $model->count();
        if(!$total) {
            return;
        }

        $bar    = $this->output->createProgressBar($total);


        $bar->setMessage('reindex "' . $repo->getIndex());
        $bar->setFormat('%message%  %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%');

        $model->chunk(1000, function($collection) use ($model, $repo, $bar) {
            $relations  = $repo->getSearchRelations();
            if(count($relations)) {
                $collection->load($relations);
            }

            $params = ['body' => []];
            foreach($collection as $model) {
                $params['body'][] = [
                    'index' => [
                        '_index'    => $repo->getIndex(),
                        '_type'     => $repo->getType(),
                        '_id'       => $model->id
                    ]
                ];

                $params['body'][] = $repo->prepareSearchData($model);
            }

            $this->elasticClient->bulk($params);
            $bar->advance(count($collection));
        });


        $bar->finish();
        $this->line('');
    }
}