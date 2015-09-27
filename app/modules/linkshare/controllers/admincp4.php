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

error_reporting(E_ALL^E_NOTICE);
error_reporting(E_WARNING);

use app\third_party\LOG\Log;

require_once APPPATH . 'third_party/OAUTH2/LinkshareConfig.php';
require_once APPPATH . 'third_party/OAUTH2/CurlApi.php';

class Admincp4 extends Admincp_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->admin_navigation->parent_active('linkshare');

    }

    public function index()
    {
        redirect('admincp/linkshare/siteAdvertisers/1');
    }
    
    public function logsTest()
    {

        include "app/third_party/LOG/Log.php";
        
        $string = "Test error" . " || Class name: " . __CLASS__ . " | Method name: " . __METHOD__ . " | error line: " . __LINE__ . " | from file: " . __FILE__;

        Log::debug( array(__FILE__, __LINE__, __CLASS__, __METHOD__, 'Test error'), Log::CATEGORIES);

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
                'width' => '5%'),
            array(
                'name' => 'Date',
                'width' => '10%'),
            array(
                'name' => 'Level',
                'width' => '5%'),
            array(
                'name' => 'File Name',
                'width' => '20%'
            ),
            array(
                'name' => 'Line',
                'width' => '5%'
            ),
            array(
                'name' => 'Class',
                'width' => '5%'
            ),
            array(
                'name' => 'Method',
                'width' => '10%'
            ),
            array(
                'name' => 'Message',
                'width' => '40%'
            )
        );
        
        //Prepare data for filters
        $raw_data = $this->input->get('datepicker', TRUE);
        $data_array = explode('/', $raw_data);
        
        $filters = array();
        $filters['limit'] = 5;
        $filters['offset'] = $this->input->get('offset', TRUE);
        
        $filters['day'] = $data_array[1];
        $filters['month'] = $data_array[0];
        $filters['year'] = $data_array[2];
        $filters['zone'] = $this->input->get('zone', TRUE);
        
        echo "<pre>";
        print_r($filters);
        echo "</pre>";
        //die;
        
        
        $this->dataset->columns($columns);
        $this->dataset->datasource('log_model', 'getLogs', $filters);

        $this->dataset->base_url(site_url('admincp4/linkshare/logsPanel'));
        $this->dataset->rows_per_page($filters['limit']);
        
        $allZones = array('advertisers', 'categories', 'products', 'export');        
        $data = array(
            'form_title' => 'Log Panel',
            'allZones'       => $allZones
        );
        $this->dataset->initialize();
        $this->load->view('logsPanel',$data);

    }
    
}
