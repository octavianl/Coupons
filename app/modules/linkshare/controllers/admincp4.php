<?php 
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Content Control Panel
 *
 * @category Admin Controller
 * @package  Linkshare
 * @author   Weblight <office@weblight.ro>
 * @license  License http://www.weblight.ro/
 * @link     http://www.weblight.ro/
 *
 */

use app\third_party\LOG\Log;

require_once APPPATH . 'third_party/OAUTH2/LinkshareConfig.php';
require_once APPPATH . 'third_party/OAUTH2/CurlApi.php';

class Admincp4 extends Admincp_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->admin_navigation->parent_active('linkshare');

        //error_reporting(E_ALL^E_NOTICE);
        //error_reporting(E_WARNING);
    }

    public function index()
    {
        redirect('admincp/linkshare/siteAdvertisers/1');
    }
    
    public function logsTest()
    {
        include "app/third_party/LOG/Log.php";

        $string = "Test error";

        Log::error($string,'advertisers');
        
        $string = "Test";

        Log::warn($string);
    }

/**
 * Logs panel
 */

    public function logsPanel()
    {
        $this->admin_navigation->module_link('Refresh Logs', site_url('admincp4/linkshare/logsPanel'));

        $this->load->library('dataset');

        $columns = array(
            array(
                'name' => 'Row No',
                'width' => '10%'),
            array(
                'name' => 'Date & Time',
                'width' => '20%'),
            array(
                'name' => 'Log Level',
                'width' => '20%'),
            array(
                'name' => 'Message',
                'width' => '50%'
            )
        );
        
        $filters = array();
        $filters['limit'] = 5;
        
        $this->dataset->columns($columns);
        $this->dataset->datasource('log_model', 'getLogs', $filters);

        $this->dataset->base_url(site_url('admincp4/linkshare/logsPanel'));
        $this->dataset->rows_per_page($filters['limit']);
       
//        echo "<pre>";
//        print_r($filters);
//        echo "</pre>";
//        die;
        
        $this->dataset->initialize();
        $this->load->view('logsPanel');

    }
    
}
