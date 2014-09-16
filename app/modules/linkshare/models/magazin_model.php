<?php

/**
 * Magazin Model
 *
 * Manages magazine
 *
 * @author Weblight.ro
 * @copyright Weblight.ro
 * @package Save-Coupon

 */
class Magazin_model extends CI_Model {

    private $CI;

    function __construct()
    {
        parent::__construct();

        $this->CI = & get_instance();
    }

    /**
     * Get Magazine
     * array $filters
     *
     * @return array
     */
    function get_magazine($filters) {
        $row = array();
        if (isset($filters['limit'])) {
            $offset = (isset($filters['offset'])) ? $filters['offset'] : 0;
            $this->db->limit($filters['limit'], $offset);
        }
        if (isset($filters['id_site']))
            $this->db->where('id_site', $filters['id_site']);
        if (isset($filters['id_status']))
            $this->db->where('id_status', $filters['id_status']);
        $this->db->order_by('id');
        $result = $this->db->get('linkshare_magazin');
        if (isset($filters['name']))
            $this->load->model('site_model');

        foreach ($result->result_array() as $linie) {
            if (isset($filters['name'])) {
                $site = $this->site_model->get_site($linie['id_site']);
                $linie['name_site'] = $site['name'];
            }
            $nr_products = $this->get_count_produse_by_mid($linie['mid'], $linie['id_site']);
            $linie['nr_products'] = $nr_products;
            $row[] = $linie;
        }

        return $row;
    }

    /**
     * Get Magazin
     *
     * @param int $id
     * @param boolean $name 	
     *
     * @return array
     */
    function get_magazin($id, $name = FALSE) {
        $row = array();
        $this->db->where('id', $id);
        $result = $this->db->get('linkshare_magazin');

        if ($name)
            $this->load->model('site_model');

        foreach ($result->result_array() as $row) {
            if ($name) {
                $site = $this->site_model->get_site($row['id_site']);
                $row['id_site'] = $site['name'];
            }
            return $row;
        }

        return $row;
    }

    /**
     * Get Magazin By Mid
     *
     * @param int $mid
     * @param int $id_site	
     *
     * @return array
     */
    function get_magazin_by_mid($mid, $id_site) {
        $row = array();
        $this->db->where('mid', $mid);
        $this->db->where('id_site', $id_site);
        $result = $this->db->get('linkshare_magazin');

        foreach ($result->result_array() as $row) {
            return $row;
        }

        return $row;
    }

    /**
     * Get Count Produse By Mid
     *
     * @param int $mid
     * @param int $id_site
     * @param array $filters
     *
     * @return array
     */
    function get_count_produse_by_mid($mid, $id_site, $filters = array()) {
        /* $i = 0;
          $row = array(); */
        $this->db->where('mid', $mid);
        $this->db->where('id_site', $id_site);
        if (isset($filters['cat_creative_id']))
            $this->db->where('cat_creative_id', $filters['cat_creative_id']);
        if (isset($filters['cat_creative_name']))
            $this->db->where('cat_creative_name', $filters['cat_creative_name']);
        $result = $this->db->get('linkshare_produs');

        return $result->num_rows();

        //crapa memoria daca parcurg tot vectorul :)
        /* foreach ($result->result_array() as $row) {
          $i++;
          }

          return $i; */
    }

    /**
     * Create New Magazin
     *
     * Creates a new magazin
     *
     * @param array $insert_fields	
     *
     * @return int $insert_id
     */
    function new_magazin($insert_fields) {
        $this->db->insert('linkshare_magazin', $insert_fields);
        $insert_id = $this->db->insert_id();

        return $insert_id;
    }

    /**
     * Create New Magazine
     *
     * Creates new magazine
     *
     * @param array $mids
     *
     * @return int $count
     */
    function new_magazine($mids) {
        $cate = count($mids);
        for ($i = 0; $i < $cate; $i++) {
            $this->db->insert('linkshare_magazin', $mids[$i]);
        }
        return $cate;
    }

    /**
     * Update Magazin
     *
     * Updates magazin
     * 
     * @param array $update_fields
     * @param int $id	
     *
     * @return boolean TRUE
     */
    function update_magazin($update_fields, $id) {
        $this->db->update('linkshare_magazin', $update_fields, array('id' => $id));

        return TRUE;
    }

    /**
     * Delete Magazin
     *
     * Deletes magazin
     * 	
     * @param int $id	
     *
     * @return boolean TRUE
     */
    function delete_magazin($id) {

        $this->db->delete('linkshare_magazin', array('id' => $id));

        return TRUE;
    }

    function parse_magazin($params) {
        if ($params[0]['limit'])
            $limit = $params[0]['limit'];
        else
            $limit = 10;
        if ($params[0]['offset'])
            $offset = $params[0]['offset'];
        else
            $offset = 0;
        return array_slice($params, $offset, $limit, TRUE);
    }

    /**
     * Delete Magazin By Status	
     * @param int $id_site	
     * @param int $id_status	
     *
     * @return boolean TRUE
     */
    function delete_magazin_by_status($id_site, $id_status) {
        $this->db->query("DELETE FROM  linkshare_magazin WHERE id_site='$id_site' AND id_status='$id_status'");
        return TRUE;
    }

}
