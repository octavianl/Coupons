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

class Admincp2 extends Admincp_Controller
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
        redirect('admincp/linkshare/site_magazine/1');
    }
    
    public function logsTest()
    {
        include "app/third_party/LOG/Log.php";

        $string = "shut down";

        Log::info($string);
    }

/**
 * Logs panel
 */

    public function logsPanel()
    {
        $this->admin_navigation->module_link('Refresh Logs', site_url('admincp/linkshare/logs_panel'));

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

        $this->dataset->datasource('log_model', 'get_logs');

        $this->dataset->columns($columns);

        $this->dataset->base_url(site_url('admincp/linkshare/logs_panel'));
        $this->dataset->rows_per_page(10);

        $this->dataset->initialize();
        $this->load->view('logs_panel');

    }
    
}
