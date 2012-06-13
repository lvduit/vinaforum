<?php 
vF_Check();

# ----------------------
# Class: Array2Object v1.0
# Author: Yplit
# Date: 13/6/2012
#-----------------------
class vF_Array2Object
{
	private $_instance;
	private $_object;
	public $convert_all_sub_array = false;

	public function __construct()
	{
	}

	public function getInstance()
	{
		if( !self::$_instance )
		{
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function convert( array $array, $convert_all_sub_array )
	{
		if( !is_array( $array ) )
		{
			$array = array( $array );
		}

		if( $convert_all_sub_array )
		{
			$this->convert_all_sub_array = true;
		}

		$this->_object = stdClass;
		foreach( $array as $_obKey => $_obValue )
		{
			$this->_addOb( $this->_object, $_obKey, $_obValue );
		}

		return $this->_object;
	}

	private function _addOb( &$object, $_obKey, $_obValue )
	{
		$object->$_obKey =  $_obValue;

		if( $this->convert_all_sub_array and is_array( $_obValue ) )
		{
			foreach( $_obValue as $__obKey => $__obValue  )
			{
				$this->_addOb( $object->$_obKey, $__obKey, $__obKey );
			}
		}
		return;
	}
}