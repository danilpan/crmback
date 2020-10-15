<?php
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

    //    $this->call(DeliveryTypeSeeder::class);
    //    $this->call(ProductsSeeder::class);
        // $this->call(UnloadsSeeder::class);
        $this->call(PermissionsSeeder::class);
        // $this->call(TestStructureSeeder::class);
        $this->call(OrganizationSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(GeosSeeder::class);
        // $this->call(ProductCategorySeeder::class);
        $this->call(ProjectCategorySeeder::class);
        //$this->call(CurrencySeeder::class);
        $this->call(TrafficSeeder::class);
        $this->call(CallCenterSeeder::class);
        $this->call(EntitiesSeeder::class);
        $this->call(EntityParamsSeeder::class);
        $this->call(RoleGroupsSeeder::class);
        $this->call(RolesSeeder::class);
        $this->call(SetAdminPermissionsSeeder::class);
        $this->call(ProjectCategoryKcSeeder::class);
        $this->call(OrderAdvertSourceSeeder::class);
        $this->call(DeviceTypeSeeder::class);
        // $this->call(OrdersSeeder::class);
        $this->call(ClientTypeSeeder::class);

    }
}
