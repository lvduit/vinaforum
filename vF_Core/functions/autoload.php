<?php 
vf_check();

// Auto Load Class 
function __autoload( $class )
{
	if( preg_match( '/[^A-z0-9_]/', $class ) )
	{
		return false;
	}

	$class = 'vF_' . $class;

	if( class_exists( $class ) )
	{
		return false;
	}

	if( file_exists( VF_DIR . '/vF_Core/classCore/' . $class . '.php' ) )	
	{
		require( VF_DIR . '/vF_Core/classCore/' . $class . '.php' );
		return true;
	}
	elseif( defined( 'VF_MODULE' ) and file_exists( VF_DIR . '/vF_Core/module/' . VF_MODULE . '/class/' . $class . '.php' ) )
	{
		require( VF_DIR . '/vF_Core/module/' . VF_MODULE . '/class/' . $class . '.php' );
		return true;
	}

	return false;
}

?>