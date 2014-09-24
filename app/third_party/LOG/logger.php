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
	 *		$log = new Logger("log.txt", Logger::INFO);
	 *		$log->LogInfo("Returned a million search results");	//Prints to the log file
	 *		$log->LogFATAL("Oh dear."); //Prints to the log file
	 *		$log->LogDebug("x = 5"); //Prints nothing due to priority setting
	*/
error_reporting(E_ALL);
ini_set('display_errors', 'On');
	class Log
	{
		
		const DEBUG 	= 1;	// Most Verbose
		const INFO 		= 2;	// ...
		const WARN 		= 3;	// ...
		const ERROR 	= 4;	// ...
		const FATAL 	= 5;	// Least Verbose
		const OFF 		= 6;	// Nothing at all.
		
		const LOG_OPEN 		= 1;
		const OPEN_FAILED 	= 2;
		const LOG_CLOSED 	= 3;
		private static $loggerInstance;
		
		/**
		 * Date string. Default: "Y-m-d H:i:s"
		 * @var string 
		 */
		public $dateFormat	= "Y-m-d H:i:s";
		/**
		 * The format of the log line. Defaults to ":date :log_level :log_message\n"
		 * @var string  
		 */
		public $logFormat = ":date :log_level :log_message\n";
		
		private $_logFile;
		private $_level = self::INFO;
		
		private $_newLines = '';
		private $_fileHandle;
		
		private function __construct()
		{
			$filepath = FCPATH.APPPATH.'logs/logs.txt';

			$this->_logFile = $filepath;

			// $this->_level = $logLevel;
			
			$this->_CheckPermissions();
			return;
		}
		
		private function _CheckPermissions()
		{
			if (file_exists($this->_logFile) && !is_writable($this->_logFile))
			{
				throw new LogFileCouldNotWriteException("The file exists, but could not be opened for writing.".
					" Check that appropriate permissions have been set.");
			}
		}

		public static function getInstance() {
			if (null == self::$loggerInstance) {
				self::$loggerInstance = new self();
			}
			return self::$loggerInstance;
		}

		public static function info($line)
		{
			self::getInstance()->Log($line, self::INFO);
		}
		
		public static function debug($line)
		{
			self::getInstance()->Log($line, self::DEBUG);
		}
		
		public static function warn($line)
		{
			self::getInstance()->Log($line, self::WARN);	
		}
		
		public static function error($line)
		{
			self::getInstance()->Log($line, self::ERROR);		
		}

		public static function fatal($line)
		{
			self::getInstance()->Log($line, self::FATAL);
		}
		
		public static function Log($line, $level)
		{
			if (self::getInstance()->_level <= $level && $level != self::OFF)
			{
			   self::getInstance()->_newLines .= self::getInstance()->_GetLogLine($level, $line);
			}
		}

		public function __destruct()
		{
			$this->Save();
		}
		/**
		 * Opens the file and gains exclusive lock 
		 * 
		 * @throws LogFileCouldNotBeOpenedException
		 * @return boolean
		 */
		private function _OpenFile()
		{
			if ($this->_fileHandle = fopen($this->_logFile , "a"))
			{
				// Windows does not support flock's build in block mechanism
				if (stripos(PHP_OS, 'win')!== false) {
					do {
						$canWrite = flock($this->_fileHandle, LOCK_EX);
						// If lock not obtained sleep for 0.5 millisecond, to avoid collision and CPU load
						if(!$canWrite) usleep(500);
					} while (!$canWrite && ((microtime()-$startTime) < 100*1000));
				} else {
					$wouldblock = true;
					$canWrite = flock($this->_fileHandle, LOCK_EX, $wouldblock);
				}
		
				return $canWrite;
			}
			else
			{
				throw new LogFileCouldNotBeOpenedException("File could not be opened:". $this->_logFile);
			}
		}
		private function _CloseFile() {
			fflush($this->_fileHandle);
			flock($this->_fileHandle, LOCK_UN);
			fclose($this->_fileHandle);
		}
		
		/**
		 * Saves new log messages to the log file
		 * and clears local cache. 
		 * 
		 * @throws LogFileCouldNotWriteException
		 */
		public function Save()
		{
			if (empty($this->_newLines)) return false;
			
			if ($this->_OpenFile())
			{
			    if (fwrite($this->_fileHandle , $this->_newLines) === false)
			    {
			        throw new LogFileCouldNotWriteException("The file could not be written to.".
			        " Check that appropriate permissions have been set.");
			    }
			    $this->_newLines = '';
			    $this->_CloseFile();
			    return true;
			}
			return false;
		}
		
		protected function _GetLogLine($level, $message)
		{
			$time = date($this->dateFormat);
			$line = str_replace(':date', $time, $this->logFormat);
			$line = str_replace(':log_level', $this->_GetLevelName($level), $line);
			$line = str_replace(':log_message', $message, $line);
			return $line . PHP_EOL;
		}
		
		protected function _GetLevelName($level) {
			$levelNames = array(
					self::INFO=> 'INFO',
					self::WARN=> 'WARN',
					self::DEBUG=> 'DEBUG',
					self::ERROR=> 'ERROR',
					self::FATAL=> 'FATAL'
			);
			return isset($levelNames[$level])? $levelNames[$level] : 'LOG';
		}
	}
	
   class LogFileCouldNotWriteException extends \Exception {};
   class LogFileCouldNotBeOpenedException extends \Exception {};

?>