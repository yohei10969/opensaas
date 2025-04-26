<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Individual;
use App\Models\Personal;

class SettingsController extends Controller
{
    /**
     * ここでは全体の設定をコントロールします。
     * サービス特有の設定などは、個別のコントローラーファイル（例：ServicesController.php）を作成してそこで設定をしてください。
     */

    public function personal()
    {
        
    }
}
