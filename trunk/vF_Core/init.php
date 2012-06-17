<?php 
vf_check();

# ---------------------------
# Global Init
# ---------------------------
if( $vF ) return;

vF_config::getInstance()->loadConfig( vF_DIR . '/' . vF_constant::vF_CONFIG_FILE, $vF_Config );
global $vF_Config; 
global $vF;

$vF = vF_init::getInstance();
$vF->System = vF_system::getInstance();
$vF->System->startSystem();
$vF->System->debug = ( $vF_Config->debug !== true ? false : true );
//$vF->Input = vF_input::getInstance();
$vF->Client = vF_getClient::getInstance()->getClient();
$vF->Db = vF_database::getInstance( $vF_Config );
//$vF->User = vF_getUser::getInstance()->getUser();
$vF->Options = vF_getOptions::getInstance()->getOptions();
$vF->Lang = vF_Language::getInstance();

?>