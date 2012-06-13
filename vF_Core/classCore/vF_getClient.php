<?php 
vF_Check();

# ----------------------
# Class: getClient
# Author: Yplit
# Date: 13/6/2012
#-----------------------
class vF_getClient
{
	static $instance;
	public $IP;
	public $browser;
	public $Os;
	public $user_agent;
	public $referer;
	public $client_hash;

	public function __construct()
	{
	}

	public static getInstance()
	{
		if( !self::$instance )
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static getClient()
	{
		require( vF_DIR . '/vF_Core/functions/client.php' );
		if( !$this->IP ) $this->IP = $this->_getIP();
		if( !$this->browser ) $this->browser = $this->_getBrowser();
		if( !$this->user_agent ) $this->user_agent = $this->_getUserAgent();
		if( !$this->Os ) $this->OS = $this->_getOs();
		if( !$this->referer ) $this->referer = $this->_getReferer();
		if( !$this->client_hash )
		{
			$this->client_hash = vF_hash::getInstance()->hashString( $this->user_agent );
		}
	}

	private function _getIP()
	{
		if( vF_GetEnv( 'HTTP_CLIENT_IP' ) and strcasecmp( vF_GetEnv( 'HTTP_CLIENT_IP' ), 'unknown' ) )
		{
			$onlineip = vF_GetEnv( 'HTTP_CLIENT_IP' );
		}
		elseif( vF_GetEnv( 'HTTP_X_FORWARDED_FOR') and strcasecmp( vF_GetEnv( 'HTTP_X_FORWARDED_FOR' ), 'unknown' ) )
		{
			$onlineip = vF_GetEnv( 'HTTP_X_FORWARDED_FOR' );
		}
		elseif( vF_GetEnv( 'REMOTE_ADDR' ) and strcasecmp( vF_GetEnv( 'REMOTE_ADDR' ), 'unknown' ) )
		{
			$onlineip = vF_GetEnv( 'REMOTE_ADDR' );
		}
		elseif( isset( $_SERVER['REMOTE_ADDR'] ) and $_SERVER['REMOTE_ADDR'] and strcasecmp( $_SERVER['REMOTE_ADDR'], 'unknown' ) )
		{
			$onlineip = $_SERVER['REMOTE_ADDR'];
		}
		preg_match("/[\d\.]{7,15}/", $onlineip, $onlineipmatches);
		$return = $onlineipmatches[0] ? $onlineipmatches[0] : 'unknown';

		if( !$format ) return $return;

		$ips = explode('.', $return);
		for( $i=0; $i<3; $i++ )
		{
			$ips[$i] = intval( $ips[$i] );
		}
		return sprintf( '%03d%03d%03d', $ips[0], $ips[1], $ips[2] );
	}

	private function _getBrowser()
	{
		# ----------------------
		# File: browser.ini
		# Licsense: Nukeviet
		#-----------------------
		$browser = vF_ParseIniFile( vF_DIR . '/vF_Core/file/browser.ini' );
		if( !$browser ) return false;

		if( is_array( $browser ) )
		{
			foreach( $browser as $name => $info )
			{
				if( preg_match( '#'. $info['rule'] .'#i', USER_AGENT ) )
				return $name;
			}
		}
		return ( 'Unknown' );
	}

	private function _getUserAgent()
	{
		return ( vF_GetEnv( 'HTTP_USER_AGENT' ) );
	}

	private function _getOs()
	{
		$os = vF_ParseIniFile( vF_DIR . '/vF_Core/file/os.ini' );
		if( !$os ) return false;

		foreach( $os as $name => $info )
		{
			if( preg_match( "#" . $info['rule'] . "#i", $this->user_agent, $results ) )
			{
				if( strstr( $name, "win" ) )
				{
					return ( $name . '|' . $info['name'] );
				}
				if( isset( $results[1] ) )
				{
					return ( $name . '|' . $info['name'] . ' ' . $results[1] );
				}
				return ( $name . '|' . $info['name'] );
			}
		}
		return ( "Unspecified|Unspecified" );
	}

	private function _getReferer()
	{
		return ( vF_GetEnv( 'HTTP_REFERER' ) );
	}

	
}