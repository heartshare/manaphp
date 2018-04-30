<?php
namespace App\Admin\Areas\Menu\Models;

use App\Admin\Areas\Rbac\Models\Permission;
use ManaPHP\Db\Model;

class Item extends Model
{
    public $item_id;
    public $item_name;
    public $group_id;
    public $permission_id;
    public $display_order;
    public $creator_name;
    public $updator_name;
    public $created_time;
    public $updated_time;

    public function getSource($context = null)
    {
        return 'menu_item';
    }

    public function rules()
    {
        return [
            'item_name' => ['length' => '5-32'],
            'group_id' => 'exists',
            'permission_id' => ['exists' => Permission::class],
            'display_order' => ['range' => '0-127']
        ];
    }
}