<?php 
vf_check();

# ----------------------
# Function: Auto load classCore
# Author: Yplit
# Date: 14/6/2012
#-----------------------
function __autoload( $className )
{
	if( preg_match( '/[^A-z0-9_]/', $className ) )
	{
		return false;
	}

	if( class_exists( $className ) )
	{
		return true;
	}

	if( substr( $className, 0, 10 ) == 'vF_Module_' ) // Module Custom Class
	{
		if( !class_exists( 'vF_constant' ) )
		{
			require( vF_DIR . '/vF_Core/classCore/vF_constant.php' );
		}
		$classPath = vF_DIR . '/vF_Core/' . vF_constant::vF_MODULE_DIR . '/class/' . $className . '.php';
	}
	elseif( substr( $className, 0 , 10 ) == 'vF_helper_' ) // Helper Class
	{
		$classPath = vF_DIR . '/vF_Core/helper/' . $className . '.php'; 
	}
	else
	{
		// Class Core
		$classPath = vF_DIR . '/vF_Core/classCore/' . $className . '.php';
	}

	if( file_exists( $classPath ) )
	{
		require( $classPath );
		return true;
	}

	return false;
}

?>