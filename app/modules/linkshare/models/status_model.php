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

    function __construct()
    {
        parent::__construct();

        $this->CI = & get_instance();
    }

    /**
     * Get statuses
     *
     *
     * @return array
     */
    function getStatuses()
    {
        $row = array();
        $result = $this->db->get('linkshare_status');
        foreach ($result->result_array() as $linie) {
            $row[] = $linie;
        }

        return $row;
    }

    /**
     * Get status
     *
     * @param int $id	
     *
     * @return array
     */
    function getStatus($id)
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
     * @param string $id_status	
     *
     * @return int
     */
    function getStatusByName($id_status)
    {
        $status = 0;
        $this->db->where('id_status', $id_status);
        $result = $this->db->get('linkshare_status');

        foreach ($result->result_array() as $row) {
            return $row['id'];
        }

        return $status;
    }
    
    /**
     * Get status by ID
     *
     * @param string $id_status	
     *
     * @return int
     */
    function getStatusNameByID($id)
    {
        $statusName = '';
        $this->db->where('id', $id);
        $result = $this->db->get('linkshare_status');

        foreach ($result->result_array() as $row) {
            $statusName = $row['name'];
        }

        return $statusName;
    }

    /**
     * Get status
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
     * Create New status
     *
     * Creates a new status
     *
     * @param array $insert_fields	
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
     * Updates status
     * 
     * @param array $update_fields
     * @param int $id	
     *
     * @return boolean true
     */
    function updateStatus($update_fields, $id)
    {

        $this->db->update('linkshare_status', $update_fields, array('id' => $id));

        return true;
    }

    /**
     * Delete status
     *
     * Deletes status
     * 	
     * @param int $id	
     *
     * @return boolean true
     */
    function deleteStatus($id)
    {

        $this->db->delete('linkshare_status', array('id' => $id));

        return true;
    }

}
