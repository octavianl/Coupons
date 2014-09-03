<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Freedom Control Panel
*
* Displays all freedoms
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Admincp extends Admincp_Controller {
	function __construct()
	{
		parent::__construct();
				
		$this->admin_navigation->parent_active('freedom');
		
		//error_reporting(E_ALL^E_NOTICE);
		//error_reporting(E_WARNING);
	}	
	
	function index () {		
		redirect('admincp/freedom/spartacus/');
		
	}
	
	function spartacus(){
            $this->load->view('spartacus.php');
        }
	
        function predator(){
            $this->load->view('predator.php');
        }
        
        function lolcat(){
            $this->load->view('lolcat.php');
        }
        
        function xmlParse($file,$wrapperName,$limit=NULL,&$cate){
    $xml = new XMLReader();
    if(!$xml->open($file)){
        die("Failed to open input file.");
    }
    print '<pre>';
    $total = $slice = 0;
    
    while($xml->read()){
        //echo 'read<br/>';
        if($xml->nodeType==XMLReader::ELEMENT && $xml->name == $wrapperName){
            while($xml->read() && $xml->name != $wrapperName){
                if($xml->nodeType==XMLReader::ELEMENT){
                    $name = $xml->name;
                    $xml->read();      
                    $value = $xml->value;
                    $subarray[$name] = $value;                   
                }
            }
            if($limit==NULL || $slice<$limit){
                if($this->callback($subarray,&$cate)){
                    $slice++;
                }
                unset($subarray);
            }
            $total++;
        }
        
        echo "slice = $slice total = $total<br/>";
    }
    $xml->close();
}
        function callback($array,&$cate){
    //condition and action for positive validation (increments parser limit)
    if($array['ns1:campaignID'] == 0){
        /*add to database*/
        print_r($array);
        $cate++;
        return TRUE;
    }
    else {        
        return FALSE;
    }
}

        function xml(){
            error_reporting(E_ALL);
            ini_set('display_errors',1);            
            $file = 'http://lld2.linksynergy.com/services/restLinks/getProductLinks/ff8c950f7a1c7f4f7b3db7e9407bbd89822f611a6c44c441bc9043c5edaa4746/35150/26/-1/1';
            $file = 'http://lld2.linksynergy.com/services/restLinks/getProductLinks/ff8c950f7a1c7f4f7b3db7e9407bbd89822f611a6c44c441bc9043c5edaa4746/36342/200074226/-1/1';
            $file = 'http://lld2.linksynergy.com/services/restLinks/getProductLinks/ff8c950f7a1c7f4f7b3db7e9407bbd89822f611a6c44c441bc9043c5edaa4746/36342/200074655/-1/1';
            $wrapperName = 'ns1:return';
            $cate = 1;
            $this->xmlParse($file,$wrapperName,100,&$cate);
            echo "cate = $cate<br/>";
        }
	
}