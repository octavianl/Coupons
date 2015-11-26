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

use app\third_party\LOG\Log;

require_once APPPATH . 'third_party/OAUTH2/LinkshareConfig.php';
require_once APPPATH . 'third_party/OAUTH2/CurlApi.php';
require_once APPPATH . "third_party/LOG/Log.php";

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
        
        $string = "Test error" . " || Class name: " . __CLASS__ . " | Method name: " . __METHOD__ . " | error line: " . __LINE__ . " | from file: " . __FILE__;

        Log::debug( array(__FILE__, __LINE__, __CLASS__, __METHOD__, 'Test error'), Log::CATEGORIES);
        echo 'ok';
    }

    public function updateFilters()
    {        
        $this->load->library('asciihex');
        
        $filters = array();

        if (!empty($_POST['datepicker']))
        {
            $filters['datepicker'] = $_POST['datepicker'];
        }
        
        if (!empty($_POST['zone']))
        {
            $filters['zone'] = $_POST['zone'];
        }
        
        if (!empty($_POST['offset']))
        {
            $filters['offset'] = $_POST['offset'];
        }
        
        if (!empty($_POST['limit']))
        {
            $filters['limit'] = $_POST['limit'];
        }
        
        //print_r($filters);
        
        $filters = $this->CI->asciihex->AsciiToHex(base64_encode(serialize($filters)));

        echo $filters;
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
                'width' => '10%',
                ),
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

        $filters = $filters_decode = array();
        // variable to check if we come from a post request
        $fromFilterz = null;
       
        if (isset($_POST['filterz']))
        {
            $filters_decode = unserialize(base64_decode($this->asciihex->HexToAscii($_POST['filterz'])));
            $fromFilterz = 'post';
        }                
               
        if (isset($filters_decode) && !empty($filters_decode))
        {
            foreach ($filters_decode as $key => $val)
            {
                $filters[$key] = $val;
            }
        }
        
        //Prepare data for filters
        if (isset($filters['datepicker'])) {
            $raw_data = $filters['datepicker'];
        } else {
            $raw_data = $this->input->post('datepicker', TRUE);
        }
        
        // in case of redirect get datepicker from GET
        if (isset($_GET['datepicker'])) {
           $raw_data = $this->input->get('datepicker', TRUE); 
        }
       
        $data_array = explode('/', $raw_data);
        
        if (!isset($filters['limit'])) {
            $filters['limit'] = 500; // number of log lines per page
        }
        
        // in case of redirect get limit from GET
        if (isset($_GET['limit'])) {
           $filters['limit'] = $_GET['limit'];
        }
        
        if (!isset($filters['offset'])) {
            $filters['offset'] = 0;
        }
        
        // in case of redirect get offset from GET
        if (isset($_GET['offset'])) {
           $filters['offset'] = $_GET['offset'];
        }
        
        $filters['day'] = $data_array[1];
        $filters['month'] = $data_array[0];
        $filters['year'] = $data_array[2];
        
        if (isset($filters['zone'])) {
        } else {
            $filters['zone'] = $this->input->post('zone', TRUE);
        }                
        
        // in case of redirect get zone from GET
        if (isset($_GET['zone'])) {
           $filters['zone'] = $this->input->get('zone', TRUE); 
        }
        
        //echo "<pre>FILTERS";
        //print_r($filters);
        //echo "</pre>";
        //die;
        
        // redirect for viewing pagination links css correctly
        if (isset($fromFilterz)) {
            $newUrl = site_url('admincp4/linkshare/logsPanel?sort_dir=&sort_column=&limit=' . $filters['limit'] .'&offset=' . $filters['offset'] . '&zone=' . $filters['zone'] . '&datepicker=' . $filters['datepicker']);
            redirect($newUrl);
        }
        
        $this->dataset->columns($columns);
        $this->dataset->datasource('log_model', 'getLogs', $filters);

        $this->dataset->base_url(site_url('admincp4/linkshare/logsPanel'));
        $this->dataset->rows_per_page($filters['limit']);
        
        $allZones = array('advertisers', 'categories', 'products', 'export');
        
        $data = array(
            'form_title' => 'Log Panel',
            'allZones'   => $allZones
        );
        
        $this->dataset->initialize();
        $this->load->view('logsPanel',$data);
    }
    
}
