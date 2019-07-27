<?php

use Illuminate\Database\Seeder;
// use Spatie\Permission\Models\Role;
// use Spatie\Permission\Models\Permission;
use Silvanite\Brandenburg\Role;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

      Role::create([
          'name' => 'Administrator',
          'slug' => 'administrator',
      ]);

      Role::create([
          'name' => 'Editor',
          'slug' => 'editor',
      ]);

      Role::create([
          'name' => '微信用户',
          'slug' => User::DEFAULT_ROLE,
      ]);

      Role::create([
          'name' => '公众号用户',
          'slug' => User::MP_ROLE,
      ]);

      Role::create([
          'name' => '婚恋用户',
          'slug' => 'seeker',
      ]);
      // // 重置角色和权限的缓存
      // app()['cache']->forget('spatie.permission.cache');

      // $role = Role::create(['name' => 'wx']);//wxUser微信用户 openId@wx
      // $role = Role::create(['name' => 'mp']);//wxUser微信用户 ==>关联 ==〉wxAccount微信公众用户 openId@mp
      // $role = Role::create(['name' => 'seeker']);//婚恋用户


      // // 创建权限
      // Permission::create(['name' => 'edit posts']);
      // Permission::create(['name' => 'delete posts']);
      // Permission::create(['name' => 'publish posts']);
      // Permission::create(['name' => 'unpublish posts']);

      // // 创建角色并赋予已创建的权限
      // $role = Role::create(['name' => 'writer']);
      // $role->givePermissionTo('edit posts');
      // $role->givePermissionTo('delete posts');

      // $role = Role::create(['name' => 'admin']);
      // $role->givePermissionTo('publish posts');
      // $role->givePermissionTo('unpublish posts');
    }
}
