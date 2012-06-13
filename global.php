<?php 
if( !defined( 'IS_VF' ) ) die();
define( 'vF_DIR', dirname( __file__ ) );

$start_time = time();

require( vF_DIR . '/vF_Core/functions/checkSecurity.php' );
require( vF_DIR . '/vF_Core/functions/autoload.php' );
require( vF_DIR . '/vF_Core/constants.php' );
require( vF_DIR . '/vF_Core/functions/main.php' );
require( vF_DIR . '/vF_Core/init.php' );