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

    const DEBUG = 1; // Most Verbose
    const INFO = 2; // ...
    const WARN = 3; // ...
    const ERROR = 4; // ...
    const FATAL = 5; // Least Verbose
    const OFF = 6; // Nothing at all.
    const LOG_OPEN = 1;
    const OPEN_FAILED = 2;
    const LOG_CLOSED = 3;

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

    private function __construct()
    {
        $filepath = FCPATH . APPPATH . 'logs/logs.csv';

        $this->logFile = $filepath;

        // $this->level = $logLevel;

        $this->checkPermissions();
        return;
    }

    private function checkPermissions()
    {
        if (file_exists($this->logFile) && !is_writable($this->logFile)) {
            throw new LogFileCouldNotWriteException(
                "The file exists, but could not be opened for writing." .
                " Check that appropriate permissions have been set."
            );
        }
    }

    public static function getInstance()
    {
        if (null == self::$loggerInstance) {
            self::$loggerInstance = new self();
        }
        return self::$loggerInstance;
    }

    public static function info($line)
    {
        self::getInstance()->log($line, self::INFO);
    }

    public static function debug($line)
    {
        self::getInstance()->log($line, self::DEBUG);
    }

    public static function warn($line)
    {
        self::getInstance()->log($line, self::WARN);
    }

    public static function error($line)
    {
        self::getInstance()->log($line, self::ERROR);
    }

    public static function fatal($line)
    {
        self::getInstance()->log($line, self::FATAL);
    }

    protected function log($line, $level)
    {
        if (self::getInstance()->level <= $level && $level != self::OFF) {
            self::getInstance()->newLines[] = self::getInstance()->getLogLine($level, $line);
        }
    }

    public function __destruct()
    {
        $this->save();
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
        if (empty($this->newLines)) {
            return false;
        }
        
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
            self::INFO => 'INFO',
            self::WARN => 'WARN',
            self::DEBUG => 'DEBUG',
            self::ERROR => 'ERROR',
            self::FATAL => 'FATAL'
        );
        return isset($levelNames[$level]) ? $levelNames[$level] : 'LOG';
    }
}
