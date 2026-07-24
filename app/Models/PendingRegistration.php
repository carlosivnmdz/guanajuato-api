<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'first_name',
    'middle_name',
    'last_name',
    'birthday',
    'country',
    'email',
    'phone',
])]
class PendingRegistration extends Model
{
    protected function casts(): array
    {
        return [
            'birthday' => 'date',
        ];
    }


    public function otpCodes()
    {
        return $this->morphMany(OtpCode::class, 'otpable');
    }
}
