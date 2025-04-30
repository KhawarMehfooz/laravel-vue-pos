<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AppSettingsController extends Controller
{
    public function index(){
        $settings = Auth::user()->settings;
        if(!$settings){
            $settings = Setting::create([
                'user_id'=>Auth::id(),
                'business_name'=>'Your Business name',
                'business_location'=>'Your Business Location',
                'business_contact'=>'99999999',
                'business_email'=>'email@example.com',
                'currency_symbol'=>'PKR',
                'stock_source_label'=>'Distributor'
            ]);
        }

        return Inertia::render('settings/appSettings',[
            'settings'=>$settings
        ]);
    }
}
