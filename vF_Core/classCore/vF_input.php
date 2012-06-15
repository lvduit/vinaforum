<?php 
vf_check();

# ----------------------
# Class: Input
# Author: Yplit
# Date: 13/6/2012
#-----------------------
class vF_input
{
	private static $_instance;
	public $vF;
	private $_method = array( 
		'_GET', '_POST', '_REQUEST', '_COOKIE', '_SESSION', '_SERVER', '_ENV'
	);
	public $getParamType = array(
		'g' => '_GET',
		'p' => '_POST', 
		'r' => '_REQUEST', 
		'c' => '_COOKIE', 
		's' => '_SESSION',
		'se' => '_SERVER', 
		'e' => '_ENV'
	);
	public $domain;
	public $host;
	public $path;
	public $siteUrl;
	public $serverName;

	// Cookie
	public $cookiePath;
	public $cookieDomain;
	public $cookiePrefix = 'vF_';
	public $cookieHash = true;
	
	// Session
	public $sessionSavePath = '/';
	
	public function __construct()
	{
		$this->vF = $GLOBALS['vF'];
	}

	public static function getInstance()
	{
		if( !self::$_instance )
		{
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function setServerName( $serverName )
	{
		$this->serverName = $serverName;
	}

	public function setPath( $path )
	{
		$this->path = $path;
	}

	public function setCookiePath( $cookiePath )
	{
		$this->cookiePath = $cookiePath;
	}

	public function setCookieDomain( $cookieDomain )
	{
		$this->cookieDomain = $cookieDomain;
	}

	public function setCookiePrefix( $cookiePrefix )
	{
		$this->cookiePrefix = $cookiePrefix;
	}

	public function allowCookieHash()
	{
		$this->cookieHash = true;
	}

	public function disableCookieHash()
	{
		$this->cookieHash = false;
	}

	public function addCookie( $cookieName, $cookieValue, $cookieExpire = 0, $cookiePath = '', $cookieDomain = '', $cookieSecure = true, $httpOnly = false )
	{
		if( empty( $cookieName ) ) return false;
		$cookieName = $this->cookiePrefix . $cookieName;

		if( $this->cookieHash )
		{
			$cookieValue = $this->hashCookieValue( $cookieValue );
		}

		$cookieExpire = intval( $cookieExpire );

		if( empty( $cookiePath ) and !empty( $this->cookiePath ) )
		{
			$cookiePath = $this->cookiePath;
		}

		if( empty( $cookieDomain ) and !empty( $this->cookieDomain ) )
		{
			$cookieDomain = $this->cookieDomain;
		}

		return $this->_setCookie( $cookieName, $cookieValue, $cookieExpire, $cookiePath, $cookieDomain, $cookieSecure, $httpOnly );
	}

	public function unsetCookie( $cookieName )
	{
		if( $this->issetCookie( $cookieName ) )
		{
			$this->_setCookie( $cookieName, '', $vF->system->time - 365*24*60*60 );
			//unset( $_COOKIE["$cookieName"] );
		}
		return true;
	}

	public function getCookie( $cookieName, $defalut = '' )
	{
		if( empty( $cookieName ) ) return ( !empty( $defalut ) ? $defalut : false );
		if( !$_COOKIE["$cookieName"] ) return ( !empty( $defalut ) ? $defalut : false );
		if( $this->cookieHash )
		{
			return $this->cookieDecode( $_COOKIE["$cookieName"] );
		}
		return $_COOKIE["$cookieName"];
	}

	private function _setCookie( $cookieName, $cookieValue, $cookieExpire = 0, $cookiePath = '', $cookieDomain = '', $cookieSecure = true, $httpOnly = false )
	{
		return setcookie( $cookieName, $cookieValue, $cookieExpire, $cookiePath, $cookieDomain, $cookieSecure, $httpOnly );
	}

	public function issetCookie( $cookieName )
	{
		if( !$_COOKIE ) return false;
		if( !$_COOKIE["$cookieName"] ) return false;
		if( empty( $_COOKIE["$cookieName"] ) ) return false;
		return true;
	}

	public function getStringParam( $keyName, $type = 'r', $default = '' )
	{
		return (string)$this->getParam( $keyName, $type, $default );
	}

	public function getBooleanParam( $keyName, $type = 'r', $default = '' )
	{
		return (boolean)$this->getParam( $keyName, $type, $default );
	}

	public function getIntParam( $keyName, $type = 'r', $default = '' )
	{
		return (int)$this->getParam( $keyName, $type, $default );
	}

	public function getArrayParam( $keyName, $type = 'r', $default = '' )
	{
		return (array)$this->getParam( $keyName, $type, $default );
	}

	public function getDate( $timeFormat, $time = null )
	{
		if( $time !== null )
		{
			return date( $timeFormat, intval( $time ) );
		}
		return date( $timeFormat );
	}

	public function getYear( $twoNumber = false, $time = null )
	{
		if( $time != null ) $time = vF_getVf::get('System')->time;
		$timeFormat = ( $twoNumber != false ? 'Y' : 'y' );

		return date( "$timeFormat", $time );
	}

	public function getParam( $keyName, $type = 'r', $default = '' )
	{
		if( empty( $keyName ) ) return false;
		// All type: r(request), p(post), g(get), s(session), c(cookie), se(server), e(env)
		if( !in_array( $type, array_keys( $this->getParamType ) ) )
		{
			// Default request
			$type = 'r'; 
		}
		switch( $type )
		{
			case 'c': return $this->getCookie( $keyName, $default );
			case 'p': return $this->getPost( $keyName, $default );
			case 'g': return $this->getGet( $keyName, $default );
			case 's': return $this->getSession( $keyName, $default );
			case 'se': return $this->getServer( $keyName, $default );
			case 'e': return $this->getEnv( $keyName, $default );
			case 'r': default: return $this->getRequest( $keyName, $default );
		}
		return false;
	}

	public function getGet( $keyName, $defaul = '' )
	{
	}
}