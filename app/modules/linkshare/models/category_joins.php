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
class Advertiser_model extends CI_Model {

    private $CI;

    function __construct() {
        parent::__construct();

        $this->CI = & get_instance();
    }
    
    /**
     * Get Joins Categories for export csv file
     * 
     *
     * @return array
     */
    
    public function getJoinsCategoriesForExport() {
        
        $id_merged_category = $this->getMergedCategory($filters['id_site']);

        foreach ($id_merged_category as $row) {
            $listMergedCategory = array();

            $categories = $this->getJoinsCategory($row['ID'],$filters['id_site']);            

            foreach ($categories as $val) {
                $nr_products = $this->getCountProductsByMID($filters['id_site'], $val['cat_id'], $val['mid']);
                $total_products += $nr_products;
            }
            
            $listMergedCategory['category_merged_ID'] = $row['ID'];
            $listMergedCategory['category_merged_name'] = $row['name'];
            $listMergedCategory['categories_merged'] = $categories;
            $listMergedCategory['count_merged'] = $this->countJoinCategory($row['ID']);
            $listMergedCategory['nr_products'] = $total_products;

            $result[] = $listMergedCategory;
            unset($category_merged);
        }
        //echo "<pre>"; print_r($categories); echo "</pre>";         
        //$nr_products = $this->getCountProductsByMID($filters['id_site'], $val['cat_id'], $val['mid']);

        return $result;
        
    }
    
}




