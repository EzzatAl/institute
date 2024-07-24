<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission as ModelsPermission;

class Permission extends ModelsPermission
{
    use HasFactory;
    public $table = "permissions";
    protected $fillable =[
      "name",
      "guard_name"
    ];
    public function PRS ()
    {
        return $this->hasMany(Role_and_Permission::class,'permission_id');
    }
}
