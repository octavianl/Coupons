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
    function get_logs($filters = array())
    {
        //Directory read
        $filedir = FCPATH.APPPATH.'logs/'; //'logs/logs.csv'

        if ($dirHandle = opendir($filedir)) {
        while (false !== ($filename = readdir($dirHandle))) {
                if (substr($filename, -4) == '.csv') {

                        //File read
                        $filepath = $filedir.$filename;
                        $row = 1;
                        if (($fileHandle = fopen($filepath, "r")) !== FALSE) {
                                    while (($data = fgetcsv($fileHandle, 1000, ",")) !== FALSE) {
                                        $num = count($data);
                                        $row++;

                                        $columns[] = array(
                                            array(
                                                'RowNo' => $row,
                                                'width' => '20%'),
                                            array(
                                                'DateTime' => $data[0],
                                                'width' => '30%'),
                                            array(
                                                'LogLevel' => $data[1],
                                                'width' => '20%'),
                                            array(
                                                'Message' => $data[2],
                                                'width' => '30%'
                                            )
                                        );

                        }
                        fclose($fileHandle);
                    }

                }
            }
            closedir($dirHandle);
        }
        

        $output = array_slice($columns, $filters['offset'],$filters['limit']);
        
        return $output;
        

    }


}
