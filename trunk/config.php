<?php 
if( !defined( 'IS_VF' ) ) die();


class vF_configForum
{

	# ------------------------------
	# SQL Infomation
	  var $dbhost = 'localhost';
	  var $dbuser = 'root';
	  var $dbpass = '';
	  var $dbname = 'vinaforum';
	  var $tablePrefix = 'vF_';
	# -------------------------------


	# -------------------------------
	# Developer Options
	  var $debug = true;
	  var $adminIpOnly = false;
	# -------------------------------

	# -------------------------------
	# Not Change
	  var $key = '8300f1006560ab382348760502d3c760';
	# -------------------------------

}

$vF_Config = new vF_configForum();
