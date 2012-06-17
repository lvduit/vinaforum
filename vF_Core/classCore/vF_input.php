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
	private $_method = array( '_GET', '_POST', '_REQUEST', '_COOKIE', '_SESSION', '_SERVER', '_ENV', '_FILES' );
	public $functionDisable = array( 'base64_decode', 'cmd', 'passthru', 'eval', 'exec', 'system', 'fopen', 'fsockopen', 'file', 'file_get_contents', 'readfile', 'unlink' );
	public $getParamType = array(
		'g' => '_GET',
		'p' => '_POST', 
		'r' => '_REQUEST', 
		'c' => '_COOKIE', 
		's' => '_SESSION',
		'se' => '_SERVER', 
		'e' => '_ENV',
		'f' => '_FILES',
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
		//$this->vF = $GLOBALS['vF'];
		$this->cookieDomain = $this->_getCookieDomain();
		$this->cookiePath = $this->_getCookiePath();
		$this->cookiePrefix = ( isset( $GLOBALS['vF_Config']->cookiePrefix ) ? $GLOBALS['vF_Config']->cookiePrefix : 'vF_' );
		$this->sessionPath = $this->_getSessionPath();
	}

	public static function getInstance()
	{
		if( !self::$_instance )
		{
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function _getCookieDomain()
	{
		$this->serverName = $_SERVER['SERVER_NAME'];
		$cookieDomain = preg_replace( "/^([w]{3})\./", "", $this->serverName );
		$cookieDomain = ( preg_match( "/^([0-9a-z][0-9a-z-]+\.)+[a-z]{2,6}$/", $cookieDomain ) ) ? '.' . $cookieDomain : '';
		return $cookieDomain;
	}

	private function _getCookiePath()
	{
		return $this->_getForumPath() . '/';
	}

	public function _getForumPath()
	{
		$baseSiteUrl = pathinfo( $_SERVER['PHP_SELF'], PATHINFO_DIRNAME );
		if ( $baseSiteUrl == DIRECTORY_SEPARATOR ) return '';

		if ( ! empty( $baseSiteUrl ) ) $baseSiteUrl = str_replace( DIRECTORY_SEPARATOR, '/', $baseSiteUrl );
		if ( ! empty( $baseSiteUrl ) ) $baseSiteUrl = preg_replace( "/[\/]+$/", '', $baseSiteUrl );
		if ( ! empty( $baseSiteUrl ) ) $baseSiteUrl = preg_replace( "/^[\/]*(.*)$/", '/\\1', $baseSiteUrl );
		if ( ! empty( $baseSiteUrl ) ) $baseSiteUrl = preg_replace( "#/index\.php(.*)$#", '', $baseSiteUrl );

		return $baseSiteUrl;
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
		$cookieName = $this->cookiePrefix . $cookieName;

		if( $this->issetCookie( $cookieName ) )
		{
			$this->_setCookie( $cookieName, '', $GLOBALS['vF']->system->time - 365*24*60*60 );
			unset( $_COOKIE["$cookieName"] );
		}
		return true;
	}

	public function getCookie( $cookieName, $defalut = '' )
	{
		if( empty( $cookieName ) ) return ( !empty( $defalut ) ? $defalut : false );

		$cookieName = $this->cookiePrefix . $cookieName;

		if( !isset( $_COOKIE["$cookieName"] ) ) return ( !empty( $defalut ) ? ( $defalut ) : false );
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
			$cookieName = $this->cookiePrefix . $cookieName;
		if( !isset( $_COOKIE["$cookieName"] ) ) return false;
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

	public function getGet( $keyName, $default = '' )
	{
		if( !isset( $_GET["$keyName"] ) ) return( !empty( $default ) ? $default : false );

		return $this->_getSecurity( $_GET["$keyName"] );
	}

	public function getPost( $keyName, $default = '' )
	{
		if( !isset( $_POST["$keyName"] ) ) return( !empty( $default ) ? $default : false );

		return $this->_postSecurity( $_POST["$keyName"] );
	}

	private function _getSecurity( $value )
	{
		if( !empty( $value ) and !is_numeric( $value ) )
		{
			$value = str_replace( array( "\t", "\r", "\n", "../" ), "", $value );
			$value = $this->_unHtmlentities( $value );
			unset( $matches );
			preg_match_all( '/<!\[cdata\[(.*?)\]\]>/is', $value, $matches );
			$value = str_replace( $matches[0], $matches[1], $value );
			$value = strip_tags( $value );
			$value = preg_replace( '#(' . implode( '|', $this->functionDisable ) . ')(\s*)\((.*?)\)#si', "", $value );
			$value = str_replace( array( '\'', '"', '<', '>' ), array( "&#039;", "&quot;", "&lt;", "&gt;" ), $value );
		}

		return trim( $value );
	}

	private function _postSecurity( $value )
	{
		if( !empty( $value ) and !is_numeric( $value ) )
		{
			$value = str_replace( array( "\t", "\r", "\n", "../" ), "", $value );
			$value = $this->_unHtmlentities( $value );
			unset( $matches );
			preg_match_all( '/<!\[cdata\[(.*?)\]\]>/is', $value, $matches );
			//$value = $this->filterTags( $value );
			$value = str_replace( $matches[0], $matches[1], $value );
			$value = strip_tags( $value );
			$value = preg_replace( '#(' . implode( '|', $this->functionDisable ) . ')(\s*)\((.*?)\)#si', "", $value );
			$value = str_replace( array( '\'', '"', '<', '>' ), array( "&#039;", "&quot;", "&lt;", "&gt;" ), $value );
		}

		return $value;
	}

	private function _unHtmlentities( $value )
	{
		$value = preg_replace( "/%3A%2F%2F/", '', $value );
		$value = preg_replace( '/([\x00-\x08][\x0b-\x0c][\x0e-\x20])/', '', $value );
		$value = preg_replace( "/%u0([a-z0-9]{3})/i", "&#x\\1;", $value );
		$value = preg_replace( "/%([a-z0-9]{2})/i", "&#x\\1;", $value );
		$value = str_ireplace( array( '&#x53;&#x43;&#x52;&#x49;&#x50;&#x54;', '&#x26;&#x23;&#x78;&#x36;&#x41;&#x3B;&#x26;&#x23;&#x78;&#x36;&#x31;&#x3B;&#x26;&#x23;&#x78;&#x37;&#x36;&#x3B;&#x26;&#x23;&#x78;&#x36;&#x31;&#x3B;&#x26;&#x23;&#x78;&#x37;&#x33;&#x3B;&#x26;&#x23;&#x78;&#x36;&#x33;&#x3B;&#x26;&#x23;&#x78;&#x37;&#x32;&#x3B;&#x26;&#x23;&#x78;&#x36;&#x39;&#x3B;&#x26;&#x23;&#x78;&#x37;&#x30;&#x3B;&#x26;&#x23;&#x78;&#x37;&#x34;&#x3B;', '/*', '*/', '<!--', '-->', '<!-- -->', '&#x0A;', '&#x0D;', '&#x09;', '' ), '', $value );
		$search = '/&#[xX]0{0,8}(21|22|23|24|25|26|27|28|29|2a|2b|2d|2f|30|31|32|33|34|35|36|37|38|39|3a|3b|3d|3f|40|41|42|43|44|45|46|47|48|49|4a|4b|4c|4d|4e|4f|50|51|52|53|54|55|56|57|58|59|5a|5b|5c|5d|5e|5f|60|61|62|63|64|65|66|67|68|69|6a|6b|6c|6d|6e|6f|70|71|72|73|74|75|76|77|78|79|7a|7b|7c|7d|7e);?/ie';
		$value = preg_replace( $search, "chr(hexdec('\\1'))", $value );
		$search = '/&#0{0,8}(33|34|35|36|37|38|39|40|41|42|43|45|47|48|49|50|51|52|53|54|55|56|57|58|59|61|63|64|65|66|67|68|69|70|71|72|73|74|75|76|77|78|79|80|81|82|83|84|85|86|87|88|89|90|91|92|93|94|95|96|97|98|99|100|101|102|103|104|105|106|107|108|109|110|111|112|113|114|115|116|117|118|119|120|121|122|123|124|125|126);?/ie';
		$value = preg_replace( $search, "chr('\\1')", $value );
		$search = array( '&#60', '&#060', '&#0060', '&#00060', '&#000060', '&#0000060', '&#60;', '&#060;', '&#0060;', '&#00060;', '&#000060;', '&#0000060;', '&#x3c', '&#x03c', '&#x003c', '&#x0003c', '&#x00003c', '&#x000003c', '&#x3c;', '&#x03c;', '&#x003c;', '&#x0003c;', '&#x00003c;', '&#x000003c;', '&#X3c', '&#X03c', '&#X003c', '&#X0003c', '&#X00003c', '&#X000003c', '&#X3c;', '&#X03c;', '&#X003c;', '&#X0003c;', '&#X00003c;', '&#X000003c;', '&#x3C', '&#x03C', '&#x003C', '&#x0003C', '&#x00003C', '&#x000003C', '&#x3C;', '&#x03C;', '&#x003C;', '&#x0003C;', '&#x00003C;', '&#x000003C;', '&#X3C', '&#X03C', '&#X003C', '&#X0003C', '&#X00003C', '&#X000003C', '&#X3C;', '&#X03C;', '&#X003C;', '&#X0003C;', '&#X00003C;', '&#X000003C;', '\x3c', '\x3C', '\u003c', '\u003C' );
		$value = str_ireplace( $search, '<', $value );
		return $value;
	}

	protected function _getSessionPath()
	{
		$savePath = '';
		$path = vF_constant::vF_RESOURCE_DIR . '/' . vF_constant::vF_SESSION_DIR;
		$disableFunctions = vF_system::getInstance()->getDisableFunctions();

		if ( function_exists( 'session_save_path' ) and !in_array( 'session_save_path', $disableFunctions ) )
		{
			if ( !empty( $path ) )
			{
				$savePath = vF_DIR . '/' . $path;
				if ( !is_dir( $savePath ) )
				{
					$oldumask = umask( 0 );
					$res = @mkdir( $savePath, 0755 );
					umask( $oldumask );
				}

				if ( !@is_writable( $savePath ) )
				{
					if ( !@chmod( $savePath ) ) $savePath = '';
				}

				clearstatcache();
				if ( ! empty( $savePath ) ) session_save_path( $savePath . '/' );
			}
		}

		return session_save_path();
	}

	public function testCookie()
	{
		$this->addCookie( 'vfTestCookie', md5( $this->cookiePrefix ) );
		$cookie = $this->getCookie( 'vfTestCookie', '' );
		$this->unsetCookie( 'vfTestCookie' );
		return ( $cookie == md5( $this->cookiePrefix ) );
	}

	protected function _cleanAll()
	{
		if( isset( $_GET ) ) $_GET = array();
		if( isset( $_POST ) ) $_POST = array();
		$_REQUEST = array();
		$this->endSession();
		$_COOKIE = array();
	}

	public function Env( $key )
	{
		require_once( vF_DIR . '/vF_Core/functions/client.php' );
		return vF_GetEnv( $key );
	}

	public function hashCookieValue( $value )
	{
		if( empty( $value ) OR !is_string( $value ) ) return $value;

		$key = md5( $this->cookiePrefix . !empty( $GLOBALS['vF_Config']->key ) ? $GLOBALS['vF_Config']->key : '' );
		$keyOne = md5( substr( $key, 0, 16 ) );
		$keyTwo = md5( substr( $key, 16, 32 ) );
		return md5( $value . $keyOne ) . $keyTwo;
	}
}