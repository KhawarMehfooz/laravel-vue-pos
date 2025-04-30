<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'user_id',
        'business_name',
        'business_location',
        'business_contact',
        'business_email',
        'business_logo',
        'currency_symbol',
        'stock_source_label'
    ];

    protected $attributes = [
        'stock_source_label'=>'Distributor'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public static function getOrCreateSettings($userId, $data){
        return static::updateOrCreate(
            ['user_id'=>$userId],
            $data
        );
    }
}
