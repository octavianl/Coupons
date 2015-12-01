<?php

/**
 * Magazin Model - Manages magazine
 *
 * @category Admin Controller
 * @package  Linkshare
 * @author   Weblight <office@weblight.ro>
 * @license  License http://www.weblight.ro/
 * @link     http://www.weblight.ro/
 *
 */
class Advertiser_temp_model extends CI_Model 
{
    private $CI;

    public function __construct() 
    {
        parent::__construct();

        $this->CI = & get_instance();
    }
    
    /**
     * Get Temp Advertisers
     * array $filters The filters to search by
     *
     * @return array The advertisers in the temp table
     */
    public function getTempAdvertisers($filters = array()) 
    {
        $row = array();
        $order_dir = (isset($filters['sort_dir'])) ? $filters['sort_dir'] : 'ASC';

        $this->load->model('site_model');

        if (isset($filters['sort'])) {
            $this->db->order_by($filters['sort'], $order_dir);
        }

        if (isset($filters['limit'])) {
            $offset = (isset($filters['offset'])) ? $filters['offset'] : 0;
            $this->db->limit($filters['limit'], $offset);
        }

        if (isset($filters['id_site'])) {
            $this->db->where('id_site', $filters['id_site']);
        }

        if (isset($filters['id_categories'])) {
            $this->db->like('id_categories', $filters['id_categories']);
        }

        if (isset($filters['name_status'])) {
            $this->db->like('status', $filters['name_status']);
        }

        if (isset($filters['name'])) {
            $this->db->like('name', $filters['name']);
        }

        if (isset($filters['mid'])) {
            $this->db->where('mid', $filters['mid']);
        }

        if (isset($filters['id_status'])) {
            $this->db->where('id_status', $filters['id_status']);
        }
            
        $this->db->order_by('id');
        $result = $this->db->get('linkshare_advertisers_temp');

        foreach ($result->result_array() as $linie) {
            $site = $this->site_model->getSite($linie['id_site']);
            $linie['name_site'] = $site['name'];

            //$nr_products = $this->getCountProductsByMID($linie['mid'], $linie['id_site']);
            //$linie['nr_products'] = $nr_products;
            $row[] = $linie;
        }
        
        return $row;
    }

    /**
     * Get Temp Status Name
     *
     * @return array
     */
    public function getTempStatusName() 
    {
        $id = 0;
        $this->load->model('status_model');
        $this->db->select('id_status');
        $query = $this->db->get('linkshare_advertisers_temp');

        foreach ($query->result_array() as $name) {
            $id = $name['id_status'];
        }

        $statusName = $this->status_model->getStatusNameByID($id);

        return $statusName;
    }

    /**
     * Get Temp Status ID
     *
     * @return array
     */
    public function getTempStatusID()
    {
        $id = 0;
        $this->db->select('id_status');
        $query = $this->db->get('linkshare_advertisers_temp');
        
        foreach ($query->result_array() as $name) {
            $id = $name['id_status'];
        }
        
        return $id;
    }
    
    /**
     * Get Temp Advertiser By Mid
     *
     * @param int $mid Merchandiser identifier
     *
     * @return array
     */
    public function getTempAdvertiserByMID($mid) 
    {
        $row = array();
        $this->db->where('mid', $mid);
        $result = $this->db->get('linkshare_advertisers_temp');

        foreach ($result->result_array() as $row) {
            return $row;
        }

        return $row;
    }
    
    /**
     * Create New Temp Advertisers
     *
     * @param array $insert_fields Fields to insert
     *
     * @return int $insert_id The new temporary advertiser identifier
     */
    public function newTempAdvertiser($insert_fields) 
    {
        $this->db->insert('linkshare_advertisers_temp', $insert_fields);
        $insert_id = $this->db->insert_id();

        return $insert_id;
    }    


    /**
     * Update Advertisers from Temp
     * 
     * @param array $update_fields The fields to update
     * @param int   $mid           Merchandiser identifier
     * @param int   $id_site       Site identifier
     *
     * @return boolean true
     */
    public function updateAdvertiserFromTemp($update_fields, $mid, $id_site) 
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

        $this->db->update('linkshare_advertisers', $update_fields, array('mid' => $mid, 'id_site' => $id_site));

        return true;
    }
    
    /**
     * Check if merchandiser exists
     * 	
     * @param int $mid Merchendiser identifier
     * @param int $sid Site identifier
     *
     * @return boolean true
     */
    public function existsAdvertiser($mid, $sid) 
    {
        $this->db->where('mid', $mid);
        $this->db->where('id_site', $sid);
        
        $result = $this->db->get('linkshare_advertisers_temp');

        $row = array();
        foreach ($result->result_array() as $row) {
            return $row;
        }

        return $row;
    }

    /**
     * Delete temporary advertisers table
     *
     * @return boolean true
     */
    public function deleteTempAdvertiser() 
    {
        $this->db->truncate('linkshare_advertisers_temp');
        
        return true;
    }

    /**
     * Delete Temp Advertisers By MID	
     * 
     * @param int $mid Merchendiser identifier
     * @param int $sid Site identifier
     *
     * @return boolean true
     */
    public function deleteTempAdvertiserByMID($mid, $sid) 
    {
        $this->db->where('mid', $mid);
        $this->db->where('id_site', $sid);
        $this->db->delete('linkshare_advertisers_temp');

        return true;
    }        

}
