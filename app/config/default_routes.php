<?php

/**
* Default Routes
*
* This file is subject to change with future upgrades.
* Do not add your custom routes here.  Instead, use
* the routes.php file.
*
* @copyright Electric Function, Inc.
* @package Hero Framework
* @author Electric Function, Inc.
*/

$route['default_controller'] = "frontpage";
$route['scaffolding_trigger'] = "";

// reroute all callback/X calls to the main callback controller
$route['callback:any'] = 'billing/callback/process';

$route['admincp9/([a-zA-Z_-]+)/(:any)'] = "$1/admincp9/$2";
$route['admincp9/([a-zA-Z_-]+)'] = "$1/admincp9/index";

$route['admincp8/([a-zA-Z_-]+)/(:any)'] = "$1/admincp8/$2";
$route['admincp8/([a-zA-Z_-]+)'] = "$1/admincp8/index";

$route['admincp7/([a-zA-Z_-]+)/(:any)'] = "$1/admincp7/$2";
$route['admincp7/([a-zA-Z_-]+)'] = "$1/admincp7/index";

$route['admincp6/([a-zA-Z_-]+)/(:any)'] = "$1/admincp6/$2";
$route['admincp6/([a-zA-Z_-]+)'] = "$1/admincp6/index";

$route['admincp5/([a-zA-Z_-]+)/(:any)'] = "$1/admincp5/$2";
$route['admincp5/([a-zA-Z_-]+)'] = "$1/admincp5/index";

$route['admincp4/([a-zA-Z_-]+)/(:any)'] = "$1/admincp4/$2";
$route['admincp4/([a-zA-Z_-]+)'] = "$1/admincp4/index";

$route['admincp3/([a-zA-Z_-]+)/(:any)'] = "$1/admincp3/$2";
$route['admincp3/([a-zA-Z_-]+)'] = "$1/admincp3/index";

//second admin controller module
$route['admincp2/([a-zA-Z_-]+)/(:any)'] = "$1/admincp2/$2";
$route['admincp2/([a-zA-Z_-]+)'] = "$1/admincp2/index";

// reroute all callback/X calls to the main callback controller
$route['callback:any'] = 'billing/callback/process';

// admin specific routes
$route['admincp'] = 'admincp/dashboard';
$route['admincp/dashboard/([a-zA-Z_-]+)'] = 'admincp/dashboard/$1';
$route['admincp/dataset/([a-zA-Z_-]+)'] = 'admincp/dataset/$1';
$route['admincp/login'] = 'admincp/login';
$route['admincp/login/go'] = 'admincp/login/go';
$route['admincp/logout'] = 'admincp/login/logout';
$route['admincp/([a-zA-Z_-]+)/(:any)'] = "$1/admincp/$2";
$route['admincp/([a-zA-Z_-]+)'] = "$1/admincp/index";

// miscellaneous routes
$route['checkout'] = 'billing/checkout';
$route['checkout/([a-zA-Z_-]+)'] = 'billing/checkout/$1';
$route['subscriptions'] = 'billing/subscriptions';
$route['subscriptions/(:any)'] = 'billing/subscriptions/$1';