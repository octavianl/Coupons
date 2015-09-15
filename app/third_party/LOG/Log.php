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

use app\exceptions\LogFileCouldNotBeOpenedException;
use app\exceptions\LogFileCouldNotWriteException;

class Log
{
    const DEBUG = 1; // ...
    const INFO = 2; // ...
    const NOTICE = 3; // ...
    const WARN = 4; // ...
    const ERROR = 5; // Fatality
    const OFF = 6; // Nothing at all.

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
    public $logFormat = ":date :loglevel :log_message\n";
    private $logFile;
    private $level = self::INFO;
    private $newLines = array();
    private $fileHandle;
    private $delimiter = ',';
    
    //private $filepath = FCPATH . APPPATH . 'logs/';
    private $filename = 'general';
    private $ext = 'csv';

    private function __construct()
    {
        $this->logFile = $filepath;
        // $this->level = $logLevel;

        $this->checkFolder();
        $this->checkPermissions();
        return;
    }

    private function checkFolder()
    {
        $current_year = FCPATH . APPPATH . 'logs/' . date("Y");
        $current_month = FCPATH . APPPATH . 'logs/' . date("Y").'/'.date('m');

        if(!is_dir($current_year) || !is_dir($current_month)){
            
            throw new Exception("Folder for current month doesn't exist! Make new folder.");
            
            mkdir($current_month, 0777, true);
            
        }
    }
    
    private function checkPermissions()
    {
        if (file_exists($this->logFile) && !is_writable($this->logFile)) {
            throw new Exception(
                "The file exists, but could not be opened for writing." .
                " Check that appropriate permissions have been set."
            );
        }
    }

    public function setFileName($filename = null)
    {
        if (is_null($filename)) {
            return;
        }
        $this->filename = $this->filepath . $filename . '-' . date("D") . '.' . $this->ext;
    }
    
    public static function getInstance()
    {
        if (null == self::$loggerInstance) {
            self::$loggerInstance = new self();
        }
        return self::$loggerInstance;
    }

    // LEVEL OF LOGS
    public static function debug($line, $filename = null)
    {
        self::getInstance()->log($line, self::DEBUG);
    }
    
    public static function info($line, $filename = null)
    {
        self::getInstance()->log($line, self::INFO);
    }

    public static function notice($line, $filename = null)
    {
        self::setFileName($filename);
        self::getInstance()->log($line, self::NOTICE);
    }
    
    public static function warn($line, $filename = null)
    {
        self::getInstance()->log($line, self::WARN);
    }

    public static function error($line, $filename = null)
    {
        self::getInstance()->log($line, self::ERROR);
    }

    protected function log($line, $level)
    {
        //$fp = fopen($this->filename, 'w');
        try {
            $this->openFile();
        } catch (Exception $ex) {
            // scrii in db !!!
        }
        
                
        if (fputcsv($this->fileHandle, $line, $this->delimiter) === false) {
//            throw new LogFileCouldNotWriteException(
//                "The file could not be written to." .
//                " Check that appropriate permissions have been set."
//            );
            // scrii in db
        }
                
        $this->closeFile();
    }
   
    /**
     * Opens the file and gains exclusive lock 
     * 
     * @throws LogFileCouldNotBeOpenedException
     * @return boolean
     */
    private function openFile()
    {
        if ($this->fileHandle = fopen($this->logFile, "a")) {
            // Windows does not support flock's build in block mechanism
            if (stripos(PHP_OS, 'win') !== false) {
                do {
                    $canWrite = flock($this->fileHandle, LOCK_EX);
                    // If lock not obtained sleep for 0.5 millisecond, to avoid collision and CPU load
                    if (!$canWrite) {
                        usleep(500);
                    }
                } while (!$canWrite && ((microtime() - $startTime) < 100 * 1000));
            } else {
                $wouldblock = true;
                $canWrite = flock($this->fileHandle, LOCK_EX, $wouldblock);
            }

            return $canWrite;
        } else {
            throw new LogFileCouldNotBeOpenedException("File could not be opened:" . $this->logFile);
        }
    }

    private function closeFile()
    {
        fflush($this->fileHandle);
        flock($this->fileHandle, LOCK_UN);
        fclose($this->fileHandle);
    }

    /**
     * Saves new log messages to the log file
     * and clears local cache. 
     * 
     * @throws LogFileCouldNotWriteException
     */
    public function save()
    {        
        if ($this->openFile()) {
            foreach ($this->newLines as $arrNewLine) {
                if (fputcsv($this->fileHandle, $arrNewLine) === false) {
                    throw new LogFileCouldNotWriteException(
                        "The file could not be written to." .
                        " Check that appropriate permissions have been set."
                    );
                }
            }
            $this->newLines = array();
            $this->closeFile();
            return true;
        }
        return false;
    }

    protected function getLogLine($level, $message)
    {

        $arr = array(
            'date' => date($this->dateFormat),
            'loglevel' => $this->getLevelName($level),
            'message' => $message
        );

        return $arr;
    }

    protected function getLevelName($level)
    {
        $levelNames = array(
            self::DEBUG => 'DEBUG',
            self::INFO => 'INFO',
            self::NOTICE => 'NOTICE',
            self::WARN => 'WARN',            
            self::ERROR => 'ERROR'            
        );
        return isset($levelNames[$level]) ? $levelNames[$level] : 'LOG';
    }
}
