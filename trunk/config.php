<?php 
if( !defined( 'IS_VF' ) ) die();

$vF_Config = stdClass;

# ------------------------------
# SQL Infomation
  $vF_Config->dbhost = 'localhost';
  $vF_Config->dbuser = 'root';
  $vF_Config->dbpass = '';
  $vF_Config->dbname = 'vinaforum';
  $vF_Config->tablePrefix = 'vF_';
# -------------------------------


# -------------------------------
# Developer Options
  $vF_Config->debug = false;
  $vF_Config->adminIpOnly = false;
# -------------------------------

# -------------------------------
# Not Change
  $vF_Config->key = '8300f1006560ab382348760502d3c760';
# -------------------------------