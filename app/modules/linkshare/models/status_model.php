<?php

/**
 * Status Model - Manages status
 *
 * @category Admin Controller
 * @package  Linkshare
 * @author   Weblight <office@weblight.ro>
 * @license  License http://www.weblight.ro/
 * @link     http://www.weblight.ro/
 *
 */

class Status_model extends CI_Model
{

    private $CI;

    public function __construct()
    {
        parent::__construct();

        $this->CI = & get_instance();
    }

    /**
     * Get statuses
     *
     * @return array
     */
    public function getStatuses()
    {
        $rows = array();
        
        $result = $this->db->get('linkshare_status');
        foreach ($result->result_array() as $linie) {
            $rows[] = $linie;
        }

        return $rows;
    }

    /**
     * Get status
     *
     * @param int $id Status identifier	
     *
     * @return array
     */
    public function getStatus($id)
    {
        $row = array();
        $this->db->where('id', $id);
        $result = $this->db->get('linkshare_status');

        foreach ($result->result_array() as $row) {
            return $row;
        }

        return $row;
    }

    /**
     * Get status
     *
     * @param string $name The status name	
     *
     * @return int
     */
    public function getStatusByName($name)
    {
        $status = 0;
        $this->db->where('id_status', $name);
        $result = $this->db->get('linkshare_status');

        foreach ($result->result_array() as $row) {
            return $row['id'];
        }

        return $status;
    }
    
    /**
     * Get Status Name by ID
     *
     * @param int $id The status identifier	
     *
     * @return int
     */
    function getStatusNameByID($id)
    {
        $status = $this->getStatus($id);
        return $status['name'];
    }

    /**
     * Get Status By Application Status
     *
     * @param string $status	
     *
     * @return array
     */
    function getStatusByApplicationStatus($status)
    {
        switch ($status)
        {
        case 'Temp Removed' : 
            return $this->getStatusByName('temp removed');
        case 'Approved' : 
            return $this->getStatusByName('approved');
        case 'Extended' : 
            return $this->getStatusByName('approval extended');
        case 'Perm Rejected' : 
            return $this->getStatusByName('perm rejected');
        case 'Perm Removed' : 
            return $this->getStatusByName('perm removed');
        case 'Self Removed' : 
            return $this->getStatusByName('self removed');
        case 'Temp Rejected' : 
            return $this->getStatusByName('temp rejected');
        case 'Waiting' : 
            return $this->getStatusByName('wait');
        default : 
            return '';
        }

        return 0;
    }

    /**
     * Create New Status
     *
     * @param array $insert_fields Fields to insert	
     *
     * @return int $insert_id
     */
    function newStatus($insert_fields)
    {
        $this->db->insert('linkshare_status', $insert_fields);
        $insert_id = $this->db->insert_id();

        return $insert_id;
    }

    /**
     * Update status
     * 
     * @param array $update_fields Fields to update
     * @param int   $id            Status identifier	
     *
     * @return boolean true
     */
    function updateStatus($update_fields, $id)
    {
        $this->db->update('linkshare_status', $update_fields, array('id' => $id));
        return true;
    }

    /**
     * Delete Status
     * 	
     * @param int $id Status identifier	
     *
     * @return boolean true
     */
    function deleteStatus($id)
    {
        $this->db->delete('linkshare_status', array('id' => $id));
        return true;
    }
}
