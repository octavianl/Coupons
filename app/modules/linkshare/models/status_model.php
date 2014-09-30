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
    function get_statuses()
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
    function get_status($id)
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
    function get_status_by_name($id_status)
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
     * Get status
     *
     * @param string $status	
     *
     * @return array
     */
    function get_status_by_application_status($status)
    {
        switch ($status)
        {
        case 'Temp Removed' : 
            return $this->get_status_by_name('temp removed');
        case 'Approved' : 
            return $this->get_status_by_name('approved');
        case 'Extended' : 
            return $this->get_status_by_name('approval extended');
        case 'Perm Rejected' : 
            return $this->get_status_by_name('perm rejected');
        case 'Perm Removed' : 
            return $this->get_status_by_name('perm removed');
        case 'Self Removed' : 
            return $this->get_status_by_name('self removed');
        case 'Temp Rejected' : 
            return $this->get_status_by_name('temp rejected');
        case 'Waiting' : 
            return $this->get_status_by_name('wait');
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
    function new_status($insert_fields)
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
    function update_status($update_fields, $id)
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
