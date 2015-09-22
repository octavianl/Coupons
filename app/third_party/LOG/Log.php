<?php

/* Log class (Thread safe)
 * 
 * Author : Iskren Stoyanov <i.stoyanov@gmail.com>
 * Date	: Dec 29, 2012
 * Comments	: Inspired by http://codefury.net/projects/klogger/ 
 * Version	: 1.1
 *
 * Log messages are stored only when Logger::Save() method is called,
 * until then they are stored in the object it self.
 * Logger::Save() is called in the descruct method.
 *
 * Usage: 
 * 		$log = new Logger("log.txt", Logger::INFO);
 * 		$log->LogInfo("Returned a million search results");	//Prints to the log file
 * 		$log->LogFATAL("Oh dear."); //Prints to the log file
 * 		$log->LogDebug("x = 5"); //Prints nothing due to priority setting
 */
//error_reporting(E_ALL);
//ini_set('display_errors', 'On');

namespace app\third_party\LOG;

//use app\exceptions\LogFileCouldNotBeOpenedException;
//use app\exceptions\LogFileCouldNotWriteException;

class Log
{
    const DEBUG = 1; // Debug: debug messages
    const INFO = 2; // Informational: informational messages
    const NOTICE = 3; // Notice: normal but significant condition
    const WARN = 4; // Warning: warning conditions
    const ERROR = 5; // Error: error conditions
    const OFF = 6; // Logs OFF

    private static $loggerInstance;
    private static $logModel;
    

    /**
     * Date string. Default: "Y-m-d H:i:s"
     * @var string 
     */
    public $dateFormat = "Y-m-d H:i:s";

    /**
     * The format of the log line. Defaults to ":date :loglevel :log_message\n"
     * @var string  
     */
    public static $logHeader = array( 'Date', 'Level' , 'File', 'Line', 'Class', 'Method', 'Message');
    private $newLines = array();
    private static $fileHandle;
    private $delimiter = ',';

    private static $filepath;

    private $filename = 'general';
    private $filename_fullpath = '';
    private static $ext;


    private static function checkFolder()
    {

        $current_year = self::$filepath . date("Y");
        $current_month = self::$filepath . date("Y") . '/' . date('m');

        if(!is_dir($current_year) || !is_dir($current_month)){
            mkdir($current_month, 0777, true);            
        } else {
            // SCRII IN DB
            //throw new \Exception("Folder for current month doesn't exist! Making new folder.");
        }                
    }
    
    private static function checkPermissions($logFile)
    {
        if (file_exists($logFile) && !is_writable($logFile)) {
            return false;
        }else {
            return true;
        }
    }

    public function setFileName($filename = null)
    {
        self::init();
        
        $current_month = self::$filepath . date("Y") . '/' . date('m') . '/';
        
        if (is_null($filename)) {
            return;
        }

        $filename_fullpath = $current_month . $filename . '-' . date("d-m-Y") . '.' . self::$ext;
        
        return $filename_fullpath;
    }
    
    public static function getInstance()
    {
        if (null == self::$loggerInstance) {
            self::$loggerInstance = new self();            
        }
        
        return self::$loggerInstance;
    }
    
    public static function init()
    {
        self::$ext = 'csv';
        self::$filepath = FCPATH . APPPATH . 'logs/';
        self::checkFolder();
        
        //self::$logModel = new LogModel(); 
    }

    // LEVEL OF LOGS
    public static function debug($line, $filename = null)
    {
        self::getInstance()->log($line, $filename, self::DEBUG);
    }
    
    public static function info($line, $filename = null)
    {
        self::getInstance()->log($line, $filename, self::INFO);
    }

    public static function notice($line, $filename = null)
    {
        self::getInstance()->log($line, $filename, self::NOTICE);
    }
    
    public static function warn($line, $filename = null)
    {
        self::getInstance()->log($line, $filename, self::WARN);
    }

    public static function error($line, $filename = null)
    {
        self::getInstance()->log($line, $filename, self::ERROR);
    }
    
    public static function off($line, $filename = null)
    {
        self::getInstance()->log($line, $filename, self::OFF);
    }

    /**
     * Log method
     * 
     * @param $line array with variables from log line
     * @return boolean  
     */
    
    protected function log($line, $filename, $level)
    {
        $filename_fullpath = self::setFileName($filename);
        self::getInstance()->newLines = array_merge(self::getInstance()->getLogLine($level), $line);
        
        // Check if logs are not OFF mode
        if ($level != self::OFF) {
            
            // Check if logs file opens
            try {
                self::openFile($filename_fullpath);
            } catch (Exception $ex) {
                
//                $insert_fields = array(
//                    'log_message'   => "File can't be opened",
//                    'log_errorline' => __LINE__,
//                    'log_filename'  => $filename_fullpath,
//                );
//                self::$logModel->logError($insert_fields);
                
            }
            
            // Check if logs file exists and can be writable
            try {
                self::checkPermissions($filename_fullpath);
            } catch (Exception $ex) {
                //scriu in db !!!
            }
            
            // Save content in log file
            try {
                self::save();
            } catch (Exception $ex) {
                //scriu in db !!!
            }
    
//            echo "<pre>";
//            print_r(self::getInstance()->newLines);
//            echo "</pre>";
//            print_r($filename_fullpath);
            
        }else {
            echo "off";
        }
    }
   
    /**
     * Opens the file for writing 
     * 
     * @return boolean
     */
    private function openFile($filename_fullpath)
    {
        if (!file_exists($filename_fullpath)) {
            self::$fileHandle = fopen($filename_fullpath, 'w');
            fputcsv(self::$fileHandle, self::$logHeader); 
        }else{
            self::$fileHandle = fopen($filename_fullpath, 'a');
        }
        
        if (self::$fileHandle) {
            return true;
        } else {
            return false;
        }
    }

    private function closeFile()
    {
        fflush(self::$fileHandle);
        flock(self::$fileHandle, LOCK_UN);
        fclose(self::$fileHandle);
    }

/**
     * Saves new log messages to the log file
     * and clears local cache. 
     * 
     * @throws LogFileCouldNotWriteException
     */
    public function save()
    {
        if (empty(self::getInstance()->newLines)) {
            return false;
        }
        
        if (self::$fileHandle) {
            if (fputcsv(self::$fileHandle, self::getInstance()->newLines) === false) {
                //scriu in db !!!
            }
            //print_r($arrNewLine);
            $this->newLines = array();
            $this->closeFile();
            return true;
        }
        return false;
    }

    protected function getLogLine($level)
    {

        $levelNames = array(
            self::DEBUG => 'DEBUG',
            self::INFO => 'INFO',
            self::NOTICE => 'NOTICE',
            self::WARN => 'WARN',            
            self::ERROR => 'ERROR'            
        );
        
        $arr = array(
            date($this->dateFormat),
            $levelNames[$level]
        );
        
        return $arr;
    }

}

//class LogModel extends CI_Model {
//    
//    private $CI;
//
//    function __construct() {
//        parent::__construct();
//
//        $this->CI = & get_instance();
//    }
//    
//    /**
//     * Add New Log Error
//     *
//     * @param array $insert_fields	
//     *
//     * @return int $insert_id
//     */
//    function logError($insert_fields) {
//
//        $this->db->insert('linkshare_logs', $insert_fields);
//        $insert_id = $this->db->insert_id();
//
//        return $insert_id;
//    }
//}
