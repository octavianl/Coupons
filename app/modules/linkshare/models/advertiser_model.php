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
     * Get advertisers
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
            $this->db->where_not_in('retry', array(0));
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
     * Get Advertiser
     *
     * @param int $id Advertiser id
     *
     * @return array
     */
    public function getAdvertiser($id) 
    {
        $row = array();
        $this->db->where('id', $id);
        $result = $this->db->get('linkshare_advertisers');
        
        foreach ($result->result_array() as $row) {
            return $row;
        }

        return $row;
    }    
    
    /**
     * Create New Advertiser
     *
     * @param array $insert_fields The advertiser fields	
     *
     * @return int The new advertiser identifier
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
     * Update Advertiser
     *
     * @param array $update_fields The advertiser fields to update
     * @param int   $id             The advertiser id
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
     * 0 - not parsed, 1 - parsed, 2 - xml error
     * 
     * @param int $pcc   The parsed creative category flag
     * @param int $mid   The merchandiser linskhare id
     * @param int $retry The number of retries 	
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
     * 0 - not parsed, 1 - parsed, 2 - xml error
     *      
     * @param int $site_id Site id
     *
     * @return boolean true
     */
    public function resetPCC($site_id) 
    {
        $data = array(
            'pcc' => 0
        );
        $this->db->update('linkshare_advertisers', $data, array('id_site' => $site_id));

        return true;
    }

    /**
     * Check advertisers where PCC flag is 2 which means parsing error
     * PCC = Parsed Creative Category
     * 0 - not parsed, 1 - parsed, 2 - xml error
     *      
     * @param int $sid Site id
     *
     * @return int The number of advertisers with problems found
     */
    public function checkPCC($sid) 
    {
        $this->db->where('pcc', 2);
        $this->db->where('id_site', $sid);
        
        $result = $this->db->get('linkshare_advertisers');
        
        return $result->num_rows();
    }
    
    /**
     * Delete Advertiser
     * 	
     * @param int $id The advertiser identifier	
     *
     * @return boolean
     */
    public function deleteAdvertiser($id) 
    {
        $this->db->delete('linkshare_advertisers', array('id' => $id));

        return true;
    }
    
    /**
     * Parse Advertiser
     * 	
     * @param array $params The params to parse advertisers by	
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
     * Delete Advertiser By Status	
     * 
     * @param int $id_site   Site identifier
     * @param int $id_status The status of advertiser
     *
     * @return boolean true
     */
    public function deleteAdvertiserByStatus($id_site, $id_status) 
    {
        $this->db->query("DELETE FROM linkshare_advertisers WHERE id_site='$id_site' AND id_status='$id_status'");
        return true;
    }

    /**
     * Delete Advertisers By MID	
     * 
     * @param int $mid Merchandiser identifier
     * @param int $sid Site identifier
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
}
