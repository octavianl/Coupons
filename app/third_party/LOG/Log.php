<?php

/**
 *  Log class (Thread safe)
 *
 */
//error_reporting(E_ALL);
//ini_set('display_errors', 'On');

namespace app\third_party\LOG;

include APPPATH . 'third_party/LOG/LogModel.php';


class Log {

    const DEBUG = 1; // Debug: debug messages
    const INFO = 2; // Informational: informational messages
    const NOTICE = 3; // Notice: normal but significant condition
    const WARN = 4; // Warning: warning conditions
    const ERROR = 5; // Error: error conditions
    
    const CATEGORIES = 'categories'; // Zone categories
    const ADVERTISERS = 'advertisers'; // Zone categories
    const PRODUCTS = 'products'; // Zone categories
    const EXPORT = 'export'; // Zone categories

    private static $loggerInstance;

    /**
     * Date string. Default: "Y-m-d H:i:s"
     * @var string 
     */
    public $dateFormat = "Y-m-d H:i:s";

    /**
     * The format of the log line. Defaults to ":date :loglevel :log_message\n"
     * @var string  
     */
    public static $logHeader = array('Date', 'Level', 'File', 'Line', 'Class', 'Method', 'Message');
    private $newLines = array();
    private static $fileHandle;
    private $delimiter = ',';
    private static $filepath;
    private $filename = 'general';
    private $filename_fullpath = '';
    private static $ext;

    private static function checkFolder() {

        $current_year = self::$filepath . date("Y");
        $current_month = self::$filepath . date("Y") . '/' . date('m');

        if (!is_dir($current_year) || !is_dir($current_month)) {
            if (!mkdir($current_month, 0777, true)){
                $this->writeDB("Failed to create folder", __LINE__, $current_month);
            }
        } else {
            return true;
        }
    }

    public function setFileName($filename = null) {
        self::init();

        $current_month = self::$filepath . date("Y") . '/' . date('m') . '/';

        if (is_null($filename)) {
            return;
        }

        $filename_fullpath = $current_month . $filename . '-' . date("d-m-Y") . '.' . self::$ext;

        return $filename_fullpath;
    }

    public static function getInstance() {
        if (null == self::$loggerInstance) {
            self::$loggerInstance = new self();
        }

        return self::$loggerInstance;
    }

    public static function init() {
        self::$ext = 'csv';
        self::$filepath = FCPATH . APPPATH . 'logs/';
        self::checkFolder();

        //self::$logModel = new LogModel(); 
    }

    // LEVEL OF LOGS
    public static function debug($line, $filename = null) {
        self::getInstance()->log($line, $filename, self::DEBUG);
    }

    public static function info($line, $filename = null) {
        self::getInstance()->log($line, $filename, self::INFO);
    }

    public static function notice($line, $filename = null) {
        self::getInstance()->log($line, $filename, self::NOTICE);
    }

    public static function warn($line, $filename = null) {
        self::getInstance()->log($line, $filename, self::WARN);
    }

    public static function error($line, $filename = null) {
        self::getInstance()->log($line, $filename, self::ERROR);
    }

    /**
     * Log method
     * 
     * @param $line array with variables from log line
     * @return boolean  
     */
    protected function log($line, $filename, $level) {
        $filename_fullpath = self::setFileName($filename);
        self::getInstance()->newLines = array_merge(self::getInstance()->getLogLine($level), $line);

        // Open file and check if can be writable
        self::openFile($filename_fullpath);

        // Save content in log file
        self::save($filename_fullpath);

    }

    /**
     * Opens the file for writing 
     * 
     * @return boolean
     */
    private function openFile($filename_fullpath) {
        if (!file_exists($filename_fullpath)) {
            self::$fileHandle = fopen($filename_fullpath, 'w');
            fputcsv(self::$fileHandle, self::$logHeader);
        } else {
            self::$fileHandle = fopen($filename_fullpath, 'a');
        }

        if (!is_writable($filename_fullpath)) {
            $this->writeDB("File can't be opened", __LINE__, $filename_fullpath);
        }
    }

    private function closeFile() {
        fflush(self::$fileHandle);
        flock(self::$fileHandle, LOCK_UN);
        fclose(self::$fileHandle);
    }

    /**
     * Saves new log messages to the log file
     * 
     * @throws LogFileCouldNotWriteException
     */
    public function save($filename_fullpath) {
        if (empty(self::getInstance()->newLines)) {
            return false;
        }

        if (self::$fileHandle) {
            if (fputcsv(self::$fileHandle, self::getInstance()->newLines) === false) {
                $this->writeDB("Can't write into file", __LINE__, $filename_fullpath);
            }
            //print_r($arrNewLine);
            $this->newLines = array();
            $this->closeFile();
            return true;
        }
        return false;
    }

    protected function getLogLine($level) {

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
    
    private function writeDB($log_message, $log_errorline, $log_filename) {
        $logModel = new \LogModel(); 

        $insert_fields = array(
            'log_message'   => $log_message,
            'log_errorline' => $log_errorline,
            'log_filename'  => $log_filename,
        );

        $logModel->logError($insert_fields); 
    }

}
