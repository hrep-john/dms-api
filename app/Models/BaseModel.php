<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class BaseModel extends Model implements Auditable
{
    use HasFactory;
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    public function getFormattedCreatedByAttribute()
    {
        return User::find($this->created_by)->user_info->full_name ?? '';
    }

    public function getFormattedUpdatedByAttribute()
    {
        return User::find($this->updated_by)->user_info->full_name ?? '';
    }

    public function getFormattedCreatedAtAttribute()
    {
        return Carbon::parse($this->created_at)->format('Y-m-d H:i:s');
    }

    public function getFormattedUpdatedAtAttribute()
    {
        return Carbon::parse($this->updated_at)->format('Y-m-d H:i:s');
    }
}
