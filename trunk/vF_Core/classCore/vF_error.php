<?php vF_Check();# ----------------------# Class: Error# Author: Yplit# Date: 13/6/2012#-----------------------class vF_error{	private static $_instance;	public $logDir;	public $errorDir;	public $vF;	public function __construct()	{		$this->vF = $GLOBALS['vF'];		$this->logDir = vF_DIR . '/' . vF_constant::vF_RESOURCE_DIR . '/' . vF_constant::vF_LOGS_DIR;		$this->errorDir = $this->logDir . '/' . vF_constant::vF_ERROR_DIR;	}	public static function getInstance()	{		if( !self::$_instance )		{			self::$_instance = new self();		}		return self::$_instance;	}	public function systemError( $errorStatus, $file = null, $line = null )	{		if( empty( $errorStatus ) ) exit();		if( $file !== null )		{			$file = str_replace( vF_DIR, '', $file );			$file = str_replace( '\\', '/', $file );		}		$errorFile = $this->errorDir . '/' . 'error_' . md5( substr( $errorStatus, 0, 5 ) . $this->vF->System->time ) . '.' . vF_constant::vF_LOGS_FILE_EXT;		$this->_logError( $errorFile, $errorStatus, $file, $line );		$this->_printScreen( $errorStatus );		return;	}	public function databaseError( $query, $sqlError )	{		if( $this->vF->System->debug )		{					}		$errorFile = $this->errorDir . '/' . 'error_' . md5( substr( $errorStatus, 0, 5 ) . $errorCode . time() ) . '.' . vF_constant::vF_LOGS_FILE_EXT;		$this->_logError( $errorFile, $errorStatus );	}	public function moduleError( $moduleName )	{		if( $this->vF->System->debug )		{			// Show infomation for Administrator!		}		$errorContent = strtr( '#MODULE#', $moduleName, vF_constant::vF_MODULE_COULD_NOT_LOAD_MODULE );		$this->_error404( $errorContent );	}	public function moduleOptionsError( $optionName )	{		if( $this->vF->System->debug )		{			// Show infomation for Administrator!		}		$errorContent = str_replace( '#OPTION#', $optionName, vF_constant::vF_MODULE_COULD_NOT_LOAD_OPTION );		$this->_error404( $errorContent );	}	private function _logError( $logFile, $logContent = '', $file = null, $line = null )	{		if( !$logFile ) return false;		if( file_exists( $logFile ) )		{			$newFileName = '';			$newFileID = 1;			while( true )			{				$newFileName = substr( $logFile, 0, ( strlen( $logFile ) - ( strlen( vF_constant::vF_LOGS_FILE_EXT ) + 1 ) ) ) . '-' . $newFileID . '.' . vF_constant::vF_LOGS_FILE_EXT;				if( !file_exists( $newFileName ) ) break;				$newFileID++;			}			$logFile = $newFileName;		}		$logContent = "Time: " . date( 'r' ) . "\n"			. "ERROR: " . $logContent . "\n"			. ( $file !== null ? "FILE: " . $file . "\n" : '' )			. ( $line !== null ? "LINE: " . $line . "\n" : '' );		error_log( $logContent, 3, $logFile );	}	private function _printScreen( $errorStatus = '' )	{		echo "<html><head><title>Forum Error!</title>\n";		echo "<style></style>\n";		echo "</head>\n";		echo "<body><div style=\"padding-top:100px;font-weight:bold; padding:100px;margin:50px;border:1px solid #C0C0C0;background:#E1E1E1; moz-border-radius:2px;border-radius:2px;-webkit-border-radius:2px;-o-border-radius:2px;color:#434343\">$errorStatus</div>\n";		echo "</body></html>";		exit;	}	private function _error404( $errorStatus = '' )	{		/*		$themesParams = array(			'errorContent' => $errorStatus		);		return vF_themes::getInstance()->loadTemplate(			'errorPage', $themesParams		);		*/		$this->_printScreen( $errorStatus );	}}