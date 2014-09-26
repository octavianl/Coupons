<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Linkshare Module Definition
*
* Declares the module, update code, etc.
*
* @author Weblight.ro
* @copyright Weblight.ro
* @package Save-Coupon
*
*/

class Linkshare extends Module
{
    var $version = '1.0';
    var $name = 'linkshare';

    function __construct ()
    {
            // set the active module
            $this->active_module = $this->name;	

            parent::__construct();
    }	
    /**
    * Pre-admin function
    * 
    * Initiate navigation in control panel
    * 
    * @return Linkshare Navigation
    **/
    function admin_preload ()
    {
        $this->CI->admin_navigation->child_link('linkshare', 10, 'Networks', site_url('admincp/linkshare/list_networks'));
        $this->CI->admin_navigation->child_link('linkshare', 20, 'Application Status', site_url('admincp/linkshare/list_status'));
        $this->CI->admin_navigation->child_link('linkshare', 30, 'Categorii', site_url('admincp/linkshare/list_categorii'));           
        $this->CI->admin_navigation->child_link('linkshare', 40, 'Site-uri', site_url('admincp/linkshare/lista_sites'));             
        $this->CI->admin_navigation->child_link('linkshare', 50, 'Import produse linkshare', site_url('admincp/linkshare/import_produse'));
        $this->CI->admin_navigation->child_link('linkshare', 60, 'Export categorii presta', site_url('admincp/linkshare/export_categorii'));
        $this->CI->admin_navigation->child_link('linkshare', 70, 'Export produse presta', site_url('admincp/linkshare/export_produse'));
        $this->CI->admin_navigation->child_link('linkshare', 80, 'Purge produse linkshare', site_url('admincp/linkshare/purge_produse'));
        $this->CI->admin_navigation->child_link('linkshare', 90, 'Logs Panel', site_url('admincp/linkshare/logs_panel'));
        //$this->CI->admin_navigation->child_link('linkshare',90,'test',site_url('admincp/linkshare/test'));
    }		
}
