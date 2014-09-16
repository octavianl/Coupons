<?php

/**
 * Produs New Model
 *
 * Manages produs new
 *
 * @author Weblight.ro
 * @copyright Weblight.ro
 * @package Save-Coupon

 */
class Produs_new_model extends CI_Model {

    private $CI;

    function __construct()
    {
        parent::__construct();

        $this->CI = & get_instance();
    }
    
    /**
     * Get Produse
     *
     *
     * @return array
     */
    function get_produse() {
        $row = array();
        $result = $this->db->get('linkshare_produs_new');

        foreach ($result->result_array() as $linie) {
            $row[] = $linie;
        }

        return $row;
    }

    /**
     * Get Produse By Mid
     * @param array $params
     *
     * @return array
     */
    function get_produse_by_mid($params) {
        $row = array();
        if ($params['mid'])
            $this->db->where('mid', $params['mid']);
        $result = $this->db->get('linkshare_produs_new');

        foreach ($result->result_array() as $linie) {
            $row[] = $linie;
        }

        return $row;
    }

    /**
     * Get Produs Status
     *
     *
     * @return array
     */
    function get_produs_status() {
        $row = array();
        $result = $this->db->get('linkshare_produs_new');

        foreach ($result->result_array() as $value) {
            $row[$value['id']] = $value['name'];
        }

        return $row;
    }

    /**
     * Get Produs
     *
     * @param int $id	
     *
     * @return array
     */
    function get_produs($id) {
        $row = array();
        $this->db->where('id', $id);
        $result = $this->db->get('linkshare_produs_new');

        foreach ($result->result_array() as $row) {
            return $row;
        }

        return $row;
    }

    /**
     * Create New Produs
     *
     * Creates a new produs
     *
     * @param array $insert_fields	
     *
     * @return int $insert_id
     */
    function new_produs($insert_fields) {
        $this->db->insert('linkshare_produs_new', $insert_fields);
        $insert_id = $this->db->insert_id();

        return $insert_id;
    }

    /**
     * Update Produs
     *
     * Updates produs
     * 
     * @param array $update_fields
     * @param int $id	
     *
     * @return boolean TRUE
     */
    function update_produs($update_fields, $id) {
        $this->db->update('linkshare_produs_new', $update_fields, array('id' => $id));

        return TRUE;
    }

    /**
     * Update Produs By Linkid
     *
     * Updates produs
     * 
     * @param array $update_fields
     * @param int $linkid	
     *
     * @return boolean TRUE
     */
    function update_produs_by_linkid($update_fields, $linkid) {
        $this->db->update('linkshare_produs_new', $update_fields, array('linkid' => $linkid));

        return TRUE;
    }

    /**
     * Delete Produs
     *
     * Deletes produs
     * 	
     * @param int $id	
     *
     * @return boolean TRUE
     */
    function delete_produs($id) {

        $this->db->delete('linkshare_produs_new', array('id' => $id));

        return TRUE;
    }

    /**
     * Checks if Produs Exists
     *
     * Exists produs
     * 	
     * @param int $linkid	
     *
     * @return boolean
     */
    function exists_produs($linkid) {
        $this->db->where('linkid', $linkid);
        $result = $this->db->get('linkshare_produs_new');
        foreach ($result->result_array() as $row) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Mark Old Produs
     *
     * Marks old produs
     * 	
     * @param int $id	
     *
     * @return int $i
     */
    function mark_old($mid) {
        $i = $j = 0;
        $update_fields = array();
        $this->db->where('mid', $mid);
        $result = $this->db->get('linkshare_produs');
        foreach ($result->result_array() as $row) {
            //if the old product is not present among the new = current products...we mark it as old,not delete it
            $update_fields['available'] = 'no';
            $update_fields['last_update_date'] = date('Y-m-d H:i:s');
            if (!$this->exists_produs($row['linkid'])) {
                $this->db->update('linkshare_produs', $update_fields, array('linkid' => $row['linkid']));
                $i++;
            }
        }

        //delete all mid new = current products 
        $this->db->delete('linkshare_produs_new', array('mid' => $mid));
        $result = $this->db->get('linkshare_produs_new');
        foreach ($result->result_array() as $row) {
            $j++;
        }
        //if table empty reset auto_increment
        if (!$j)
            $this->db->query('ALTER TABLE linkshare_produs_new AUTO_INCREMENT = 0');
        return $i;
    }

}
