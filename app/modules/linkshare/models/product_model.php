<?php

/**
 * Produs Model - Manages produs
 *
 * @category Admin Controller
 * @package  Linkshare
 * @author   Weblight <office@weblight.ro>
 * @license  License http://www.weblight.ro/
 * @link     http://www.weblight.ro/
 *
 */

class Product_model extends CI_Model
{
    private $CI;

    function __construct()
    {
        parent::__construct();

        $this->CI = & get_instance();
    }

    /**
     * 
     * Get Produse
     *
     * @return array
     */
    function getProducts()
    {
        $row = array();
        $result = $this->db->get('linkshare_produs');

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
        if (isset($params['id_site']))
            $this->db->where('id_site', $params['id_site']);
        if (isset($params['mid']))
            $this->db->where('mid', $params['mid']);
        if (isset($params['cat_creative_id']))
            $this->db->where('cat_creative_id', $params['cat_creative_id']);
        if (isset($params['cat_creative_name']))
            $this->db->where('cat_creative_name', $params['cat_creative_name']);
        $result = $this->db->get('linkshare_produs');

        foreach ($result->result_array() as $linie) {
            $row[] = $linie;
        }

        return $row;
    }

    /**
     * Get Produse By Mid and cat_id
     * @param array $params
     *
     * @return array
     */
    function getTempProductsByMid($filters)
    {
        $row = array();
        if (isset($filters['limit'])) {
            $offset = (isset($filters['offset'])) ? $filters['offset'] : 0;
            $this->db->limit($filters['limit'], $offset);
        }
        if (isset($filters['id_site']))
            $this->db->where('id_site', $filters['id_site']);
        if (isset($filters['mid']))
            $this->db->where('mid', $filters['mid']);
        if (isset($filters['cat_creative_id']))
            $this->db->where('cat_creative_id', $filters['cat_creative_id']);
        if (isset($params['cat_creative_name']))
            $this->db->where('cat_creative_name', $filters['cat_creative_name']);
        $result = $this->db->get('linkshare_produs_temp');

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
    function getProductStatus()
    {
        $row = array();
        $result = $this->db->get('linkshare_produs');

        foreach ($result->result_array() as $value) {
            $row[$value['id']] = $value['name'];
        }

        return $row;
    }

    /**
     * Change Produs Status
     * @param int $id_product
     *
     * @return boolean
     */
    function changeProductStatus($id_product)
    {
        $row = array();
        $this->db->where('id', $id_product);
        $result = $this->db->get('linkshare_produs');

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
     * Change Temp Produs Status
     * @param int $id_product
     *
     * @return boolean
     */
    function changeTempProductStatus($id_product,$sid,$cat_id,$mid,$linkid)
    {
        $row = array();
        $this->db->where('id', $id_product);
        $this->db->where('id_site', $sid);
        $this->db->where('cat_creative_id', $cat_id);
        $this->db->where('mid', $mid);
        $this->db->where('linkid', $linkid);
        $result = $this->db->get('linkshare_produs_temp');

        foreach ($result->result_array() as $value) {
            $available = $value['available'];
        }

        if ($available == 'yes')
            $available = 'no';
        elseif ($available == 'no')
            $available = 'yes';

        $update_fields['available'] = $available;
        $this->updateTempProduct($update_fields, $sid, $mid, $cat_id, $linkid);

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
        $result = $this->db->get('linkshare_produs');

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
        $result = $this->db->get('linkshare_produs');

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
        $this->db->insert('linkshare_produs', $insert_fields);
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
        $this->db->update('linkshare_produs', $update_fields, array('id' => $id));

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
        $this->db->update('linkshare_produs', $update_fields, array('linkid' => $linkid));

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
        $this->db->delete('linkshare_produs', array('id' => $id));

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
        $result = $this->db->get('linkshare_produs');
        foreach ($result->result_array() as $row) {
            return true;
        }

        return false;
    }

    // TEMP PRODUCTS
       
    /**
     * Create New Temp Products
     *
     * Creates a new produs
     *
     * @param array $insert_fields	
     *
     * @return int $insert_id
     */
    function newTempProduct($insert_fields)
    {
        $this->db->insert('linkshare_produs_temp', $insert_fields);
        $insert_id = $this->db->insert_id();

        return $insert_id;
    }
    
    /**
     * Create New Temp Products
     *
     * Creates a new produs
     *
     * @param array $insert_fields	
     *
     * @return int $insert_id
     */
    function updateTempProduct($update_fields, $sid, $mid, $cat_id, $linkid)
    {
        $this->db->where('id_site', $sid);
        $this->db->where('mid', $mid);
        $this->db->where('cat_creative_id', $cat_id);
        $this->db->where('linkid', $linkid);
        $this->db->update('linkshare_produs_temp', $update_fields);

        return $insert_id;
    }
    
    /**
     * 
     * Get Temp Products
     *
     * @return array
     */
    function getTempProducts($mid,$cat_id,$limit=0,$flag)
    {
        $row = array();
        if($limit) { $this->db->limit($limit, 0); }
        $this->db->where('mid', $mid);
        $this->db->where('cat_creative_id', $cat_id);
        $this->db->where('parsed', $flag);
        $result = $this->db->get('linkshare_produs_temp');

        foreach ($result->result_array() as $row) {
            return $row;
        }

        return $row;
    }
       
    /**
     * Delete Temp Produs
     *
     * Deletes produs
     * 	
     * @param int $id	
     *
     * @return boolean true
     */
    function deleteTempProduct($id)
    {
        $this->db->delete('linkshare_produs_temp', array('id' => $id));

        return true;
    }
    
    /**
     * Check Temp Product exists by mid,sid,cat_id,link_id
     *
     * existsAdvertiser
     * 	
     * @param int $mid	
     *
     * @return boolean true
     */
    function existsTempProduct($sid, $cat_id, $mid, $link_id) {
        $this->db->where('id_site', $sid);
        $this->db->where('cat_creative_id', $cat_id);
        $this->db->where('mid', $mid);
        $this->db->where('linkid', $link_id);
        $result = $this->db->get('linkshare_produs_temp');
        
        foreach ($result->result_array() as $row) {
            if(isset($row)) {return true;} else { break; }
        }

        return false;
    }
    
    /**
     * Update Temp Produs By Linkid
     *
     * Updates produs
     * 
     * @param array $update_fields
     * @param int $linkid	
     *
     * @return boolean true
     */
    function updateTempProductByLinkID($update_fields, $linkid)
    {
        $this->db->where('linkid', $linkid);
        $this->db->update('linkshare_produs_temp', $update_fields);

        return true;
    }
    
    public function checkProducts($current, $temp) 
    {
        $updateFields = array(
            'linkname',
            'nid'
        );
        
        $productFields = array();
        
        foreach ($updateFields as $field) {
            if (isset($temp[$field])
                && $temp['field'] != $current['field']) {
                $productFields[$field] = $temp[$field];
            }
        }
        
        return $productFields;
    }
    
    /**
     * Update Produs
     *
     * Updates produs
     * 
     * @param array $update_fields desc
     * @param array $fields	 desc
     *
     * @return boolean true
     */
    function updateProductArray($update_fields, $fields)
    {                
        $this->db->update('linkshare_produs', $update_fields, $fields);

        return true;
    }
    
    // $fields vine din controller cu siteid,mid,catid
    public function updateProductCurrent($current, $temp, $fields)
    {
        $this->updateProductArray($this->checkProducts($current, $temp), $fields);
    }        
}
