<?php if (!defined('BASEPATH')) require_once('index.php');
if (!defined('BASEPATH')) exit('No direct script access allowed');


/**

* Content Control Panel

* 

* Displays all control panel forms, datasets, and other displays
 
*
 
* @author Weblight.ro

* @copyright Weblight.ro

* @package BJ Tool

*

*/



class Admincp extends Admincp_Controller {
                
	function __construct()
	{

		parent::__construct();
				
		$this->admin_navigation->parent_active('soccerstats');
                                		
		//error_reporting(E_ALL^E_NOTICE);
		//error_reporting(E_WARNING);
	}
        
        function index () 
        {
            redirect('admincp/soccerstats/list_introstats');		
	}
        
//********************************** Introstats LIST *******************************//         
    /**
	* Manage Introstats
	*
	* Lists active markets for managing
	*/
	function list_introstats()
	{
		$this->load->library('dataset');
		//$this->admin_navigation->module_link('Add New Market',site_url('admincp/soccerstats/add_market'));

		$columns = array(
						array(
							'name' => 'ID #',
							'type' => 'id',
							'width' => '5%',
                                                    ),

						array(
							'name' => 'Name',
                                                        'type' => 'text',
							'width' => '15%',
							),
                                                array(
							'name' => 'Pld',
                                                        'type' => 'text',
							'width' => '5%',
							),
                                                array(
							'name' => '1 X 2',
                                                        'type' => 'text',
							'width' => '10%',
							),
                                                array(
							'name' => '1',
                                                        'type' => 'text',
							'width' => '5%',
							),
                                                array(
							'name' => 'X',
                                                        'type' => 'text',
							'width' => '5%',
							),
                                                array(
							'name' => '2',
                                                        'type' => 'text',
							'width' => '5%',
							),
                                                array(
							'name' => 'Goals',
                                                        'type' => 'text',
							'width' => '5%',
							),
                                                array(
							'name' => 'Home Goals',
                                                        'type' => 'text',
							'width' => '5%',
							),
                                                array(
							'name' => 'Away Goals',
                                                        'type' => 'text',
							'width' => '5%',
							),
                                                array(
							'name' => 'Over 1.5',
                                                        'type' => 'text',
							'width' => '10%',
							),
                                                array(
							'name' => 'Over 2.5',
                                                        'type' => 'text',
							'width' => '10%',
							),
                                                array(
							'name' => 'Over 3.5',
                                                        'type' => 'text',
							'width' => '10%',
                                                    )
                    );
        }
      
}