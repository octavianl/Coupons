<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Freedom Module Definition
*
* Declares the module, update code, etc.
*
* @author Weblight.ro
* @copyright Weblight.ro
* @package Save-Coupon
*
*/

class Freedom extends Module {
	var $version = '1.0';
	var $name = 'freedom';

	function __construct () {
		// set the active module
		$this->active_module = $this->name;	
		
		parent::__construct();
	}
	
	/*
	* Pre-admin function
	*
	* Initiate navigation in control panel
	*/
	function admin_preload ()
	{
            $this->CI->admin_navigation->child_link('freedom',10,'Spartacus',site_url('admincp/freedom/spartacus'));
            $this->CI->admin_navigation->child_link('freedom',20,'Predator',site_url('admincp/freedom/predator')); 
            $this->CI->admin_navigation->child_link('freedom',30,'Lolcat',site_url('admincp/freedom/lolcat'));
	}
		
}
