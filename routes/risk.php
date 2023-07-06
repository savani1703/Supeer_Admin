<?php

use App\Http\Controllers\RiskManagementController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () { return view("risk.dashboard"); });
Route::post('/getCustSummery', [RiskManagementController::class, "getCustSummery"]);
Route::post('/customer/leveling/report', [RiskManagementController::class, "getCustomerLevelingReport"]);


Route::get('/custDetail', function () { return view("risk.cust-hid-detail"); });
Route::post('/get/custHidDetail', [RiskManagementController::class, "GetCustHidDetail"]);

Route::get('/custBehaviour', function () { return view("risk.cust-behaviour"); });
Route::post('/custBehaviour', [RiskManagementController::class, "custBehaviour"]);

Route::get('/state', [RiskManagementController::class, "renderStateView"]);
Route::post('/getStateData', [RiskManagementController::class, "getUserStateData"]);

