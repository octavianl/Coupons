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

    function __construct()
    {
        parent::__construct();

        $this->CI = & get_instance();
    }

    /**
     * Get Categorii
     *
     * @return array
     */
    function get_categorii()
    {
        $row = array();
        $result = $this->db->get('linkshare_categories');

        foreach ($result->result_array() as $linie) {
            $row[] = $linie;
        }

        return $row;
    }

    /**
     * Get Categorie Status
     *
     * @return array
     */
    function getCategoryStatus()
    {
        $row = array();
        $result = $this->db->get('linkshare_categories');

        foreach ($result->result_array() as $value) {
            $row[$value['id']] = $value['name'];
        }

        return $row;
    }

    /**
     * Get Categorie
     *
     * @param int $id	
     *
     * @return array
     */
    function get_categorie($id)
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
        $result = $this->db->get('linkshare_categories');

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
        $this->db->insert('linkshare_categories', $insert_fields);
        $insert_id = $this->db->insert_id();

        return $insert_id;
    }

    /**
     * Update Categorie
     *
     * Updates categorie
     * 
     * @param array $update_fields
     * @param int   $id	
     *
     * @return boolean true
     */
    function updateCategory($update_fields, $id)
    {

        $this->db->update('linkshare_categories', $update_fields, array('id' => $id));

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

        $this->db->delete('linkshare_categories', array('id' => $id));

        return true;
    }

    /**
     * Get Categorii Parse
     *
     * @param array $filters
     * @return array
     */
    function getCategoriesParse($filters)
    {
        if ($filters['limit']) {
            return array_slice($filters['categories'], $filters['offset'], $filters['limit']);
        }
        return $filters['categories'];
    }

}
