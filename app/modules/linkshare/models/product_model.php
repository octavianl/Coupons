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
     * Get Products
     *
     * @return array
     */
    function getProducts()
    {
        $row = array();
        $result = $this->db->get('linkshare_products');

        foreach ($result->result_array() as $linie) {
            $row[] = $linie;
        }

        return $row;
    }

    /**
     * Get Products By Mid
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
        if (isset($params['linkid']))
            $this->db->where('linkid', $params['linkid']);
        if (isset($params['cat_creative_id']))
            $this->db->where('cat_creative_id', $params['cat_creative_id']);
        if (isset($params['cat_creative_name']))
            $this->db->like('cat_creative_name', $params['cat_creative_name']);
        if (isset($params['parsed']))
            $this->db->where('parsed', $params['parsed']);
        $result = $this->db->get('linkshare_products');

        foreach ($result->result_array() as $linie) {
            $row[] = $linie;
        }

        return $row;
    }

    /**
     * Get Count Products By Mid
     *
     * @param int $mid
     * @param int $id_site
     * @param array $filters
     *
     * @return array
     */
    function getCountProductsByMID($mid, $id_site, $filters = array()) {
        /* $i = 0;
          $row = array(); */
        $this->db->where('mid', $mid);
        $this->db->where('id_site', $id_site);
        if (isset($filters['cat_creative_id']))
            $this->db->where('cat_creative_id', $filters['cat_creative_id']);
        if (isset($filters['cat_creative_name']))
            $this->db->where('cat_creative_name', $filters['cat_creative_name']);
        if (isset($filters['parsed']))
            $this->db->where('parsed', $filters['parsed']);
        $result = $this->db->get('linkshare_products');

        return $result->num_rows();

        //crapa memoria daca parcurg tot vectorul :)
        /* foreach ($result->result_array() as $row) {
          $i++;
          }

          return $i; */
    }
    
    /**
     * Get Count Temp Products By Mid
     *
     * @param int $mid
     * @param int $id_site
     * @param array $filters
     *
     * @return array
     */
    function getCountTempProductsByMID($mid, $id_site, $filters = array()) {
        /* $i = 0;
          $row = array(); */
        $this->db->where('mid', $mid);
        $this->db->where('id_site', $id_site);
        if (isset($filters['cat_creative_id']))
            $this->db->where('cat_creative_id', $filters['cat_creative_id']);
        if (isset($filters['cat_creative_name']))
            $this->db->where('cat_creative_name', $filters['cat_creative_name']);
        if (isset($filters['parsed']))
            $this->db->where('parsed', $filters['parsed']);
        $result = $this->db->get('linkshare_products_temp');

        return $result->num_rows();

        //crapa memoria daca parcurg tot vectorul :)
        /* foreach ($result->result_array() as $row) {
          $i++;
          }

          return $i; */
    }
    
    /**
     * Get Products By Mid
     * @param array $filters
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
        if (isset($filters['linkid']))
            $this->db->where('linkid', $filters['linkid']);
        if (isset($filters['mid']))
            $this->db->where('mid', $filters['mid']);
        if (isset($filters['cat_creative_id']))
            $this->db->where('cat_creative_id', $filters['cat_creative_id']);
        if (isset($filters['cat_creative_name']))
            $this->db->like('cat_creative_name', $filters['cat_creative_name']);
        if (isset($filters['parsed']))
            $this->db->where('parsed', $filters['parsed']);
        $result = $this->db->get('linkshare_products_temp');

        foreach ($result->result_array() as $linie) {

            $row[] = $linie;
        }

        return $row;
    }
    
     /**
     * Get Temp Product
     * 	
     * @param array $filters	
     *
     * @return boolean true
     */
    function getTempProduct($filters) {
        $this->db->where('id_site', $filters['id_site']);
        $this->db->where('cat_creative_id', $filters['cat_creative_id']);
        $this->db->where('mid', $filters['mid']);
        $this->db->where('linkid', $filters['linkid']);
        $result = $this->db->get('linkshare_products_temp');
        
        $row = array();
        foreach ($result->result_array() as $row) {
            return $row;
        }

        return $row;
    }
    
    /**
     * Get Product Status
     *
     * @return array
     */
    function getProductStatus()
    {
        $row = array();
        $result = $this->db->get('linkshare_products');

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
        $result = $this->db->get('linkshare_products');

        foreach ($result->result_array() as $value) {
            $available = $value['available'];
        }

        if ($available == 'yes')
            $available = 'no';
        elseif ($available == 'no')
            $available = 'yes';

        $update_fields['available'] = $available;
        $filters = array ('id' => $id_product);
        $this->updateProduct($update_fields, $filters);

        return true;
    }
    
    /**
     * Change Temp Produs Status
     * 
     * @param int $id_product
     * @param int $sid
     * @param int $cat_id
     * @param int $mid
     * @param int $linkid
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
        $result = $this->db->get('linkshare_products_temp');

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
        $result = $this->db->get('linkshare_products');

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
        $result = $this->db->get('linkshare_products');

        foreach ($result->result_array() as $row) {
            return $row;
        }

        return $row;
    }

    /**
     * Create New Produs
     *
     * @param array $insert_fields	
     *
     * @return int $insert_id
     */
    function newProduct($insert_fields)
    {
        $this->db->insert('linkshare_products', $insert_fields);
        $insert_id = $this->db->insert_id();

        return $insert_id;
    }
 
    /**
     * Update Product
     * 
     * @param array $update_fields
     * @param array $filters
     *
     * @return boolean true
     */
    function updateProduct($update_fields, $filters)
    {
        $this->db->update('linkshare_products', $update_fields, $filters);

        return true;
    }

    /**
     * Update Product By Linkid
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
        $this->db->update('linkshare_products', $update_fields, array('linkid' => $linkid));

        return true;
    }

    /**
     * Delete Produs
     * 	
     * @param array $filters	
     *
     * @return boolean true
     */
    function deleteProduct($filters)
    {
        $this->db->delete('linkshare_products', $filters);

        return true;
    }

    /**
     * Checks Product Exists
     * 	
     * @param int $linkid	
     *
     * @return boolean
     */
    function existsProduct($linkid)
    {
        $this->db->where('linkid', $linkid);
        $result = $this->db->get('linkshare_products');
        foreach ($result->result_array() as $row) {
            return true;
        }

        return false;
    }

    // TEMP PRODUCTS
       
    /**
     * Create New Temp Products
     *
     * @param array $insert_fields	
     *
     * @return int $insert_id
     */
    function newTempProduct($insert_fields)
    {
        $this->db->insert('linkshare_products_temp', $insert_fields);
        $insert_id = $this->db->insert_id();

        return $insert_id;
    }
    
    /**
     * Update New Temp Products
     *
     * @param array $update_fields	
     * @param int $sid	
     * @param int $mid	
     * @param int $cat_id	
     * @param int $linkid	
     *
     * @return int $insert_id
     */
    function updateTempProduct($update_fields, $sid, $mid, $cat_id, $linkid)
    {
        $this->db->where('id_site', $sid);
        $this->db->where('mid', $mid);
        $this->db->where('cat_creative_id', $cat_id);
        $this->db->where('linkid', $linkid);
        $this->db->update('linkshare_products_temp', $update_fields);

        return $insert_id;
    }
    
    /**
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
        $result = $this->db->get('linkshare_products_temp');

        foreach ($result->result_array() as $row) {
            return $row;
        }

        return $row;
    }
       
    /**
     * Delete Temp Products
     * 	
     * @param int $id	
     *
     * @return boolean true
     */
    function deleteTempProduct($filters)
    {
        $this->db->delete('linkshare_products_temp', $filters);

        return true;
    }
    
        
    /**
     * Delete Temporary Products
     * 	
     * @param int $id	
     *
     * @return boolean true
     */
    function deleteTempProducts() {
        $this->db->truncate('linkshare_products_temp');
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
        $result = $this->db->get('linkshare_products_temp');
        
        $row = array();
        foreach ($result->result_array() as $row) {
            return $row;
        }

        return $row;
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
        $this->db->update('linkshare_products_temp', $update_fields);

        return true;
    }
    
    public function checkProducts($current, $temp) 
    {
        $updateFields = array(
            'cat_creative_name',
            'merchantname',
            'linkname',
            'nid',
            'click_url',
            'icon_url',
            'show_url',
            'productname',
            'categ_primary',
            'categ_secondary',
            'price',
            'saleprice',
            'currency',
            'upccode',
            'description_short',
            'description_long',
            'keywords',
            'linkurl',
            'imageurl',
            'price_list',
            'price_save',
            'price_final'
        );
        
        $productFields = array();
        
        foreach ($updateFields as $field) {
            if (isset($temp[$field]) && $temp[$field] != $current[$field]) {
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
     * @param array $where	 desc
     *
     * @return boolean true
     */
    function updateProductArray($update_fields, $where)
    {
        if (empty($update_fields)) {
            return false;
        }
        $this->db->update('linkshare_products', $update_fields, $where);

        return true;
    }
    
    // $fields vine din controller cu siteid,mid,catid
    public function updateProductCurrent($current, $temp)
    {
        $where = array(
            'mid'               => $current['mid'],
            'cat_creative_id'   => $current['cat_creative_id'],
            'linkid'            => $current['linkid'],
            'id_site'           => $current['id_site']
        );
        
        $this->updateProductArray($this->checkProducts($current, $temp), $where);
    }

}
