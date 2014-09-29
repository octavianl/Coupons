<?php

/**
 * Categorie Creative Model
 *
 * @category Categorie_Creative_Model
 * @package  Linkshare
 * @author   Weblight <office@weblight.ro>
 * @license  License http://www.weblight.ro/
 * @link     http://www.weblight.ro/
 *
 */
class Categorie_creative_model extends CI_Model
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
    function get_categorii($filters)
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

        $result = $this->db->get('linkshare_categorie_creative');
        if (isset($filters['name']))
            $this->load->model('site_model');

        foreach ($result->result_array() as $linie) {
            if (isset($filters['name'])) {
                $site = $this->site_model->get_site($linie['id_site']);
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
    function get_categorii_linii($filters)
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

        $result = $this->db->get('linkshare_categorie_creative');

        return $result->num_rows();
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
        $result = $this->db->get('linkshare_categorie_creative');

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
    function get_categorie_name($id)
    {
        $row = array();
        $this->db->where('id', $id);
        $result = $this->db->get('linkshare_categorie_creative');

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
    function new_categorie($insert_fields)
    {
        $this->db->insert('linkshare_categorie_creative', $insert_fields);
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
    function delete_categorie_by_mid($id_site, $mid)
    {
        $this->db->delete('linkshare_categorie_creative', array('id_site' => $id_site, 'mid' => $mid));

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
    function update_categorie($update_fields, $id)
    {
        $this->db->update('linkshare_categorie_creative', $update_fields, array('id' => $id));

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
        $this->db->delete('linkshare_categorie_creative', array('id' => $id));

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
    function get_categorii_parse($filters)
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

}
