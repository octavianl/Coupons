<?php

/**
 * Produs New Model
 *
 * Manages produs new
 *
 * @author   Weblight <office@weblight.ro>
 * @license  License http://www.weblight.ro/
 * @link     http://www.weblight.ro/

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
    function get_produs($id)
    {
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
    function new_produs($insert_fields)
    {
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
     * @return boolean true
     */
    function update_produs($update_fields, $id)
    {
        $this->db->update('linkshare_produs_new', $update_fields, array('id' => $id));

        return true;
    }

    /**
     * Update Produs By Linkid
     *
     * Updates produs
     * 
     * @param array $update_fields
     * @param int $linkid	
     *
     * @return boolean true
     */
    function update_produs_by_linkid($update_fields, $linkid)
    {
        $this->db->update('linkshare_produs_new', $update_fields, array('linkid' => $linkid));

        return true;
    }

    /**
     * Delete Produs
     *
     * Deletes produs
     * 	
     * @param int $id	
     *
     * @return boolean true
     */
    function delete_produs($id)
    {

        $this->db->delete('linkshare_produs_new', array('id' => $id));

        return true;
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
    function exists_produs($linkid)
    {
        $this->db->where('linkid', $linkid);
        $result = $this->db->get('linkshare_produs_new');
        foreach ($result->result_array() as $row) {
            return true;
        }

        return false;
    }
    
    /**
     * Get Produse By Linkid
     * @param array $params
     *
     * @return array
     */
    function get_produse_by_linkid($linkid,$mid) {
        $row = array();

        $result = $this->db->get('linkshare_produs', array('linkid' => $linkid,'mid' => $mid));

        foreach ($result->result_array() as $linie) {
            $row[] = $linie;
        }

        return $row;
    }

    /**
     * Copy to Old Produs
     *
     * Creates a new produs
     *
     * @param array $insert_fields	
     *
     * @return int $insert_id
     */
    function copy_to_old($insert_fields)
    {
        $this->db->insert('linkshare_produs_old', $insert_fields['0']);
        $insert_id = $this->db->insert_id();

        return $insert_id;
    }
    
    /**
     * Delete Old Product from 
     *
     * Deletes produs
     * 	
     * @param int $id	
     *
     * @return boolean true
     */
    function delete_old_from_current($linkid,$mid)
    {
        $this->db->delete('linkshare_produs', array('linkid' => $linkid,'mid' => $mid));

        return true;
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
    function mark_old($mid)
    {
        $i = $j = 0;
        $update_fields = array();
        $this->db->where('mid', $mid);
        $result = $this->db->get('linkshare_produs');
        foreach ($result->result_array() as $row) {
            //if the old product is not present among the new = current products...we mark it as old move it to linkshare_produs_old
            $update_fields['available'] = 'no';
            $update_fields['last_update_date'] = date('Y-m-d H:i:s');
            if (!$this->exists_produs($row['linkid'])) {
                $this->db->update('linkshare_produs', $update_fields, array('linkid' => $row['linkid']));
                
                // Copy product to linkshare_produs_old
                $produs = $this->get_produse_by_linkid($row['linkid'],$mid);    
                $this->copy_to_old($produs);
                
                // Delete product from linkshare_produs
                $this->delete_old_from_current($row['linkid'],$mid);
                
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
