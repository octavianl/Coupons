<?php

/**
 * Produs Partial Model - Manages partial produs
 *
 * @category Admin Controller
 * @package  Linkshare
 * @author   Weblight <office@weblight.ro>
 * @license  License http://www.weblight.ro/
 * @link     http://www.weblight.ro/
 *
 */

class Product_partial_model extends CI_Model
{

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
    function getProducts()
    {
        $row = array();
        $result = $this->db->get('linkshare_produs_partial');

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
    function getProductsByMid($params)
    {
        $row = array();
        if (isset($params['limit'])) {
            $offset = (isset($params['offset'])) ? $params['offset'] : 0;
            $this->db->limit($params['limit'], $offset);
        }
        if ($params['id_site'])
            $this->db->where('id_site', $params['id_site']);
        if ($params['mid'])
            $this->db->where('mid', $params['mid']);
        $result = $this->db->get('linkshare_produs_partial');

        foreach ($result->result_array() as $linie) {
            $row[] = $linie;
        }

        return $row;
    }

    /**
     * Get Product Status
     *
     *
     * @return array
     */
    function getProductStatus()
    {
        $row = array();
        $result = $this->db->get('linkshare_produs_partial');

        foreach ($result->result_array() as $value) {
            $row[$value['id']] = $value['name'];
        }

        return $row;
    }

    /**
     * Change Produs Status
     * 
     * @param int $id_product
     *
     * @return boolean
     */
    function changeProductStatus($id_product)
    {
        $row = array();
        $this->db->where('id', $id_product);
        $result = $this->db->get('linkshare_produs_partial');

        foreach ($result->result_array() as $value) {
            $available = $value['available'];
        }

        if ($available == 'yes')
            $available = 'no';
        elseif ($available == 'no')
            $available = 'yes';

        $update_fields['available'] = $available;
        $this->updateProduct($update_fields, $id_product);

        return true;
    }

    /**
     * Get Produs
     *
     * @param int $id	
     *
     * @return array
     */
    function getProduct($id)
    {
        $row = array();
        $this->db->where('id', $id);
        $result = $this->db->get('linkshare_produs_partial');

        foreach ($result->result_array() as $row) {
            return $row;
        }

        return $row;
    }

    /**
     * Get Produs By linkid
     *
     * @param int $linkid	
     *
     * @return array
     */
    function getProductByLinkID($linkid)
    {
        $row = array();
        $this->db->where('linkid', $linkid);
        $result = $this->db->get('linkshare_produs_partial');

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
    function newProduct($insert_fields)
    {
        $this->db->insert('linkshare_produs_partial', $insert_fields);
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
    function updateProduct($update_fields, $id)
    {
        $this->db->update('linkshare_produs_partial', $update_fields, array('id' => $id));

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
    function updateProductByLinkID($update_fields, $linkid)
    {
        $produs = $this->getProductByLinkID($linkid);
        //preserve insert date
        $update_fields['insert_date'] = $produs['insert_date'];
        $this->db->update('linkshare_produs_partial', $update_fields, array('linkid' => $linkid));

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
    function deleteProduct($id)
    {

        $this->db->delete('linkshare_produs_partial', array('id' => $id));

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
    function existsProduct($linkid)
    {
        $this->db->where('linkid', $linkid);
        $result = $this->db->get('linkshare_produs_partial');
        foreach ($result->result_array() as $row) {
            return true;
        }

        return false;
    }

    /**
     * Sterge Produs partial
     *
     * Truncate produs partial	
     *
     * @return boolean true
     */
    function deletePartialProduct()
    {
        $this->db->truncate('linkshare_produs_partial');

        return true;
    }

}
