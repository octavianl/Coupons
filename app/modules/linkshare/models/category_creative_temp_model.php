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
class Category_creative_temp_model extends CI_Model 
{

    private $CI;

    public function __construct() 
    {
        parent::__construct();

        $this->CI = & get_instance();
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
