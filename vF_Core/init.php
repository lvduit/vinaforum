<?php 
vf_check();

# ---------------------------
# Global Init
# Note: Start System > Input > Client >  Db > Options > User >  Lang
# ---------------------------
if( $vF ) return;

	$vF = stdClass;
	$vF->init = $vF->User = $vF->Client = $vF->Options = $vF->Lang = stdClass;

	// Load config file
	require( vF_DIR . '/' . vF_CONFIG_FILE );

	$vF->init->time = $start_time;

	$vF->System = new vF_system;
	$vF->System->startSystem();

	$vF->Input = new vF_input;

	$vF->Client = vF_getClient::getInstance()->getClient();

	$vF->Db = new vF_database(  );

	$vF->User = vF_getUser::getInstance()->getUser();

	$vF->Options = vF_getOptions::getInstance()->getOptions();

	$vF->Lang = new vF_Language( $vF->Options->forumLanguage );


?>