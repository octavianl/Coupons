<?php

/**
 * Log Model - Manages logs
 *
 * @category Admin Controller
 * @package  Linkshare
 * @author   Weblight <office@weblight.ro>
 * @license  License http://www.weblight.ro/
 * @link     http://www.weblight.ro/
 *
 */

class Log_model extends CI_Model
{

    private $CI;

    function __construct()
    {
        parent::__construct();

        $this->CI = & get_instance();
    }

    /**
     * Get Logs from csv
     *
     *
     * @return array
     */
    function get_logs()
    {
        $row = 1;
        $filepath = FCPATH.APPPATH.'logs/logs.csv';

        if (($handle = fopen($filepath, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $num = count($data);
                $row++;

                $columns[] = array(
                    array(
                        'Row No' => $row,
                        'width' => '20%'),
                    array(
                        'Date & Time' => $data[0],
                        'width' => '30%'),
                    array(
                        'Log Level' => $data[1],
                        'width' => '20%'),
                    array(
                        'Message' => $data[2],
                        'width' => '30%'
                    )
                );

            }
            fclose($handle);
        }

        return $columns;

    }


}
