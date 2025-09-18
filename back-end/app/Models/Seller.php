<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Sale;
use Illuminate\Support\Facades\Hash;

class Seller extends Model
{
    protected $fillable = [
        'name',
        'email',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Seller has many sales
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

        protected static function booted()
        {
            static::created(function (Seller $seller) {
            });

            static::updated(function (Seller $seller) {
                $user = $seller->user;
                if ($user) {
                    $changed = false;
                    if ($seller->wasChanged('name') && $user->name !== $seller->name) { $user->name = $seller->name; $changed = true; }
                    if ($seller->wasChanged('email') && $user->email !== $seller->email) { $user->email = $seller->email; $changed = true; }
                    // If controller set a transient 'temp_password' property, sync it as well
                    if (!empty($seller->temp_password)) {
                        $user->password = Hash::make($seller->temp_password);
                        $changed = true;
                    }
                    if ($changed) {
                        $user->save();
                    }
                }
            });
        }

}
