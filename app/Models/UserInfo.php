<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\UserInfo
 *
 * @property int $id
 * @property int $user_id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $middle_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereMiddleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereUserId($value)
 * @mixin Eloquent
 * @property string|null $mobile_number
 * @property string|null $sex
 * @property string|null $profile_picture_url
 * @property mixed|null $birthday
 * @property string|null $home_address
 * @property string|null $barangay
 * @property string|null $city
 * @property string|null $region
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereBarangay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereHomeAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereMobileNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereProfilePictureUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserInfo whereSex($value)
 */
class UserInfo extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'middle_name',
        'mobile_number',
        'sex',
        'profile_picture_url',
        'birthday',
        'home_address',
        'barangay',
        'city',
        'region',
        'tenant_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'id', 'user_id', 'created_at', 'updated_at', 'deleted_at'
    ];

    /**
     * UserInfo belongs to a User
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tenant()
    {
        return $this->hasOne(Tenant::class, 'id', 'tenant_id');
    }

    public function getFullNameAttribute()
    {
        $middleName = $this->middle_name == ""
            ? $this->middle_name
            : sprintf(' %s', $this->middle_name);

        return sprintf('%s%s %s', $this->first_name, $middleName, $this->last_name);
    }
}
