<?php

use Gzero\Core\Models\Permission;
use Gzero\Core\Models\Role;
use Gzero\Core\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRolesAndPermissionsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'acl_roles',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->unique();
                $table->timestamps();
            }
        );

        Schema::create(
            'acl_permissions',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->unique();
                $table->string('category');
                $table->boolean('is_core');
                $table->timestamps();
            }
        );

        Schema::create(
            'acl_permission_role',
            function (Blueprint $table) {
                $table->integer('permission_id')->unsigned()->index();
                $table->integer('role_id')->unsigned()->index();
                $table->timestamps();
                $table->foreign('permission_id')->references('id')->on('acl_permissions')->onDelete('CASCADE');
                $table->foreign('role_id')->references('id')->on('acl_roles')->onDelete('CASCADE');
            }
        );

        Schema::create(
            'acl_user_role',
            function (Blueprint $table) {
                $table->integer('user_id')->unsigned()->index();
                $table->integer('role_id')->unsigned()->index();
                $table->timestamps();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
                $table->foreign('role_id')->references('id')->on('acl_roles')->onDelete('CASCADE');

            }
        );

        $this->createRolesAndPermissions();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('acl_user_role');
        Schema::dropIfExists('acl_permission_role');
        Schema::dropIfExists('acl_permissions');
        Schema::dropIfExists('acl_roles');
    }

    /**
     * It creates base permissions
     */
    private function createRolesAndPermissions()
    {
        $permissions = [];

        $permissions[] = [
            'name'     => 'admin-access',
            'category' => 'general',
            'is_core'  => true
        ];

        $entities = ['content', 'block', 'user', 'file', 'role'];
        foreach ($entities as $entity) {
            $permissions[] = [
                'name'     => $entity . '-create',
                'category' => $entity,
                'is_core'  => true
            ];
            $permissions[] = [
                'name'     => $entity . '-read',
                'category' => $entity,
                'is_core'  => true
            ];
            $permissions[] = [
                'name'     => $entity . '-update',
                'category' => $entity,
                'is_core'  => true
            ];
            $permissions[] = [
                'name'     => $entity . '-delete',
                'category' => $entity,
                'is_core'  => true
            ];
        }

        // Options are different
        $permissions[] = [
            'name'     => 'options-read',
            'category' => 'options',
            'is_core'  => true
        ];
        $permissions[] = [
            'name'     => 'options-update-general',
            'category' => 'options',
            'is_core'  => true
        ];
        $permissions[] = [
            'name'     => 'options-update-seo',
            'category' => 'options',
            'is_core'  => true
        ];

        Permission::insert($permissions);

        $adminRole = Role::create(['name' => 'Admin']);
        $user      = User::find(1);
        $user->roles()->attach($adminRole);
        $adminRole->permissions()->attach(Permission::all(['id'])->pluck('id')->toArray());

        $moderatorRole = Role::create(['name' => 'Moderator']);
        $permissionIds = Permission::whereIn('category', ['block', 'content', 'file'])
            ->orWhereIn('name', ['admin-access'])
            ->get(['id'])
            ->pluck('id')
            ->toArray();
        $moderatorRole->permissions()->attach($permissionIds);

    }
}
