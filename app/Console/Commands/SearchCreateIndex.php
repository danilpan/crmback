<?php

namespace App\Console\Commands;

use App\Repositories\AttemptsRepository;
use App\Repositories\Optovichok\ClientRepository;
use App\Repositories\OrderSenderRepository;
use App\Repositories\ProductCategoryRepository;
use App\Repositories\ProductsRepository;
use App\Repositories\SmsTemplatesRepository;
use App\Repositories\UserImagesRepository;
use Illuminate\Console\Command;
use Illuminate\Container\Container as App;

use Config;
use Elasticsearch\Client as ElasticClient;
use App\Repositories\Repository;

use App\Repositories\OrdersRepository;
use App\Repositories\DeliveryTypesRepository;
use App\Repositories\ProjectsRepository;
use App\Repositories\UnloadsRepository;
use App\Repositories\OrganizationsRepository;
use App\Repositories\UsersRepository;
use App\Repositories\StatusesRepository;
use App\Repositories\CallsRepository;
use App\Repositories\ProjectCategoryRepository;
use App\Repositories\ProjectPageRepository;
use App\Repositories\GeoRepository;
use App\Repositories\BlackListRepository;
use App\Repositories\PostcodeInfoRepository;
use App\Repositories\ProjectCategoryKcRepository;
use App\Repositories\OrderAdvertSourceRepository;
use App\Repositories\DeviceTypeRepository;
use App\Repositories\AtsRepository;
use App\Repositories\AtsGroupRepository;
use App\Repositories\SipRepository;
use App\Repositories\LogActivityRepository;
use App\Repositories\SipCallerIdRepository;
use App\Repositories\OutRouteRepository;
use App\Repositories\ProviderRepository;
use App\Repositories\AtsUserRepository;
use App\Repositories\AtsQueueRepository;


class SearchCreateIndex extends Command
{
    protected $signature = 'search:create_index {index?} {--reindex}';

    protected $description = 'Create search index and put mapping';

    /**
     * @var ElasticClient
     */
    protected $elasticClient;

    protected $repositories = [

    ];

    public function __construct(
        ElasticClient $elasticClient,
        AtsUserRepository $atsUserRepository,
        OutRouteRepository $outRouteRepository,
        AtsQueueRepository $atsQueueRepository,
        SipRepository $sipRepository,
        ProviderRepository $providerRepository,
        SipCallerIdRepository $sipCallerIdRepository,
        OrdersRepository $ordersRepository,
        ProductsRepository $productsRepository,
        DeliveryTypesRepository $deliveryTypesRepository,
        ProjectsRepository $projectsRepository,
        UnloadsRepository $unloadsRepository,
        OrganizationsRepository $organizationsRepository,
        UsersRepository $usersRepository,
        StatusesRepository $statusesRepository,
        CallsRepository $callsRepository,
        ProductCategoryRepository $productCategoryRepository,
        ProjectCategoryRepository $projectCategoryRepository,
        ProjectPageRepository $projectPageRepository,
        GeoRepository $geoRepository,
        BlackListRepository $blackListRepository,
        PostcodeInfoRepository $postcodeInfoRepository,
        ProjectCategoryKcRepository $projectCategoryKcRepository,
        OrderAdvertSourceRepository $orderAdvertSourceRepository,
        DeviceTypeRepository $deviceTypeRepository,
        AtsRepository $atsRepository,
        AtsGroupRepository $atsGroupRepository,
        OrderSenderRepository $orderSenderRepository,
        LogActivityRepository $logActivityRepository,
        SmsTemplatesRepository $smsTemplatesRepository,
        ClientRepository $clientRepository,
        UserImagesRepository $userImagesRepository,
        AttemptsRepository $attemptsRepository
    )
    {
        $this->elasticClient    = $elasticClient;
        $this->repositories     = [
            $outRouteRepository,
            $atsQueueRepository,
            $providerRepository,
            $atsUserRepository,
            $sipRepository,
            $sipCallerIdRepository,
            $projectsRepository,
            $ordersRepository,
            $productsRepository,
            $deliveryTypesRepository,
            $unloadsRepository,
            $organizationsRepository,
            $usersRepository,
            $statusesRepository,
            $callsRepository,
            $productCategoryRepository,
            $projectCategoryRepository,
            $projectPageRepository,
            $geoRepository,
            $blackListRepository,
            $postcodeInfoRepository,
            $orderAdvertSourceRepository,
            $deviceTypeRepository,
            $projectCategoryKcRepository,
            $atsRepository,
            $atsGroupRepository,
            $orderSenderRepository,
            $logActivityRepository,
            $atsGroupRepository,
            $smsTemplatesRepository,
            $clientRepository,
            $userImagesRepository,
            $attemptsRepository
        ];

        parent::__construct();
    }


    public function handle()
    {
        $reindex    = $this->option('reindex');
        $index      = $this->argument('index');

        foreach ($this->repositories as $repo) {
            if(!$index || $index == $repo->getIndex()) {
                $this->process($repo, $reindex);
            }
        }
    }

    protected function process($repo, $reindex)
    {
        $this->createIndex($repo);
        $this->putMappings($repo);


        if($reindex) {
            $this->call('search:reindex', ['index' => $repo->getIndex()]);
        }
    }

    protected function createIndex(Repository $repository)
    {
        $index  = $repository->getIndex(); 
        $params = [
            'index' => $index
        ];
        
        $elasticIndex   = null;
        try {
            $this->elasticClient->indices()->delete($params);

            $this->info('old index "' . $index . '" deleted');
        }
        catch (\Exception $e) {
            $this->info('old index "' . $index . '" not exist');
        }

        $this->elasticClient->indices()->create($params);

        $this->info('index "' . $index . '" created');
    }

    protected function putMappings(Repository $repository)
    {
        $mappings   = $repository->getMappings();
        $type       = $repository->getType();
        $index      = $repository->getIndex();

	$mappingQuery = [
            'index'     => $index,
            'type'      => $type,
            'body'      => [
                $type   => [
                    '_source' => [
                        'enabled' => true
                    ],
                    'properties' => $mappings
                ]
            ]
        ];


        $settingsQuery  = [
            'index' => $index,
            'body'  => [
                'analysis'  => [
                    'filter'    => [
                        'stop_url'  => [
                            'type'      => 'stop',
                            'stopwords' => ['http', 'https', 'ftp', 'www']
                        ]
                    ],
                    'analyzer'  => [
                        'url'   => [
                            'type'          => 'custom',
                            'tokenizer'     => 'lowercase',
                            'filter'        => ['stop_url']
		    	],
                        'link'   => [
                            'type'          => 'custom',
                            'tokenizer'     => 'uax_url_email',
                            'filter'        => ['stop_url']
                ],
                        'whitespace'   => [
                            'type'          => 'custom',
                            'tokenizer'     => 'whitespace',
                            'filter'        => ['stop_url']
                        ],
                        /* "rebuilt_russian"=> [
                            "type"=> "custom",
                            "tokenizer"=>  "keyword",
                            "filter"=> [
                                "lowercase",
                                "russian_stop",
                                "russian_keywords",
                                "russian_stemmer"
                            ]
                        ], */
                       'keyword'   => [
                            'tokenizer'     => 'keyword',
                            'filter'        => ['lowercase']
                        ] 
                    ],
                    "normalizer"=> [
                        "normalizer_keyword"=> [
                            "type"=> "custom",
                            "char_filter"=> [],
                            "filter" => ["lowercase", "asciifolding"]
                        ]
                    ] 
                ]
            ]
        ];


    /*
    
      "normalizer": {
        "my_normalizer": {
          "type": "custom",
          "char_filter": [],
          "filter": ["lowercase", "asciifolding"]
        }
      } 
      
      */

//        "analysis": {
//        "filter" : {
//            "stopwords_filter" : {
//                "type" : "stop",
//          "stopwords" : ["http", "https", "ftp", "www"]
//        }
//      },
//      "analyzer": {
//            "lowercase_with_stopwords": {
//                "type": "custom",
//          "tokenizer": "lowercase",
//          "filter": [ "stopwords_filter" ]
//        }
//      }
//    }

        try {
            $this->elasticClient->indices()->close(['index' => $index]);
            $this->elasticClient->indices()->putSettings($settingsQuery);
            $this->elasticClient->indices()->putMapping($mappingQuery);
            $this->elasticClient->indices()->open(['index' => $index]);

            $this->info('mapping for ' . $type . ' complete');
        }
        catch(\Throwable $exception) {
            $this->line('mapping error, type:' . $type);
            $this->line($exception->getMessage());
        }

    }
}
