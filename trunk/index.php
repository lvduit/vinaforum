<?php 
define( 'IS_VF', true );
$start_time = time();
require( dirname( __file__ ) . '/global.php' );
vF_module::getInstance()->loadModule(
	vF_module::getInstance()->checkModule(
		vF_input::getInstance()->getStringParam( 
			vF_constant::vF_MODULE_PARAM, 'g', 'forum'
		)
	)
);
?>