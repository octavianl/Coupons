<?php

/**
 * Category Joins - Manages joins category, exports
 *
 * @category Admin Controller
 * @package  Linkshare
 * @author   Weblight <office@weblight.ro>
 * @license  License http://www.weblight.ro/
 * @link     http://www.weblight.ro/
 *
 */
class Category_joins extends CI_Model {

    private $CI;

    function __construct() {
        parent::__construct();

        $this->CI = & get_instance();
    }
    
    /**
     * Get Joins Category
     * 
     * @param int $id_categ_merged id_categ_merged
     * @param int $id_site         Site (channel) id
     * 
     * @return array
     */
    function getJoinsCategory($id_categ_merged, $id_site = 0) 
    {
        $this->load->model('category_creative_model');
        
        $this->db->where('id_categ_merged', $id_categ_merged);

        $result = $this->db->get('linkshare_categories_joins');

        foreach ($result->result_array() as $val) {
            if ($id_site) {
                $nr_products = $this->getCountProductsByMID($id_site, $val['cat_id'], $val['mid']);
                $val['nr_products'] = $nr_products;
            }

            $temp = $this->category_creative_model->getCategoryCreativeByCatId($val['cat_id']);
            $val['name'] = $temp['name'];
 
            $row[]=$val;
        }
        
        return $row;
    }
    
    /**
     * Get Count Products By Mid
     *
     * @param int $id_site
     * @param int $cat_id
     * @param int $mid merchandiser id
     * @param int $flag
     *
     * @return array
     */
    function getCountProductsByMID($id_site, $cat_id, $mid, $flag = 1) {

        $this->db->where('id_site', $id_site);
        $this->db->where('cat_creative_id', $cat_id);
        $this->db->where('mid', $mid);
        $this->db->where('parsed', $flag);
        $result = $this->db->get('linkshare_products');

        return $result->num_rows();
    }
    
    /**
     * Count Join Category	
     * 
     * @param int $id_categ_merged	
     *
     * @return array
     */
    function countJoinCategory($id_categ_merged) {
        $this->db->where('id_categ_merged', $id_categ_merged);
        $result = $this->db->get('linkshare_categories_joins');

        return $result->num_rows();
    }

    
}




