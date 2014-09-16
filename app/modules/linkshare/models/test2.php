<?php

/**
 * status Model
 *
 * Manages status
 *
 * @author Weblight.ro
 * @copyright Weblight.ro
 * @package Save-Coupon
 */
class Status_model extends CI_Model {

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
    function get_statuses() {
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
    function get_status($id) {
        $row = array();
        $this->db->where('id', $id);
        $result = $this->db->get('linkshare_status');

        foreach ($result->result_array() as $row) {
            return $row;
        }

        return $row;
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
    function new_status($insert_fields) {
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
     * @return boolean TRUE
     */
    function update_status($update_fields, $id) {

        $this->db->update('linkshare_status', $update_fields, array('id' => $id));

        return TRUE;
    }

    /**
     * Delete status
     *
     * Deletes status
     * 	
     * @param int $id	
     *
     * @return boolean TRUE
     */
    function delete_status($id) {

        $this->db->delete('linkshare_status', array('id' => $id));

        return TRUE;
    }

}
