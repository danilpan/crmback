<?php

use Illuminate\Database\Seeder;
use App\Repositories\ProjectCategoryKcRepository;

class ProjectCategoryKcSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       $repo = resolve(ProjectCategoryKcRepository::class);

        $data = [
            [
                'title' => 'Рублевые',
                'is_work' => true,
            ],
            [
                'title' => 'Нутра',
                'is_work' => true,
            ],
            [
                'title' => 'Комплекты',
                'is_work' => true,
            ],
            [
                'title' => 'Полурублевые',
                'is_work' => true,
            ]
        ];
        
        foreach ($data as $item) {
            $repo->create($item);
        }
    }
}
