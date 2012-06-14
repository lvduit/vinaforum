<?php 
vF_Check();

# ----------------------
# File: Check Module & Module Info
# Author: Yplit
# Date: 14/6/2012
#-----------------------
$vF_moduleInfo = array(
	'moduleName' => 'account',
	'version' => '1.0',
	'author' => 'vF',
	'date' => '14/6/2012',
	'description' => 'Account System',
);

$vF_moduleOptions = array(
	'register',
	'login',
	'userManager',
	'changePassword',
	'forgotPassword', 
	'renewPassword',
	'subAccount',
	'credits',
	'point',
);

$vF_moduleOptionsDefault = 'login';