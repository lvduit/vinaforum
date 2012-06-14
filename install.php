<?php 
define( 'IS_VF', true );
define( 'vF_DIR', dirname( __file__ ) );
define( 'IS_INSTALL', true );

require( vF_DIR . '/vF_Core/functions/checkSecurity.php' );
//require( vF_DIR . '/vF_Core/functions/autoload.php' );
require( vF_DIR . '/vF_Core/install/function.php' );
require( vF_DIR . '/vF_Core/install/vF_class.php' );

vF_install::getInstance()->process();

?>