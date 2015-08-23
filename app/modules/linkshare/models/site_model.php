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

    function __construct()
    {
        parent::__construct();

        $this->CI = & get_instance();
    }

    /**
     * Get Sites
     *
     * @return array
     */
    function getSites()
    {
        $row = array();
        $result = $this->db->get('linkshare_site');
        foreach ($result->result_array() as $linie) {
            $row[] = $linie;
        }

        return $row;
    }

    /**
     * Get Site
     *
     * @param int $id	
     *
     * @return array
     */
    function getSite($id)
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
     * @param string $token	
     *
     * @return string
     */
    function getSiteByToken($token)
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
     * @param int $sid 
     *
     * @return string
     */
    function getSiteBySID($sid)
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
     * @param array $insert_fields	
     *
     * @return int $insert_id
     */
    function newSite($insert_fields)
    {
        $this->db->insert('linkshare_site', $insert_fields);
        $insert_id = $this->db->insert_id();

        return $insert_id;
    }

    /**
     * Update Site
     * 
     * @param array $update_fields
     * @param int $id	
     *
     * @return boolean true
     */
    function updateSite($update_fields, $id)
    {

        $this->db->update('linkshare_site', $update_fields, array('id' => $id));

        return true;
    }

    /**
     * Delete Site
     * 	
     * @param int $id	
     *
     * @return boolean true
     */
    function deleteSite($id)
    {

        $this->db->delete('linkshare_site', array('id' => $id));

        return true;
    }

}
