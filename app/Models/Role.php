<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as ModelsRole;

class Role extends ModelsRole
{
    use HasFactory;
    public $table = "roles";
    protected $fillable =[
        "name",
        "guard_name"
    ];
    public function RPS ()
    {
        return $this->hasMany(Role_and_Permission::class,'role_id');
    }

}
