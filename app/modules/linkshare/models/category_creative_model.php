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

    function __construct()
    {
        parent::__construct();

        $this->CI = & get_instance();
    }

    /**
     * Get Categorii
     *
     * @param array $filters 
     *
     * @return array
     */
    function getCategories($filters)
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
        if (isset($filters['mid']))
            $this->db->order_by('cat_id');
        else
            $this->db->order_by('id');

        if (isset($filters['nume'])) {
            $filters['nume'] = str_replace("%2C", ",", $filters['nume']);
            $filters['nume'] = str_replace('%26', '&', $filters['nume']);
            $filters['nume'] = str_replace('+', ' ', $filters['nume']);
            $this->db->like('name', $filters['nume']);
        }

        $result = $this->db->get('linkshare_categories_creative');
        if (isset($filters['name']))
            $this->load->model('site_model');

        foreach ($result->result_array() as $linie) {
            if (isset($filters['name'])) {
                $site = $this->site_model->getSite($linie['id_site']);
                $linie['id_site'] = $site['name'];
            }
            $row[] = $linie;
        }

        return $row;
    }

    /**
     * Get Categorii Linii
     *
     * @param array $filters 
     *
     * @return int
     */
    function getCategoriesLines($filters)
    {
        if (isset($filters['id_site']))
            $this->db->where('id_site', $filters['id_site']);
        if (isset($filters['mid']))
            $this->db->where('mid', $filters['mid']);

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
     * Get Categorie
     *
     * @param int $id	
     *
     * @return array
     */
    function getCategory($id)
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
     * Get Categorie Name
     *
     * @param int $id	
     *
     * @return array
     */
    function getCategoryName($id)
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
     * Create New Categorie
     *
     * Creates a new categorie
     *
     * @param array $insert_fields	
     *
     * @return int $insert_id
     */
    function newCategory($insert_fields)
    {
        $this->db->insert('linkshare_categories_creative', $insert_fields);
        $insert_id = $this->db->insert_id();

        return $insert_id;
    }

    /**
     * Delete Categorie By Mid
     *
     * Deletes categorie by mid
     * @param int $id_site
     * @param int $mid	
     *
     * @return boolean true
     */
    function deleteCategoryByMid($id_site, $mid)
    {
        $this->db->delete('linkshare_categories_creative', array('id_site' => $id_site, 'mid' => $mid));

        return true;
    }

    /**
     * Update Categorie
     *
     * Updates categorie
     * 
     * @param array $update_fields
     * @param int $id	
     *
     * @return boolean true
     */
    function updateCategory($update_fields, $id)
    {
        $this->db->update('linkshare_categories_creative', $update_fields, array('id' => $id));

        return true;
    }

    /**
     * Delete Categorie
     *
     * Deletes categorie
     * 	
     * @param int $id	
     *
     * @return boolean true
     */
    function deleteCategory($id)
    {
        $this->db->delete('linkshare_categories_creative', array('id' => $id));

        return true;
    }

    /**
     * Get Categorii Parse
     *
     * @param array $filters
     * 
     * @return array
     * 
     */
    function getCategoriesParse($filters)
    {
        if ($filters['limit']) {
            return array_slice($filters['categories'], $filters['offset'], $filters['limit']);
        }
        return $filters['categories'];
    }

    function parseCategories($params)
    {
        if (isset($params[0]['limit']))
            $limit = $params[0]['limit'];
        else
            $limit = 10;
        if (isset($params[0]['offset']))
            $offset = $params[0]['offset'];
        else
            $offset = 0;
        return array_slice($params, $offset, $limit, true);
    }
    
    function newMergedCategory($merged_category)
    {
        $insert_fields = array ('name' => $merged_category);
        $this->db->insert('linkshare_categories_merged', $insert_fields);
        $insert_id = $this->db->insert_id();

        return $insert_id;
    }
    
    function newJoinCategory($id_merged_category,$check_category)
    {
        foreach ($check_category as $category) {
            $category = (int) $category;
            if (!$category) {
                continue;
            }    
            $insert_fields = array (
                'id_categ_advertiser' => $category,
                'id_categ_merged' => $id_merged_category,
                );
            $this->db->insert('linkshare_categories_joins', $insert_fields);
            $insert_id[] = $this->db->insert_id();
        }
        return $insert_id;
    }
    
    /**
     * Get Creative for merge
     *
     * @param array $filters 
     *
     * @return array
     */
    function getCreativeForMerge($filters)
    {
        $this->load->model('site_model');
        $row = array();
        
    print '<pre>MODEL';
    print_r($filters);
    print '</pre>'; 
    
    
        if (isset($filters['limit'])) {
            $offset = (isset($filters['offset'])) ? $filters['offset'] : 0;
            $this->db->limit($filters['limit'], $offset);
        }
        if (isset($filters['id_site']))
            $this->db->where('id_site', $filters['id_site']);
        if (isset($filters['nume'])) {
            $filters['nume'] = str_replace("%2C", ",", $filters['nume']);
            $filters['nume'] = str_replace('%26', '&', $filters['nume']);
            $filters['nume'] = str_replace('+', ' ', $filters['nume']);
            $this->db->like('name', $filters['nume']);
        }
        if ($filters['mid'] != '') {
            $this->db->where('mid', $filters['mid']);
            $this->db->order_by('cat_id');
        } else {
            $this->db->order_by('id');
        }

        $result = $this->db->get('linkshare_categories_creative');
        if (isset($filters['name']))

        foreach ($result->result_array() as $linie) {
            
            if (isset($filters['name'])) {
                $site = $this->site_model->getSite($linie['id_site']);
                $linie['id_site'] = $site['name'];
            }
            
            $merge_categories = $this->category_creative_model->getCreativeMerged($linie['cat_id']);
            $linie['merge_categories'] = $merge_categories;

            $check_array = explode(',', $filters['check_category']);
            in_array($linie['cat_id'], $check_array, true) ? $linie['checked'] = 1 : $linie['checked'] = 0;            
            
            $row[] = $linie;

        }
        return $row;
    }
    
    function getCreativeMerged($id)
    {
        $row = array();
        $names = array();
        $this->db->where('id_categ_advertiser', $id);
        $this->db->join('linkshare_categories_merged','linkshare_categories_joins.id_categ_merged = linkshare_categories_merged.id','left');
        $result = $this->db->get('linkshare_categories_joins');

        foreach ($result->result_array() as $row) {
            
            $names[]=$row['name'];
            
        }
  
        return $names;
    }
    
    function getMergedCategoryByID($id)
    {  
        $this->db->where('ID', $id);
        $result = $this->db->get('linkshare_categories_merged');

        return $result->result_array();
    }
    
    function getMergedCategory()
    {  
        $result = $this->db->get('linkshare_categories_merged');

        return $result->result_array();
    }
   
    function getJoinsCategory($id)
    {
        $this->db->where('id_categ_merged', $id);
        //$this->db->join('linkshare_categories_creative','linkshare_categories_creative.cat_id = linkshare_categories_joins.id_categ_merged','left');
        $result = $this->db->get('linkshare_categories_joins');

        return $result->result_array();
    }
    
    function listMergedCategory()
    {  
        $id_merged_category = $this->getMergedCategory();

        foreach ($id_merged_category as $key=>$row) {
            $listMergedCategory = array();
            
            $categories = $this->getJoinsCategory($row['ID']);
            
            foreach ($categories as $categ) {
                $category_merged[] = $categ['id_categ_advertiser'];
            }

            $listMergedCategory['category_merged_ID'] = $row['ID'];
            $listMergedCategory['category_merged_name'] = $row['name'];
            $listMergedCategory['categories_merged'] = $category_merged;
            
            $result[] = $listMergedCategory;
            unset($category_merged);
        }

        return $result;
    }
    
    function updateMergedCategory($update_fields,$id) 
    {		
        $this->db->update('linkshare_categories_merged', $update_fields, array('ID' => $id));	
        return TRUE;
    }
    
    function deleteMergedCategory($id)
    {
        $this->db->delete('linkshare_categories_merged', array('id' => $id));
        return TRUE;
    }
    
    function deleteJoinCategory($MergedCategory_id, $JoinsCategory_id)
    {
        $this->db->delete('linkshare_categories_joins', array('id_categ_merged'=>$JoinsCategory_id,'id_categ_advertiser'=>$MergedCategory_id));
        return TRUE;
    }
}
