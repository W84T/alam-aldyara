<?php
//
//namespace Database\Seeders;
//
//use App\enums\PermissionsEnum;
//use App\enums\RolesEnum;
//use Illuminate\Database\Seeder;
//use Spatie\Permission\Models\Permission;
//use Spatie\Permission\Models\Role;
//
//class RoleSeeder extends Seeder
//{
//    /**
//     * Run the database seeds.
//     */
//    public function run(): void
//    {
//        $userRole = Role::create(['name' => RolesEnum::User->value]);
//        $adminRole = Role::create(['name' => RolesEnum::Admin->value]);
//
//        $sellProducts = Permission::create([
//            'name' => PermissionsEnum::SellProducts->value,
//        ]);
//
//        $buyProducts = Permission::create([
//            'name' => PermissionsEnum::BuyProducts->value,
//        ]);
//
//        $userRole->syncPermissions([$buyProducts]);
//        $adminRole->syncPermissions([$buyProducts, $sellProducts]);
//    }
//}
