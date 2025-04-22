<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Individual extends Model
{
    use HasFactory;
    
    protected $table = 'individuals';

    protected $fillable = [
        'uuid',
        'email',
        'password',
    ];

    // パスワードをハッシュ化
    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }
}
