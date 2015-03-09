<?php

/**
 * Network Model - Manages networks
 *
 * @category Admin Controller
 * @package  Linkshare
 * @author   Weblight <office@weblight.ro>
 * @license  License http://www.weblight.ro/
 * @link     http://www.weblight.ro/
 *
 */

class Network_model extends CI_Model
{

    private $CI;

    function __construct()
    {
        parent::__construct();

        $this->CI = & get_instance();
    }

    /**
     * Get Networks
     *
     *
     * @return array
     */
    function getNetworks()
    {
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
    function getNetwork($id)
    {
        $row = array();
        $this->db->where('id', $id);
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
    function newNetwork($insert_fields)
    {
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
     * @return boolean true
     */
    function updateNetwork($update_fields, $id)
    {

        $this->db->update('linkshare_network', $update_fields, array('id' => $id));

        return true;
    }

    /**
     * Delete Network
     *
     * Deletes network
     * 	
     * @param int $id	
     *
     * @return boolean true
     */
    function deleteNetwork($id)
    {
        $this->db->delete('linkshare_network', array('id' => $id));

        return true;
    }

}
