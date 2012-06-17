<?php 

/**
 * Help to define to run include files.
 *
 * @return booelan
 */
define( 'IS_VF', true );

/**
 * Get microtime for debug info, get time excure page
 * 
 * @return int
 */
$start_time = microtime( true ); global $start_time;

/**
 * Require to Gobal file for main forum and
 * Admin center.
 *
 * @return 
 */
require( dirname( __file__ ) . '/global.php' );

/**
 * Connect to module and show result 
 * 
 * @return boolean
 */
vF_module::getInstance()->loadModule(

	/**
	 * Check Module name by Url string and return it
	 * if module exists and active. Return default 
	 * module if this module incorrect 
	 * 
	 * @return string
	 */
	vF_module::getInstance()->checkModule(
	
		/**
		 * Get Modules name and Options name, return
		 * the "forum" module if this option is empty 
		 * or not exists
		 * 
		 * @return string
		 */
		vF_input::getInstance()->getStringParam( 
			/**
			 * List param to get module name 
			 * 
			 * @param const vF_MODULE_PARAM
			 * @param string Method input
			 * @param string Default module
			 * 
			 * return
			 */
			vF_constant::vF_MODULE_PARAM, 'g', 'forum'
		)
	)
);
?>