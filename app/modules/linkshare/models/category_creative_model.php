<?php

/**
 * Categorie Creative Model
 *
 * @category Category_Creative_Model
 * @package  Linkshare
 * @author   Weblight <office@weblight.ro>
 * @license  License http://www.weblight.ro/
 * @link     http://www.weblight.ro/
 *
 */
class Category_creative_model extends CI_Model 
{

    private $CI;

    public function __construct() 
    {
        parent::__construct();

        $this->CI = & get_instance();
    }

    /**
     * Get Creative Categories
     *
     * @param array $filters The filters to search by
     *
     * @return array
     */
    public function getCategories($filters = array()) 
    {
        $row = array();
        
        $this->load->model('product_model');

        if (isset($filters['limit'])) {
            $offset = (isset($filters['offset'])) ? $filters['offset'] : 0;
            $this->db->limit($filters['limit'], $offset);
        }
        
        if (isset($filters['id_site'])) {
            $this->db->where('id_site', $filters['id_site']);
        }
            
        if (isset($filters['mid'])) {
            $this->db->where('mid', $filters['mid']);
        }
            
        if (isset($filters['cat_id'])) {
            $this->db->where('cat_id', $filters['cat_id']);
        }

        if (isset($filters['mid'])) {
            $this->db->order_by('cat_id');
        } else {
            $this->db->order_by('id');
        }

        if (isset($filters['nume'])) {
            $filters['nume'] = str_replace("%2C", ",", $filters['nume']);
            $filters['nume'] = str_replace('%26', '&', $filters['nume']);
            $filters['nume'] = str_replace('+', ' ', $filters['nume']);
            $this->db->like('name', $filters['nume']);
        }

        $result = $this->db->get('linkshare_categories_creative');
        
        if (isset($filters['name'])) {
            $this->load->model('site_model');
        }

        foreach ($result->result_array() as $linie) {
            if (isset($filters['name'])) {
                $site = $this->site_model->getSite($linie['id_site']);
                $linie['id_site'] = $site['name'];
            }

            $nr_products = $this->product_model->getCountProductsByMID($linie['mid'], $filters['id_site'], array('cat_creative_id' => $linie['cat_id']));

            $linie['nr_products'] = $nr_products;

            $row[] = $linie;
        }

        return $row;
    }

    /**
     * Get Count Products By Mid
     *
     * @param int $id_site Site id
     * @param int $cat_id  Category id
     * @param int $mid     Merchandiser id
     * @param int $flag    Flag
     *
     * @return array
     */
    public function getCountProductsByMID($id_site, $cat_id, $mid, $flag = 1) 
    {
        $this->db->where('id_site', $id_site);
        $this->db->where('cat_creative_id', $cat_id);
        $this->db->where('mid', $mid);
        $this->db->where('parsed', $flag);
        $result = $this->db->get('linkshare_products');

        return $result->num_rows();
    }

    /**
     * Get Temporary Creative Categories
     *
     * @param array $filters The filters to search by
     *
     * @return array
     */
    public function getTempCreativeCategories($filters = array()) 
    {
        $row = array();

        if (isset($filters['limit'])) {
            $offset = (isset($filters['offset'])) ? $filters['offset'] : 0;
            $this->db->limit($filters['limit'], $offset);
        }

        if (isset($filters['id_site'])) {
            $this->db->where('id_site', $filters['id_site']);
        }
            
        if (isset($filters['mid'])) {
            $this->db->where('mid', $filters['mid']);
        }
            
        if (isset($filters['cat_id'])) {
            $this->db->where('cat_id', $filters['cat_id']);
        }
            
        if (isset($filters['mid'])) {
            $this->db->order_by('cat_id');
        } else {
            $this->db->order_by('id');
        }

        if (isset($filters['nume'])) {
            $filters['nume'] = str_replace("%2C", ",", $filters['nume']);
            $filters['nume'] = str_replace('%26', '&', $filters['nume']);
            $filters['nume'] = str_replace('+', ' ', $filters['nume']);
            $this->db->like('name', $filters['nume']);
        }

        $result = $this->db->get('linkshare_categories_creative_temp');
        
        if (isset($filters['name'])) {
            $this->load->model('site_model');
            
            foreach ($result->result_array() as $linie) {
                $site = $this->site_model->getSite($linie['id_site']);
                $linie['id_site'] = $site['name'];
                $row[] = $linie;
            }
        }

        return $row;
    }

    /**
     * Get Temp Creative Category
     *
     * @param int $sid    Site id
     * @param int $cat_id Category id
     * @param int $mid    Merchant id
     * 
     * @return array
     */
    public function getTempCreativeCategory($sid, $cat_id, $mid) 
    {
        $row = array();
        $this->db->where('id_site', $sid);
        $this->db->where('cat_id', $cat_id);
        $this->db->where('mid', $mid);
        $result = $this->db->get('linkshare_categories_creative_temp');

        foreach ($result->result_array() as $row) {
            return $row;
        }

        return $row;
    }

    /**
     * Get All Categories
     * 
     * @return array
     */
    public function get_all_categories() 
    {
        $query = $this->db->get('linkshare_categories_creative');
        return $query->result();
    }

    /**
     * Get Categories Lines
     *
     * @param array $filters The filters to search by
     *
     * @return int
     */
    public function getCategoriesLines($filters) 
    {
        if (isset($filters['id_site'])) {
            $this->db->where('id_site', $filters['id_site']);
        }
            
        if (isset($filters['mid'])) {
            $this->db->where('mid', $filters['mid']);
        }

        if (isset($filters['cat_id'])) {
            $this->db->where('cat_id', $filters['cat_id']);
        }

        if (isset($filters['nume'])) {
            $filters['nume'] = str_replace("%2C", ",", $filters['nume']);
            $filters['nume'] = str_replace('%26', '&', $filters['nume']);
            $filters['nume'] = str_replace('+', ' ', $filters['nume']);
            $this->db->like('name', $filters['nume']);
        }

        $result = $this->db->get('linkshare_categories_creative');

        return $result->num_rows();
    }

    /**
     * Get Temp Creative Categories Lines
     *
     * @param array $filters The filters to search by
     *
     * @return int
     */
    public function getTempCategoriesLines($filters) 
    {
        if (isset($filters['id_site'])) {
            $this->db->where('id_site', $filters['id_site']);
        }

        if (isset($filters['mid'])) {
            $this->db->where('mid', $filters['mid']);
        }

        if (isset($filters['nume'])) {
            $filters['nume'] = str_replace("%2C", ",", $filters['nume']);
            $filters['nume'] = str_replace('%26', '&', $filters['nume']);
            $filters['nume'] = str_replace('+', ' ', $filters['nume']);
            $this->db->like('name', $filters['nume']);
        }

        $result = $this->db->get('linkshare_categories_creative_temp');

        return $result->num_rows();
    }

    /**
     * Get Category
     *
     * @param int $id Category id
     *
     * @return array
     */
    public function getCategory($id) 
    {
        $row = array();
        $this->db->where('id', $id);
        $result = $this->db->get('linkshare_categories_creative');

        foreach ($result->result_array() as $row) {
            return $row;
        }

        return $row;
    }

    /**
     * Get Category Name
     *
     * @param int $id Category identifier
     *
     * @return array
     */
    public function getCategoryName($id) 
    {
        $row = array();
        $this->db->where('id', $id);
        $result = $this->db->get('linkshare_categories_creative');

        foreach ($result->result_array() as $row) {
            return $row['name'];
        }

        return $row;
    }
    
    /**
     * Get Category Creative By cat_id
     *
     * @param int $catId Category id	
     *
     * @return array
     */
    public function getCategoryCreativeByCatId($catId) 
    {
        $row = array();
        $this->db->where('cat_id', $catId);
        $result = $this->db->get('linkshare_categories_creative');

        foreach ($result->result_array() as $row) {
            return $row;
        }

        return $row;
    }        

    /**
     * Create New Category
     *
     * @param array $insert_fields Fields to insert	
     *
     * @return int $insert_id
     */
    public function newCategory($insert_fields)
    {
        $this->db->insert('linkshare_categories', $insert_fields);
        return $this->db->insert_id();
    }

    /**
     * Create New Creative Category
     *
     * @param array $insert_fields The fields to insert	
     *
     * @return int $insert_id
     */
    public function newCreativeCategory($insert_fields) 
    {
        if (array_key_exists('id', $insert_fields)) {
            unset($insert_fields['id']);
        }

        $this->db->insert('linkshare_categories_creative', $insert_fields);
        return $this->db->insert_id();
    }

    /**
     * Create New Temp Creative Category
     *
     * @param array $insert_fields The fields to insert	
     *
     * @return int $insert_id
     */
    public function newTempCreativeCategory($insert_fields) 
    {
        $this->db->insert('linkshare_categories_creative_temp', $insert_fields);
        return $this->db->insert_id();
    }

    /**
     * Delete Category By Mid
     *
     * @param int $id_site Site id
     * @param int $mid     Merchant id	
     *
     * @return boolean true
     */
    public function deleteCategoryByMid($id_site, $mid) 
    {
        $this->db->delete('linkshare_categories_creative', array('id_site' => $id_site, 'mid' => $mid));

        return true;
    }

    /**
     * Delete Temp Category By Mid
     *
     * @param int $id_site Site id
     * @param int $mid     Merchant id 	
     *
     * @return boolean true
     */
    public function deleteTempCategoryByMid($id_site, $mid) 
    {
        $this->db->delete('linkshare_categories_creative_temp', array('id_site' => $id_site, 'mid' => $mid));

        return true;
    }

    /**
     * Update Category
     * 
     * @param array $update_fields Fields to update
     * @param int $id              Category id 	
     *
     * @return boolean true
     */
    public function updateCategory($update_fields, $id) 
    {
        $this->db->update('linkshare_categories_creative', $update_fields, array('id' => $id));

        return true;
    }

    /**
     * Delete Category
     * 	
     * @param int $id Category id	
     *
     * @return boolean true
     */
    public function deleteCategory($id) 
    {
        $this->db->delete('linkshare_categories_creative', array('id' => $id));

        return true;
    }

    /**
     * Get Categories Parse
     *
     * @param array $filters Filters to parse by
     * 
     * @return array
     * 
     */
    public function getCategoriesParse($filters) 
    {
        if ($filters['limit']) {
            return array_slice($filters['categories'], $filters['offset'], $filters['limit']);
        }

        return $filters['categories'];
    }

    /**
     * Parse Creative Categories
     *
     * @param array $params The params to parse by
     * 
     * @return array
     * 
     */
    public function parseCategories($params) 
    {
        if (isset($params[0]['limit'])) {
            $limit = $params[0]['limit'];
        } else {
            $limit = 10;
        }
            
        if (isset($params[0]['offset'])) {
            $offset = $params[0]['offset'];
        } else {
            $offset = 0;
        }

        return array_slice($params, $offset, $limit, true);
    }
    
    /**
     * New Merged Categories
     *
     * @param string $merged_category Merged category
     * @param int $sid                Site id
     * 
     * @return array
     * 
     */
    public function newMergedCategory($merged_category, $sid) 
    {
        $insert_fields = array(
            'name' => $merged_category,
            'id_site' => $sid
        );

        $this->db->insert('linkshare_categories_merged', $insert_fields);
        return $this->db->insert_id();
    }
    
    /**
     * New Join Categories
     * 
     * @param int $id_merged_category Merged category
     * @param int $check_category     Category check
     * 
     * @return array
     * 
     */
    public function newJoinCategory($id_merged_category, $check_category)
    {
        $checkedCategories = explode(',', $check_category);
        
        foreach ($checkedCategories as $checkedId) {
            $temp = $this->getCategory($checkedId);
            
            $insert_fields = array(
                'cat_id' => $temp['cat_id'],
                'mid' => $temp['mid'],
                'id_categ_merged' => $id_merged_category,
            );

            $this->db->insert('linkshare_categories_joins', $insert_fields);
            $insert_id[] = $this->db->insert_id();
            
        }
       
        return $insert_id;
    }

    /**
     * Get Creative For Merge
     * 
     * @param array $filters The filters for merge
     * 
     * @return array
     */
    public function getCreativeForMerge($filters) 
    {
        $this->load->model('site_model');
        $row = array();

//        print '<pre>MODEL';
//        print_r($filters);
//        print '</pre>';

        if (isset($filters['limit'])) {
            $offset = (isset($filters['offset'])) ? $filters['offset'] : 0;
            $this->db->limit($filters['limit'], $offset);
        }
        
        if (isset($filters['id_site'])) {
            $this->db->where('id_site', $filters['id_site']);
        }
            
        if (isset($filters['nume'])) {
            $filters['nume'] = str_replace("%2C", ",", $filters['nume']);
            $filters['nume'] = str_replace('%26', '&', $filters['nume']);
            $filters['nume'] = str_replace('+', ' ', $filters['nume']);
            $this->db->like('name', $filters['nume']);
        }
        
        if (isset($filters['cat_creativ_id'])) {
            $this->db->where('cat_id', $filters['cat_creativ_id']);
        }
            
        if ($filters['mid'] != '') {
            $this->db->where('mid', $filters['mid']);
            $this->db->order_by('cat_id');
        } else {
            $this->db->order_by('id');
        }

        $result = $this->db->get('linkshare_categories_creative');
        
        if (isset($filters['name'])) {
            foreach ($result->result_array() as $linie) {

                if (isset($filters['name'])) {
                    $site = $this->site_model->getSite($linie['id_site']);
                    $linie['id_site'] = $site['name'];
                }

                $merge_categories = $this->getCreativeMerged($linie['cat_id'], $filters['id_site']);
                $linie['merge_categories'] = $merge_categories;

                $check_array = explode(',', $filters['check_category']);
                in_array($linie['cat_id'], $check_array, true) ? $linie['checked'] = 1 : $linie['checked'] = 0;

                $nr_products = $this->getCountProductsByMID($filters['id_site'], $linie['cat_id'], $linie['mid']);
                $linie['nr_products'] = $nr_products;

                $row[] = $linie;
            }
            
            return $row;
        }
            
        return $result->result_array();
    }
    
    /**
     * Get Creative Merged
     * 
     * @param int $cat_id Category id
     * @param int $sid    Site id
     * 
     * @return array
     */
    public function getCreativeMerged($cat_id, $sid) 
    {
        $row = array();
        $names = array();
        $this->db->where('cat_id', $cat_id);
        $this->db->where('id_site', $sid);
        $this->db->join('linkshare_categories_merged', 'linkshare_categories_joins.id_categ_merged = linkshare_categories_merged.id', 'left');
        $result = $this->db->get('linkshare_categories_joins');

        foreach ($result->result_array() as $row){
            $names[] = $row['name'];
        }

        return $names;
    }

    /**
     * Get Merged Creative Category by ID
     * 
     * @param int $id Category id 
     * 
     * @return array
     */
    public function getMergedCategoryByID($id) 
    {
        $this->db->where('ID', $id);
        $result = $this->db->get('linkshare_categories_merged');

        foreach ($result->result_array() as $row) {
            return $row;
        }

        return $row;
    }

    /**
     * Get Merged Creative Category by SID
     * 
     * @param int    $sid  Site id
     * @param string $name Name of the created category merged
     * 
     * @return array
     */
    public function getMergedCategory($sid, $name = null) 
    {
        $this->db->where('id_site', $sid);
        
        if (isset($name)) {
            $this->db->where('name', $name);
        }
        
        $result = $this->db->get('linkshare_categories_merged');

        return $result->result_array();
    }

    /**
     * Get Joins Category
     * 
     * @param int $id_categ_merged Category id merged
     * @param int $id_site         Site (channel) id
     * 
     * @return array
     */
    public function getJoinsCategory($id_categ_merged, $id_site = 0) 
    {
        $this->db->where('id_categ_merged', $id_categ_merged);

        $result = $this->db->get('linkshare_categories_joins');

        foreach ($result->result_array() as $val) {
            if ($id_site) {
                $nr_products = $this->getCountProductsByMID($id_site, $val['cat_id'], $val['mid']);
                $val['nr_products'] = $nr_products;
            }

            $temp = $this->getCategoryCreativeByCatId($val['cat_id']);
            $val['name'] = $temp['name'];
 
            $row[] = $val;
        }
        
        return $row;
    }
    
    /**
     * List Merged Category
     * 
     * @param array $filters The filters to search by
     * 
     * @return array
     */
    public function listMergedCategory($filters) 
    {
        $id_merged_category = $this->getMergedCategory($filters['id_site']);

        foreach ($id_merged_category as $row) {
            $listMergedCategory = array();

            $categories = $this->getJoinsCategory($row['ID'], $filters['id_site']);            

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
        }
        //echo "<pre>"; print_r($categories); echo "</pre>";         
        //$nr_products = $this->getCountProductsByMID($filters['id_site'], $val['cat_id'], $val['mid']);

        return $result;
    }
    
    /**
     * Update Merged Category
     * 
     * @param array $update_fields Fields to update
     * @param int $id              Category id 
     * 
     * @return boolean
     */
    public function updateMergedCategory($update_fields, $id) 
    {
        $this->db->update('linkshare_categories_merged', $update_fields, array('ID' => $id));
        return true;
    }
    
    /**
     * Delete Merged Category
     * 
     * @param int $id Category id
     * 
     * @return boolean true
     */
    public function deleteMergedCategory($id) 
    {
        $this->db->delete('linkshare_categories_merged', array('id' => $id));
        return true;
    }
         
    /**
     * Delete Join Category
     * 
     * @param int $id
     * 
     * @return boolean true
     */
    public function deleteJoinCategory($categoryId, $idCategMerged, $mid = null) 
    {
        $filters = array(
          'cat_id' => $categoryId,
          'id_categ_merged' =>  $idCategMerged 
        );
        
        if (isset($mid)) {
            $filters['mid'] = $mid;
        }
        
        $this->db->delete('linkshare_categories_joins', $filters);
        return true;
    }

    /**
     * Count Join Category	
     * 
     * @param int $id_categ_merged	
     *
     * @return array
     */
    public function countJoinCategory($id_categ_merged) 
    {
        $this->db->where('id_categ_merged', $id_categ_merged);
        $result = $this->db->get('linkshare_categories_joins');

        return $result->num_rows();
    }

    /**
     * Check Temp Creative Category exists by mid,sid,cat_id    
     * 	
     * @param int $sid    Site id
     * @param int $cat_id Category id 
     * @param int $mid    Merchant id
     *
     * @return boolean true
     */
    public function existsTempCC($sid, $cat_id, $mid) 
    {
        $this->db->where('id_site', $sid);
        $this->db->where('cat_id', $cat_id);
        $this->db->where('mid', $mid);
        $result = $this->db->get('linkshare_categories_creative_temp');

        $row = array();
        foreach ($result->result_array() as $row) {
            return $row;
        }

        return $row;
    }

    /**
     * Update Creative Categories from Temp    
     * 
     * @param array $update_fields The fields to update
     * @param int $id              Category id
     * @param int $sid             Site id
     * @param int $cat_id          Category id
     * @param int $mid             Merchant id
     *
     * @return boolean true
     */
    public function updateCreativeCategoriesFromTemp($update_fields, $sid, $cat_id, $mid) 
    {
        if (array_key_exists('id', $update_fields)) {
            unset($update_fields['id']);
        }
        
        if (array_key_exists('id_site', $update_fields)) {
            unset($update_fields['id_site']);
        }
        
        if (array_key_exists('live', $update_fields)) {
            unset($update_fields['live']);
        }

        $this->db->update('linkshare_categories_creative', $update_fields, array('id_site' => $sid, 'cat_id' => $cat_id, 'mid' => $mid));

        return true;
    }

    /**
     * Delete Temp Creative Category
     * 
     * @param int $sid	  Site id
     * @param int $cat_id Category id
     * @param int $mid    Merchand id
     *
     * @return boolean true
     */
    public function deleteTempCreativeCategory($sid, $cat_id, $mid) 
    {
        $this->db->where('id_site', $sid);
        $this->db->where('cat_id', $cat_id);
        $this->db->where('mid', $mid);
        $this->db->delete('linkshare_categories_creative_temp');

        return true;
    }

    /**
     * Delete Temporary Categories    	
     *
     * @return boolean true
     */
    public function deleteTempCreativeCategories() 
    {
        $this->db->truncate('linkshare_categories_creative_temp');
        return true;
    }

}
