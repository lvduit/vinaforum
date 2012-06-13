<?php 
vF_check();

if( defined( 'VF_LOAD_FUNCTION_CLIENT' ) ) return;
define( 'VF_LOAD_FUNCTION_CLIENT', true );

# ----------------------
# Function: Get Env
# Author: Google
# Date: 13/6/2012
#-----------------------
function vF_GetEnv( $keyword )
{
	if ( ! is_array( $keyword ) )
		$keyword = array( $keyword );
	foreach( $keyword as $_keyword )
	{
		if( isset( $_SERVER[$_keyword] ) )
		{
			return $_SERVER[$_keyword];
		}
		elseif( isset( $_ENV[$_keyword] ) )
		{
			return $_ENV[$_keyword];
		}
		elseif( @getenv( $_keyword ) )
		{
			return @getenv( $_keyword );
		}
		elseif( function_exists( 'apache_getenv' ) and apache_getenv( $_keyword, true ) )
		{
			return apache_getenv( $_key, true );
		}
	}
	return '';
}

# ----------------------
# Function: Parse Ini
# Author: Nukeviet
# Date: 13/6/2012
#-----------------------
function vF_ParseIniFile( $filename )
{
	if( empty( $filename ) or !is_readable( $filename ) ) return false;
	if( !file_exists( $filename ) ) return false;

	$file = file( $filename );
	if( !$file ) return false;
	$ini = array();
	$array_key = '';
	foreach( $file as $row )
	{
		$row = trim( $row );
		if( empty( $row ) ) continue;
		if( preg_match( '/^;/', $row ) ) return;
		if( preg_match( '/^\[(.*)\]$/', $row, $match ) )
		{
			$array_key = $match[1];
			continue;
		}
		if( !strpos( $row, '=' ) ) continue;
		list( $key, $val ) = explode( '=', $row );
		$key = trim( $key ); $val = trim( $val );
		$val = str_replace( '"', '', $val );
		$val = str_replace( "'", "", $val );
		if( !empty( $array_key ) )
		{
			if( preg_match( '/^(.*?)\[\]$/', $key, $match ) )
			{
				$ini[$array_key][$match[1]][] = $val;
			}
			else
			{
				$ini[$array_key][$key] = $val;
			}
		}
	}
	return $ini;
}
