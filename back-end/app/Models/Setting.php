<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';
    public $timestamps = false;
    protected $fillable = ['input', 'value', 'label'];

    protected static function booted()
    {
        // On update: if label was set to null (e.g. not provided), keep original label in DB
        static::updating(function (self $model) {
            // If label attribute exists in attributes and is strictly null, restore original value
            $attrs = $model->getAttributes();
            if (array_key_exists('label', $attrs) && $attrs['label'] === null) {
                $original = $model->getOriginal('label');
                $model->setAttribute('label', $original);
            }
        });
    }
}
