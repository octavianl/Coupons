<?php

/**
* Network Model
*
* Manages networks
*
* @author Weblight.ro
* @copyright Weblight.ro
* @package Save-Coupon

*/

	class Network_model extends CI_Model
	{
		private $CI;
	
		function __construct()
		{
			parent::__construct();
		
			$this->CI =& get_instance();
		}
	
        /**
	* Get Networks
	*
	*
	* @return array
	*/
	function get_networks () {
		$row = array();										
		$result = $this->db->get('linkshare_network');
                foreach ($result->result_array() as $linie) {
			$row[] = $linie;
		}
                
		return $row;														
	}        
                
	/**
	* Get Network
	*
	* @param int $id	
	*
	* @return array
	*/
	function get_network ($id) {
		$row = array();								
		$this->db->where('id',$id);
		$result = $this->db->get('linkshare_network');
				
		foreach ($result->result_array() as $row) {
			return $row;
		}
		
		return $row;														
	}
        
       	
	/**
	* Create New Network
	*
	* Creates a new network
	*
	* @param array $insert_fields	
	*
	* @return int $insert_id
	*/
	function new_network ($insert_fields) {																										
		$this->db->insert('linkshare_network', $insert_fields);		
		$insert_id = $this->db->insert_id();
										
		return $insert_id;
	}
	
	/**
	* Update Network
	*
	* Updates network
	* 
	* @param array $update_fields
	* @param int $id	
	*
	* @return boolean TRUE
	*/
	function update_network ($update_fields,$id) {																										
				
		$this->db->update('linkshare_network',$update_fields,array('id' => $id));
										
		return TRUE;
	}
	
	/**
	* Delete Network
	*
	* Deletes network
	* 	
	* @param int $id	
	*
	* @return boolean TRUE
	*/
	function delete_network ($id) {																										
				
		$this->db->delete('linkshare_network',array('id' => $id));
										
		return TRUE;
	}
	
	
			
}
