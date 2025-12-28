<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'image',
        'name',
        'username',
        'password',
        'email',
        'role',
        'phone',
        'address',
        'city',
        'country',
        'designation',
        'salary',
        'joining_date'
    ];

    protected $hidden = [
        'password',
    ];

    // Available roles
    public static function getRoles()
    {
        return [
            'Admin',
            'Employee',
            'Manager',
            'Staff',
            'Cashier',
            'Biller',
            'HR',
            'Receptionist',
        ];
    }
    /**
 * Employee has many payrolls
 */
public function payrolls()
{
    return $this->hasMany(Payroll::class, 'employee_id');
}
}