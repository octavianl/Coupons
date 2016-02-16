<?php

/**
 * Categorie Model
 *
 * @category Category_Creative_Model
 * @package  Linkshare
 * @author   Weblight <office@weblight.ro>
 * @license  License http://www.weblight.ro/
 * @link     http://www.weblight.ro/
 *
 */
class Category_model extends CI_Model
{
    private $CI;

    public function __construct()
    {
        parent::__construct();
        $this->CI = & get_instance();
    }

    /**
     * Get Categories
     *
     * @return array
     */
    public function getCategories()
    {
        $row = array();
        $result = $this->db->get('linkshare_categories');

        foreach ($result->result_array() as $linie) {
            $row[] = $linie;
        }

        return $row;
    }

    /**
     * Get Category Status
     *
     * @return array
     */
    public function getCategoryStatus()
    {
        $row = array();
        $result = $this->db->get('linkshare_categories');

        foreach ($result->result_array() as $value) {
            $row[$value['id']] = $value['name'];
        }

        return $row;
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
        $result = $this->db->get('linkshare_categories');

        foreach ($result->result_array() as $row) {
            return $row;
        }

        return $row;
    }

    /**
     * Get Category Name
     *
     * @param int $id Category id	
     *
     * @return array
     */
    public function getCategoryName($id)
    {
        $row = array();
        $this->db->where('id', $id);
        $result = $this->db->get('linkshare_categories');

        foreach ($result->result_array() as $row) {
            return $row['name'];
        }

        return $row;
    }

    /**
     * Create New Category
     *
     * @param array $insert_fields Category fields to insert	
     *
     * @return int $insert_id
     */
    public function newCategory($insert_fields)
    {
        $this->db->insert('linkshare_categories', $insert_fields);
        return $this->db->insert_id();       
    }

    /**
     * Update Category
     * 
     * @param array $update_fields Category fields to update
     * @param int   $id            Category identifier	
     *
     * @return boolean true
     */
    public function updateCategory($update_fields, $id)
    {
        $this->db->update('linkshare_categories', $update_fields, array('id' => $id));
        return true;
    }

    /**
     * Delete Category
     *
     * @param int $id Category identifier	
     *
     * @return boolean true
     */
    public function deleteCategory($id)
    {
        $this->db->delete('linkshare_categories', array('id' => $id));
        return true;
    }
    
    /**
     * Delete Categories
     * 	     
     *
     * @return boolean true
     */
    public function deleteCategories() 
    {
        $this->db->truncate('linkshare_categories');
        return true;
    }

    /**
     * Get Categories Parse
     *
     * @param array $filters
     * @return array
     */
    public function getCategoriesParse($filters)
    {
        if ($filters['limit']) {
            return array_slice($filters['categories'], $filters['offset'], $filters['limit']);
        }
        return $filters['categories'];
    }

}
