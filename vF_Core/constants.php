<?php 
vf_check();

if( defined( 'VF_IS_LOAD_CONSTANTS' ) ) return;
define( 'vF_IS_LOAD_CONSTANTS', true );

// Ten file cau hinh database
define( 'vF_CONFIG_FILE', 'config.php' );

// Thu muc resource
define( 'vF_RESOURCE_DIR', 'vietf_Resource' );

// Thu muc cache
define( 'vF_CACHE_DIR', 'cache' );

// Thu muc tam
define( 'vF_TMP_DIR', 'tmp' );

// Memory limit
define( 'vF_MEMORY_LIMIT', 128 * 1024 * 1024 ); // 128 MB



?>