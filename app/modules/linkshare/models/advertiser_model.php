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
class Advertiser_model extends CI_Model 
{
    private $CI;

    public function __construct() 
    {
        parent::__construct();

        $this->CI = & get_instance();
    }

    /**
     * Get Temp Advertisers
     * 
     * @param array $filters The filters to search by
     *
     * @return array The advertisers
     */
    public function getAdvertisers($filters = array()) 
    {
        $row = array();
        $order_dir = (isset($filters['sort_dir'])) ? $filters['sort_dir'] : 'ASC';

        $this->load->model('site_model');
        $this->load->model('product_model');

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

        if (isset($filters['pcc'])) {
            $this->db->where('pcc', $filters['pcc']);
        }
        if (isset($filters['retry'])) {            
            $this->db->order_by('retry', 'desc');
        }

        if (isset($filters['id_status'])) {
            $this->db->where('id_status', $filters['id_status']);
            $this->db->order_by('id');
        }

        $result = $this->db->get('linkshare_advertisers');

        foreach ($result->result_array() as $linie) {

            $site = $this->site_model->getSite($linie['id_site']);
            $linie['name_site'] = $site['name'];

            $nr_products = $this->product_model->getCountProductsByMID($linie['mid'], $linie['id_site']);
            $linie['nr_products'] = $nr_products;
            $row[] = $linie;
        }

        return $row;
    }

    /**
     * Get Advertisers
     *
     * @param int $id
     * @param boolean $name 	
     *
     * @return array
     */
    public function getAdvertiser($id, $name = false) 
    {
        $row = array();
        $this->db->where('id', $id);
        $result = $this->db->get('linkshare_advertisers');

        if ($name)
            $this->load->model('site_model');

        foreach ($result->result_array() as $row) {
            if ($name) {
                $site = $this->site_model->getSite($row['id_site']);
                $row['id_site'] = $site['name'];
            }
            return $row;
        }

        return $row;
    }    
    
    /**
     * Create New Advertisers
     *
     * @param array $insert_fields	
     *
     * @return int $insert_id
     */
    public function newAdvertiser($insert_fields) 
    {
        if (array_key_exists('id', $insert_fields)) {
            unset($insert_fields['id']);
        }

        if (array_key_exists('name_site', $insert_fields)) {
            unset($insert_fields['name_site']);
        }

        $this->db->insert('linkshare_advertisers', $insert_fields);
        $insert_id = $this->db->insert_id();

        return $insert_id;
    }
    
    /**
     * Create New Advertisers
     *
     * @param array $mids
     *
     * @return int $count
     */
    public function newAdvertisers($mids) 
    {
        $cate = count($mids);
        
        for ($i = 0; $i < $cate; $i++) {
            $this->db->insert('linkshare_advertisers', $mids[$i]);
        }
        
        return $cate;
    }

    /**
     * Update Advertisers
     *
     * @param array $update_fields
     * @param int $id	
     *
     * @return boolean true
     */
    public function updateAdvertiser($update_fields, $id) 
    {
        $this->db->update('linkshare_advertisers', $update_fields, array('id' => $id));

        return true;
    }

    /**
     * Change PCC flag 
     * PCC = Parsed Creative Category
     * 
     * @param array $update_fields
     * @param int $id	
     *
     * @return boolean true
     */
    public function changePCC($pcc, $mid, $site_id, $retry = 2) 
    {
        $data = array(
            'pcc' => $pcc,
            'retry' => $retry
        );
        $this->db->update('linkshare_advertisers', $data, array('mid' => $mid, 'id_site' => $site_id));

        return true;
    }
    
    /**
     * Reset PCC flag
     * PCC = Parsed Creative Category
     * 
     * @param int $pcc flag of parsed creative category
     * @param int $site_id site id
     *
     * @return boolean true
     */
    public function resetPCC($pcc, $site_id) 
    {
        $data = array(
            'pcc' => $pcc,
        );
        $this->db->update('linkshare_advertisers', $data, array('id_site' => $site_id));

        return true;
    }

    /**
     * Check PCC flag
     * PCC = Parsed Creative Category
     * 
     * @param int $pcc
     * @param int $sid site id
     *
     * @return boolean true
     */
    public function checkPCC($pcc, $sid) 
    {
        $this->db->where('pcc', $pcc);
        $this->db->where('id_site', $sid);
        
        $result = $this->db->get('linkshare_advertisers');
        
        return $result->num_rows();
    }
    
    /**
     * Delete Advertisers
     * 	
     * @param int $id	
     *
     * @return boolean true
     */
    public function deleteAdvertiser($id) 
    {
        $this->db->delete('linkshare_advertisers', array('id' => $id));

        return true;
    }

    /**
     * Delete Temporary Advertisers
     * 		
     *
     * @return boolean true
     */
    public function deleteTempAdvertiser() 
    {
        $this->db->truncate('linkshare_advertisers_temp');
        
        return true;
    }
    
    /**
     * Parse Advertiser
     * 	
     * @param array $params	
     *
     * @return boolean true
     */

    public function parseAdvertiser($params) 
    {
        if (isset($params[0]['limit'])) {
            $limit = $params[0]['limit'];
        }            
        else {
            $limit = 10;
        }
            
        if (isset($params[0]['offset'])) {
            $offset = $params[0]['offset'];
        }  else {
            $offset = 0;
        }
            
        return array_slice($params, $offset, $limit, true);
    }

    /**
     * Check Advertiser
     * 	
     * @param int $mid merchendiser id	
     * @param int $sid site id
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
     * Delete Advertiser By Status	
     * 
     * @param int $id_site site id
     * @param int $id_status the status of Advertisers
     *
     * @return boolean true
     */
    public function deleteAdvertiserByStatus($id_site, $id_status) 
    {
        $this->db->query("DELETE FROM  linkshare_advertisers WHERE id_site='$id_site' AND id_status='$id_status'");

        return true;
    }

    /**
     * Delete Advertisers By MID	
     * 
     * @param int $mid merchendiser id
     * @param int $sid site id	
     *
     * @return boolean true
     */
    public function deleteAdvertiserByMID($mid, $sid) 
    {
        $this->db->where('mid', $mid);
        $this->db->where('id_site', $sid);
        $this->db->delete('linkshare_advertisers');
        
        return true;
    }
    
    /**
     * Get the number of Advertisers	
     * 
     * @param array $filters to search the Advertisers by	
     *
     * @return boolean
     */

    public function get_num_rows($filters = array()) 
    {
        if (isset($filters['id_categories'])) {
            $this->db->like('id_categories', $filters['id_categories']);
        }

        if (isset($filters['name'])) {
            $this->db->like('name', $filters['name']);
        }

        if (isset($filters['mid'])) {
            $this->db->where('mid', $filters['mid']);
        }

        if (isset($filters['id_site'])) {
            $this->db->where('id_site', $filters['id_site']);
        }

        $result = $this->db->get('linkshare_advertisers');
        
        return $result->num_rows();
    }

}
