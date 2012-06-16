<?php 
vF_Check();

# ----------------------
# Module: Forum
# Option: Main
# Author: Yplit
# Date: 13/6/2012
#-----------------------

// ----------- VARIABLE ------------
$db = vF_getVf::get( 'Db' );
$vF = $GLOBALS['vF'];

// ---------- MAIN REQUEST ----------

/**
* Some code here
* Blab, blab, blab ....
*/

$contents = "Hello world!!!"; 

exit( $contents );

// ---------- PRINT RESULT -----------

$params = array(
	// Some Params here 
	// Ex: 'param1' => 'params content', ...
);

vF_themes::getInstance()->printResult( 
	vF_themes::getInstance()->render( 
		'template_name', $contents, $params // [auto register system param , true]
	)
);