<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LogModel
 *
 * @author Webligh
 */

namespace app\third_party\LOG;

//use system\core\CI_Model;
//include_once 'system/core/Model.php';

class LogModel extends CI_Model 
{
    
    private $CI;

    function __construct() {
        parent::__construct();

        $this->CI = & get_instance();
    }
    
    /**
     * Add New Log Error
     *
     * @param array $insert_fields	
     *
     * @return int $insert_id
     */
    function logError($insert_fields) {

        $this->db->insert('linkshare_logs', $insert_fields);
        $insert_id = $this->db->insert_id();

        return $insert_id;
    }
}

