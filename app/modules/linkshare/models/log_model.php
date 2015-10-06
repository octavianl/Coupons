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

    public function __construct()
    {
        parent::__construct();

        $this->CI = & get_instance();
    }

    /**
     * Get Logs from csv
     * @param $filters array()
     *
     * @return array
     */
    public function getLogs($filters = array())
    {

        //Directory read
        $filedir = FCPATH . APPPATH . 'logs/' . $filters['year'] . '/' . $filters['month'] . '/'; //'logs/logs.csv'

        if ($dirHandle = opendir($filedir)) {
            //while (false !== ($filename = readdir($dirHandle))) {
            $filename = $filters['zone'] . '-' . $filters['day'] . '-' . $filters['month'] . '-' . $filters['year'] . '.csv';
            $filepath = $filedir . $filename;

            if (file_exists($filepath)) {

                //File read
                $fileHandle = fopen($filepath, "r");
                if ($fileHandle != FALSE) {

                    $row = 0;
                    while (($data = fgetcsv($fileHandle, 1000, ",")) !== FALSE) {
                        if ($row == 0) {
                            $row++;
                        } else {
                            $num = count($data);

                            $columns[] = array(
                                array(
                                    'RowNo' => $row,
                                    'width' => '5%'),
                                array(
                                    'DateTime' => $data[0],
                                    'width' => '10%'),
                                array(
                                    'LogLevel' => $data[1],
                                    'width' => '5%'),
                                array(
                                    'FileName' => $data[2],
                                    'width' => '20%'
                                ),
                                array(
                                    'Line' => $data[3],
                                    'width' => '5%'
                                ),
                                array(
                                    'Class' => $data[4],
                                    'width' => '5%'
                                ),
                                array(
                                    'Method' => $data[5],
                                    'width' => '10%'
                                ),
                                array(
                                    'Message' => $data[6],
                                    'width' => '40%'
                                )
                            );
                            $row++;
                        }
                    }
                    fclose($fileHandle);
                } else {
                    $columns = array();
                }
            } else {
                $columns = array();
            }
            //}
            closedir($dirHandle);
        }

        $output = array_slice($columns, $filters['offset'], $filters['limit']);

        return $output;
    }

}
