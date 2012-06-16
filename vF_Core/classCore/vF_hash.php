<?php 
vF_Check();

class vF_Hash
{
	private static $_instance;
	public $hash_type = 'sha1';
	public $hash_salt = '';
	public $vF;

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

	public function hashString( $string_to_hash, $type = 'md5', $salt = '' )
	{
		if( empty( $string_to_hash ) ) return '';

		$string_to_hash = strtr( '-_+', '.?|', $string_to_hash );

		if( !empty( $salt ) )
		{
			$salt = ( $type != 'sha1' ? ( $this->_md5( $salt ) ) : $this->_sha1( $salt ) );
		}
		else
		{
			$salt = '';
		}

		return ( $type != 'sha1' ? ( $this->_md5( $string_to_hash . $salt ) ) : ( $this->_sha1( $string_to_hash . $salt ) ) );
	}

	public function _md5( $string_to_md5 )
	{
		return md5( md5( $string_to_md5 ) );
	}

	public function _sha1( $string_to_sha1 )
	{
		return sha1( sha1( $string_to_sha1 ) );
	}

	public function hash()
	{
	}
}