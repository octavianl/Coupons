<?php

/**
 * Site Model - Manages sites
 *
 * @category Admin Controller
 * @package  Linkshare
 * @author   Weblight <office@weblight.ro>
 * @license  License http://www.weblight.ro/
 * @link     http://www.weblight.ro/
 *
 */

class Site_model extends CI_Model
{

    private $CI;

    public function __construct()
    {
        parent::__construct();
        $this->CI = & get_instance();
    }

    /**
     * Get Sites
     *
     * @return array
     */
    public function getSites()
    {
        $rows = array();
        $result = $this->db->get('linkshare_site');
        foreach ($result->result_array() as $linie) {
            $rows[] = $linie;
        }

        return $rows;
    }

    /**
     * Get Site
     *
     * @param int $id Site autoincrement id	
     *
     * @return array
     */
    public function getSite($id)
    {
        $row = array();
        $this->db->where('id', $id);
        $result = $this->db->get('linkshare_site');

        foreach ($result->result_array() as $row) {
            return $row;
        }

        return $row;
    }
    
    /**
     * Get Site by Token
     *
     * @param string $token The site token	
     *
     * @return string
     */
    public function getSiteByToken($token)
    {
        $row = array();
        $this->db->where('token', $token);
        $result = $this->db->get('linkshare_site');

        foreach ($result->result_array() as $row) {
            return $row;
        }

        return $row;
    }

    /**
     * Get Site by SID
     *
     * @param int $sid Site linkshare identifier 
     *
     * @return string
     */
    public function getSiteBySID($sid)
    {
        $row = array();
        $this->db->where('SID', $sid);
        $result = $this->db->get('linkshare_site');

        foreach ($result->result_array() as $row) {
            return $row;
        }

        return $row;
    }

    /**
     * Create New Site
     *
     * @param array $insert_fields The fields to insert	
     *
     * @return int
     */
    public function newSite($insert_fields)
    {
        $this->db->insert('linkshare_site', $insert_fields);
        return $this->db->insert_id();
    }

    /**
     * Update Site
     * 
     * @param array $update_fields The fields to update
     * @param int $id
     *
     * @return boolean true
     */
    public function updateSite($update_fields, $id)
    {
        $this->db->update('linkshare_site', $update_fields, array('id' => $id));
        return true;
    }

    /**
     * Delete Site
     * 	
     * @param int $id Site id	
     *
     * @return boolean true
     */
    public function deleteSite($id)
    {
        $this->db->delete('linkshare_site', array('id' => $id));
        return true;
    }
}
