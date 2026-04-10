<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'role_name',
    ];

    /**
     * Get the users for the role.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the role name mapped to Lao language.
     */
    public function getRoleNameLaoAttribute()
    {
        $map = [
            'admin' => 'ຜູ້ເບິ່ງແຍງລະບົບ',
            'head_of_department' => 'ຫົວໜ້າພາກສ່ວນ',
            'head_of_finance' => 'ຫົວໜ້າການເງິນ',
            'deputy_head_of_faculty' => 'ຮອງຄະນະບໍດີ',
            'head_of_faculty' => 'ຄະນະບໍດີ',
            'accountant' => 'ນັກບັນຊີ',
            'requester' => 'ຜູ້ສະເໜີຂໍ',
            'cashier' => 'ແຄັດເຊຍ / ຜູ້ຈ່າຍເງິນ',
            'revenue_officer' => 'ເຈົ້າໜ້າທີ່ລາຍຮັບ',
            'treasurer' => 'ຜູ້ຮັກສາຄັງເງິນ',
            'treasury_reconciliation_officer' => 'ເຈົ້າໜ້າທີ່ກວດສອບຄັງເງິນ',
        ];

        return $map[$this->role_name] ?? $this->role_name;
    }
}
