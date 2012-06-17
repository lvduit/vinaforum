<?php 
vF_Check();

# ----------------------
# Class: getClient
# Author: Yplit
# Date: 13/6/2012
#-----------------------
class vF_getClient
{
	private static $_instance;
	public $IP = false;
	public $browser = '';
	public $Os = '';
	public $userAgent = '';
	public $referer = '';
	public $clientHash = '';
	public $isMobile = false;
	public $cookieEnble;

	public function __construct()
	{
	}

	public static function getInstance()
	{
		if( !self::$_instance )
		{
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function getClient()
	{
		require_once( vF_DIR . '/vF_Core/functions/client.php' );
		if( ! $this->userAgent ) $this->userAgent = $this->_getUserAgent();
		if( ! $this->IP ) $this->IP = $this->_getIP();
		if( ! $this->browser ) $this->browser = $this->_getBrowser();
		if( ! $this->Os ) $this->Os = $this->_getOs();
		if( ! $this->referer ) $this->referer = $this->_getReferer();

		if( ! $this->clientHash )
		{
			$this->clientHash = vF_hash::getInstance()->hashString( $this->userAgent );
		}

		if( !$this->isMobile ) $this->isMobile = ( $this->checkMobile() ? 1 : 0 );

		//$this->cookieEnble = ( $this->_testCookie() ? 1 : 0 );

		return $this;
	}

	private function _getIP( $format = false )
	{
		if( vF_GetEnv( 'HTTP_CLIENT_IP' ) and strcasecmp( vF_GetEnv( 'HTTP_CLIENT_IP' ), 'unknown' ) )
		{
			$onlineip = vF_GetEnv( 'HTTP_CLIENT_IP' );
		}
		elseif( vF_GetEnv( 'HTTP_X_FORWARDED_FOR') and strcasecmp( vF_GetEnv( 'HTTP_X_FORWARDED_FOR' ), 'unknown' ) )
		{
			$onlineip = vF_GetEnv( 'HTTP_X_FORWARDED_FOR' );
		}
		elseif( vF_GetEnv( 'REMOTE_ADDR' ) and strcasecmp( vF_GetEnv( 'REMOTE_ADDR' ), 'unknown' ) )
		{
			$onlineip = vF_GetEnv( 'REMOTE_ADDR' );
		}
		elseif( isset( $_SERVER['REMOTE_ADDR'] ) and $_SERVER['REMOTE_ADDR'] and strcasecmp( $_SERVER['REMOTE_ADDR'], 'unknown' ) )
		{
			$onlineip = $_SERVER['REMOTE_ADDR'];
		}
		preg_match("/[\d\.]{7,15}/", $onlineip, $onlineipmatches);
		$return = $onlineipmatches[0] ? $onlineipmatches[0] : 'unknown';

		if( !$format ) return $return;

		$ips = explode('.', $return);
		for( $i=0; $i<3; $i++ )
		{
			$ips[$i] = intval( $ips[$i] );
		}
		return sprintf( '%03d%03d%03d', $ips[0], $ips[1], $ips[2] );
	}

	private function _getBrowser()
	{
		# ----------------------
		# File: browser.ini
		# Licsense: Nukeviet
		#-----------------------
		$browser = vF_ParseIniFile( vF_DIR . '/vF_Core/file/ini/browser.ini' );
		if( !$browser ) return false;

		if( is_array( $browser ) )
		{
			foreach( $browser as $name => $info )
			{
				if( preg_match( '#'. $info['rule'] .'#i', $this->userAgent ) )
				return $name . '|' . $info['name'];
			}
		}
		return ( 'Unknown' );
	}

	private function _getUserAgent()
	{
		return ( vF_GetEnv( 'HTTP_USER_AGENT' ) );
	}

	private function _getOs()
	{
		$os = vF_ParseIniFile( vF_DIR . '/vF_Core/file/ini/os.ini' );
		if( !$os ) return false;

		foreach( $os as $name => $info )
		{
			if( preg_match( "#" . $info['rule'] . "#i", $this->userAgent, $results ) )
			{
				if( strstr( $name, "win" ) )
				{
					return ( $name . '|' . $info['name'] );
				}
				if( isset( $results[1] ) )
				{
					return ( $name . '|' . $info['name'] . ' ' . $results[1] );
				}
				return ( $name . '|' . $info['name'] );
			}
		}
		return ( "Unspecified|Unspecified" );
	}

	private function _getReferer()
	{
		return ( vF_GetEnv( 'HTTP_REFERER' ) );
	}

	public function checkMobile()
	{
		if( preg_match("/Creative\ AutoUpdate/i", $this->userAgent ) )
		{
			return false;
		}

		if (isset($_SERVER['X-OperaMini-Features']))
			return true;
		if( isset( $_SERVER['UA-pixels'] ) )
			return true;
		if( isset( $_SERVER['HTTP_X_WAP_PROFILE'] ) OR isset( $_SERVER['HTTP_PROFILE'] ) )
			return true;
		if (isset($_SERVER['HTTP_ACCEPT']) && preg_match("/wap\.|\.wap/i", $_SERVER["HTTP_ACCEPT"]))
			return true;
		if ( preg_match('/(mini 9.5|vx1000|lge |m800|e860|u940|ux840|compal|wireless| mobi|ahong|lg380|lgku|lgu900|lg210|lg47|lg920|lg840|lg370|sam-r|mg50|s55|g83|t66|vx400|mk99|d615|d763|el370|sl900|mp500|samu3|samu4|vx10|xda_|samu5|samu6|samu7|samu9|a615|b832|m881|s920|n210|s700|c-810|_h797|mob-x|sk16d|848b|mowser|s580|r800|471x|v120|rim8|c500foma:|160x|x160|480x|x640|t503|w839|i250|sprint|w398samr810|m5252|c7100|mt126|x225|s5330|s820|htil-g1|fly v71|s302|-x113|novarra|k610i|-three|8325rc|8352rc|sanyo|vx54|c888|nx250|n120|mtk |c5588|s710|t880|c5005|i;458x|p404i|s210|c5100|teleca|s940|c500|s590|foma|samsu|vx8|vx9|a1000|_mms|myx|a700|gu1100|bc831|e300|ems100|me701|me702m-three|sd588|s800|8325rc|ac831|mw200|brew |d88|htc\/|htc_touch|355x|m50|km100|d736|p-9521|telco|sl74|ktouch|m4u\/|me702|8325rc|kddi|phone|lg |sonyericsson|samsung|240x|x320|vx10|nokia|sony cmd|motorola|up.browser|up.link|mmp|symbian|smartphone|midp|wap|vodafone|o2|pocket|kindle|mobile|psp|treo)/i', $this->userAgent ) )
			return true;
		if( preg_match("/Nokia([^\/]+)\/([^ SP]+)/i", $this->userAgent, $matches ) )
		{
			if( stripos( $this->userAgent, 'Series60' ) !== false || strpos( $this->userAgent, 'S60' ) !== false)
			{
				return true;
			}
			else
			{
				return true;
			}
		}

		$browsers = vF_ParseIniFile( vF_DIR . '/vF_Core/file/ini/mobile.ini' );
		if ( !empty( $browsers ) )
		{
			foreach( $browsers as $key => $info )
				if ( preg_match( $info['rule'], $this->userAgent ) )
				{
					return true;
				}
		}

		$mbs = array('1207', '3gso', '4thp', '501i', '502i', '503i', '504i', '505i', '506i', '6310', '6590', '770s', '802s', 'a wa', 'acer', 'acs-', 'airn', 'alav', 'asus', 'attw', 'au-m', 'aur ', 'aus ', 'abac', 'acoo', 'aiko', 'alco', 'alca', 'amoi', 'anex', 'anny', 'anyw', 'aptu', 'arch', 'argo', 'bell', 'bird', 'bw-n', 'bw-u', 'beck', 'benq', 'bilb', 'blac', 'c55/', 'cdm-', 'chtm', 'capi', 'cond', 'craw', 'dall', 'dbte', 'dc-s', 'dica', 'ds-d', 'ds12', 'dait', 'devi', 'dmob', 'doco', 'dopo', 'el49', 'erk0', 'esl8', 'ez40', 'ez60', 'ez70', 'ezos', 'ezze', 'elai', 'emul', 'eric', 'ezwa', 'fake', 'fly-', 'fly_', 'g-mo', 'g1 u', 'g560', 'gf-5', 'grun', 'gene', 'go.w', 'good', 'grad', 'hcit', 'hd-m', 'hd-p', 'hd-t', 'hei-', 'hp i', 'hpip', 'hs-c', 'htc ', 'htc-', 'htca', 'htcg', 'htcp', 'htcs', 'htct', 'htc_', 'haie', 'hita', 'huaw', 'hutc', 'i-20', 'i-go', 'i-ma', 'i230', 'iac', 'iac-', 'iac/', 'ig01', 'im1k', 'inno', 'iris', 'jata', 'java', 'kddi', 'kgt', 'kgt/', 'kpt ', 'kwc-', 'klon', 'lexi', 'lg g', 'lg-a', 'lg-b', 'lg-c', 'lg-d', 'lg-f', 'lg-g', 'lg-k', 'lg-l', 'lg-m', 'lg-o', 'lg-p', 'lg-s', 'lg-t', 'lg-u', 'lg-w', 'lg/k', 'lg/l', 'lg/u', 'lg50', 'lg54', 'lge-', 'lge/', 'lynx', 'leno', 'm1-w', 'm3ga', 'm50/', 'maui', 'mc01', 'mc21', 'mcca', 'medi', 'meri', 'mio8', 'mioa', 'mo01', 'mo02', 'mode', 'modo', 'mot ', 'mot-', 'mt50', 'mtp1', 'mtv ', 'mate', 'maxo', 'merc', 'mits', 'mobi', 'motv', 'mozz', 'n100', 'n101', 'n102', 'n202', 'n203', 'n300', 'n302', 'n500', 'n502', 'n505', 'n700', 'n701', 'n710', 'nec-', 'nem-', 'newg', 'neon', 'netf', 'noki', 'nzph', 'o2 x', 'o2-x', 'opwv', 'owg1', 'opti', 'oran', 'p800', 'pand', 'pg-1', 'pg-2', 'pg-3', 'pg-6', 'pg-8', 'pg-c', 'pg13', 'phil', 'pn-2', 'pt-g', 'palm', 'pana', 'pire', 'pock', 'pose', 'psio', 'qa-a', 'qc-2', 'qc-3', 'qc-5', 'qc-7', 'qc07', 'qc12', 'qc21', 'qc32', 'qc60', 'qci-', 'qwap', 'qtek', 'r380', 'r600', 'raks', 'rim9', 'rove', 's55/', 'sage', 'sams', 'sc01', 'sch-', 'scp-', 'sdk/', 'se47', 'sec-', 'sec0', 'sec1', 'semc', 'sgh-', 'shar', 'sie-', 'sk-0', 'sl45', 'slid', 'smb3', 'smt5', 'sp01', 'sph-', 'spv ', 'spv-', 'sy01', 'samm', 'sany', 'sava', 'scoo', 'send', 'siem', 'smar', 'smit', 'soft', 'sony', 't-mo', 't218', 't250', 't600', 't610', 't618', 'tcl-', 'tdg-', 'telm', 'tim-', 'ts70', 'tsm-', 'tsm3', 'tsm5', 'tx-9', 'tagt', 'talk', 'teli', 'topl', 'hiba', 'up.b', 'upg1', 'utst', 'v400', 'v750', 'veri', 'vk-v', 'vk40', 'vk50', 'vk52', 'vk53', 'vm40', 'vx98', 'virg', 'vite', 'voda', 'vulc', 'w3c ', 'w3c-', 'wapj', 'wapp', 'wapu', 'wapm', 'wig ', 'wapi', 'wapr', 'wapv', 'wapy', 'wapa', 'waps', 'wapt', 'winc', 'winw', 'wonu', 'x700', 'xda2', 'xdag', 'yas-', 'your', 'zte-', 'zeto', 'acs-', 'alav', 'alca', 'amoi', 'aste', 'audi', 'avan', 'benq', 'bird', 'blac', 'blaz', 'brew', 'brvw', 'bumb', 'ccwa', 'cell', 'cldc', 'cmd-', 'dang', 'doco', 'eml2', 'eric', 'fetc', 'hipt', 'http', 'ibro', 'idea', 'ikom', 'inno', 'ipaq', 'jbro', 'jemu', 'java', 'jigs', 'kddi', 'keji', 'kyoc', 'kyok', 'leno', 'lg-c', 'lg-d', 'lg-g', 'lge-', 'libw', 'm-cr', 'maui', 'maxo', 'midp', 'mits', 'mmef', 'mobi', 'mot-', 'moto', 'mwbp', 'mywa', 'nec-', 'newt', 'nok6', 'noki', 'o2im', 'opwv', 'palm', 'pana', 'pant', 'pdxg', 'phil', 'play', 'pluc', 'port', 'prox', 'qtek', 'qwap', 'rozo', 'sage', 'sama', 'sams', 'sany', 'sch-', 'sec-', 'send', 'seri', 'sgh-', 'shar', 'sie-', 'siem', 'smal', 'smar', 'sony', 'sph-', 'symb', 't-mo', 'teli', 'tim-', 'tosh', 'treo', 'tsm-', 'upg1', 'upsi', 'vk-v', 'voda', 'vx52', 'vx53', 'vx60', 'vx61', 'vx70', 'vx80', 'vx81', 'vx83', 'vx85', 'wap-', 'wapa', 'wapi', 'wapp', 'wapr', 'webc', 'whit', 'winw', 'wmlb', 'xda-', );
		$userAgent = strtolower( substr( $this->userAgent, 0, 4 ) );
		if( in_array( $userAgent, $mbs ) )
			return true;

		return false;
	}

	protected function _testCookie()
	{
		return ( vF_input::getInstance()->testCookie() ? true : false );
	}
}