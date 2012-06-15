<?php 
vF_Check();

# ----------------------
# Class: Load Module
# Author: Yplit
# Date: 14/6/2012
#-----------------------
class vF_module
{
	private static $_instance;
	public $currentModule = 'forum';
	public $listModule = array();
	public $vF;

	public function __construct()
	{
		$this->vF = $GLOBALS['vF'];
	}

	public static function getInstance()
	{
		if( !self::$_instance )
		{
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function setCurrentModule( $moduleName )
	{
		$this->currentModule = $moduleName;
	}

	public function modulePermission( $moduleName )
	{
		$db = vF_getVf::get( 'Db' );
		$db->query( 'SELECT * FROM `'. $db->tableName( 'premission' ) .'` WHERE `moduleID` = '. $this->getModuleId( $moduleName ) .' LIMIT 1' );
		if( $db->nums() == 0 ) return false;
		$result = $db->fetch_object();
		#
		#
		# vF_Error::getInstance()->notModule( $moduleName );
		#
		#
	}

	public function getModuleId( $moduleName )
	{
		$db = vF_getVf::get( 'Db' );
		
		$query = $db->query( 'SELECT `id` FROM `'. $db->tableName( 'modules' ) .'` WHERE `moduleName` = '. $db->e( $moduleName ) .' LIMIT 1' );
		if( $db->nums( $query ) == 0 ) return 0;

		$result = $db->fetch_object( $query );

		return (int)$result->id;
	}

	public function scanModuleList()
	{
		$listModile = array();
		$dir = vF_DIR . '/vF_Core/' . vF_constant::vF_MODULE_DIR . '/';

		@chmod( $dir, 0777 );

		$scanDir = scandir( $dir );
		foreach( $scanDir as $dirName )
		{
			if( !preg_math( '/^[A-z0-9-_]+$/', $dirName ) ) continue;
			if( $this->_checkModuleFile( $dirName ) )
			{
				$listModile[] = $dirName;
			}
		}
	}

	private function _checkModuleFile( $dir )
	{
		if( empty( $dir ) ) return false;

		$path = vF_DIR . '/vF_Core/' . vF_constant::vF_MODULE_DIR . '/';
		$listFile = array(
		//	$path . 'language',
			$path . 'options',
			$path . 'function.php',
			$path . 'isModule.php',
		);
		foreach( $listFile as $fileModule )
		{
			if( !file_exists( $fileModule ) )
			{
				return false;
			}
		}

		return true;
	}

	private function _connectModule()
	{
		$options = vF_input::getInstance()->getParam( '' );
	}

	public function checkModule( $moduleName )
	{
		if( $this->vF->Options->vf_cache_module )
		{
			$listModule = vF_cache::getInstance()->loadCache( 'moduleList' );
		}

		if( !$listModule )
		{
			// For Test
				$this->vF->Options->vf_default_module = 'forum';
			//
			$db = vF_getVf::get('Db');
			$db->query( 'SELECT `moduleName` FROM `'. $db->tableName( 'modules' ) .'` WHERE `active` = 1 ' );
			if( $db->nums() == 0 ) return $this->vF->Options->vf_default_module;
			$listModule = $db->fetchrow();
			if( !$listModule or count( $listModule ) == 0 ) return $this->vF->Options->vf_default_module;
			if( $this->vF->Options->vf_cache_module )
			{
				vF_cache::getInstance()->newCache( 'moduleList', $listModule );
			}
		}

		if( !in_array( $moduleName, $listModule ) ) return $this->vF->Options->vf_default_module;
		return $moduleName;
	}

	public function loadModule( $moduleName )
	{
		$this->setCurrentModule( $moduleName );

		require( vF_DIR . '/vF_Core/' . vF_constant::vF_MODULE_DIR . '/' . $moduleName . '/isModules.php' );

		$option = vF_input::getStringParam( vF_constant::vF_MODULE_OPTION_PARAM, 'g', '' );
		if( empty( $option ) AND $vF_moduleOptionsDefault AND !empty( $vF_moduleOptionsDefault ) )
		{
			$option = $vF_moduleOptionsDefault;
		}

		vF_DIR . '/vF_Core/' . vF_constant::vF_MODULE_DIR . '/' . $moduleName . '/options/' . $option . '/main.php';
		if( file_exists( vF_DIR . '/vF_Core/' . vF_constant::vF_MODULE_DIR . '/' . $moduleName . '/options/' . $option . '/main.php' ) )
		{
			require( vF_DIR . '/vF_Core/' . vF_constant::vF_MODULE_DIR . '/' . $moduleName . '/options/' . $option . '/main.php' );
			return true;
		}

		vF_Error::getInstance()->moduleError( $moduleName );
	}
}