<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

class Role_and_Permission extends Model
{
    use HasFactory;
    public $table = 'role_has_permissions';
    protected $fillable = [
        'permission_id',
        'role_id'
    ];
    public function roles()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
    public function permissions()
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }
    public function Permission()
    {
        return $this->hasMany(User::class, 'role_id');
    }
    public function permissions_user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Permission::class, 'permission_id')
            ->whereIn('id', function (Builder $query) {
                $query->select('permission_id')
                    ->from('role_has_permissions')
                    ->join('users', 'role_has_permissions.role_id', '=', 'users.role_id')
                    ->join('roles', 'users.role_id', '=', 'roles.id')
                    ->whereColumn('roles.id', '=', 'role_has_permissions.role_id');
            });
    }
}
