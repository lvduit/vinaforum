<?php 
vF_Check();

class vF_templateHelper
{
	protected static $_modelCache = array();

	public $vF;

	public static $stringCallbacks = array(
		'repeat'   => 'str_repeat',
		'nl2br'    => 'nl2br',
		'trim'     => 'trim',
		'censor'   => array('vF_helper_string', 'censorStringTemplateHelper'),
		'wordtrim' => array('vF_helper_string', 'wholeWordTrim'),
		'autolink' => array('vF_helper_string', 'autoLinkPlainText'),
		'wrap'     => array('vF_helper_string', 'wordWrapString')
	);

	protected static $_styleProperties = array();

	protected static $_invalidStylePropertyAccess = array();

	protected static $_displayStyles = array();

	protected static $_threadPrefixes = array();

	protected static $_userTitles = array();

	protected static $_language = null;

	protected static $_userFieldsInfo = array();

	protected static $_userFieldsValues = array();

	public static $helperCallbacks = array(
		'avatar'            => array('self', 'helperAvatarUrl'),
		'avatarcropcss'     => array('self', 'helperAvatarCropCss'),
		'username'          => array('self', 'helperUserName'),
		'usertitle'         => array('self', 'helperUserTitle'),
		'richusername'      => array('self', 'helperRichUserName'),
		'userblurb'         => array('self', 'helperUserBlurb'),
		'sortarrow'         => array('self', 'helperSortArrow'),
		'json'              => array('self', 'helperJson'),
		'clearfix'          => array('XenForo_ViewRenderer_Css', 'helperClearfix'),
		'cssimportant'      => array('XenForo_ViewRenderer_Css', 'helperCssImportant'),
		'snippet'           => array('self', 'helperSnippet'),
		'bodytext'          => array('self', 'helperBodyText'),
		'bbcode'            => array('self', 'helperBbCode'),
		'highlight'         => array('vF_helper_string', 'highlightSearchTerm'),
		'striphtml'         => array('self', 'helperStripHtml'),
		'linktitle'         => array('vF_link', 'buildIntegerAndTitleUrlComponent'),
		'wrap'              => array('self', 'helperWrap'),
		'wordtrim'          => array('self', 'helperWordTrim'),
		'pagenumber'        => array('self', 'helperPageNumber'),
		'dump'              => array('self', 'helperDump'),
		'type'              => array('self', 'helperType'),
		'implode'           => array('self', 'helperImplode'),
		'rgba'              => array('vF_helper_color', 'rgba'),
		'unrgba'            => array('vF_helper_color', 'unrgba'),
		'fullurl'           => array('vF_link', 'convertUriToAbsoluteUri'),
		'ismemberof'        => array('self', 'helperIsMemberOf'),
		'twitterlang'       => array('self', 'helperTwitterLanguage'),
		'listitemid'        => array('vF_templateHelperAdmin', 'getListItemId'),
		'threadprefix'      => array('self', 'helperThreadPrefix'),
		'threadprefixgroup' => array('self', 'helperThreadPrefixGroup'),
		'ignoredcss'        => array('self', 'helperIgnoredCss'),
		'isignored'         => array('self', 'helperIsIgnored'),
		'nodeclasses'       => array('self', 'helperNodeClasses'),

		'avatarhtml'        => array('self', 'helperAvatarHtml'),
		'datetimehtml'      => array('self', 'helperDateTimeHtml'),
		'followhtml'        => array('self', 'helperFollowHtml'),
		'likeshtml'         => array('self', 'helperLikesHtml'),
		'pagenavhtml'       => array('self', 'helperPageNavHtml'),
		'usernamehtml'      => array('self', 'helperUserNameHtml'),

		'userfieldtitle'    => array('self', 'helperUserFieldTitle'),
		'userfieldvalue'    => array('self', 'helperUserFieldValue'),

		'javascripturl'     => array('self', 'helperJavaScriptUrl'),
	);

	private function __construct()
	{
		$this->vF = $GLOBALS['vF'];
	}

	public static function appendBreadCrumbs(array $existing, $new)
	{
		if (!is_array($new))
		{
			return $existing;
		}

		foreach ($new AS $breadCrumb)
		{
			if (isset($breadCrumb['value']))
			{
				$breadCrumb['value'] = htmlspecialchars($breadCrumb['value']);
			}

			$existing[] = $breadCrumb;
		}

		return $existing;
	}

	public static function rawCondition($data, $condition = 'object')
	{
		if (!$condition OR !is_string($condition))
		{
			return $data;
		}

		switch ($condition)
		{
			case 'object':
				if (is_object($data))
				{
					return $data;
				}
				break;

			default:
				if ($data instanceof $condition)
				{
					return $data;
				}
				break;
		}

		return htmlspecialchars($data);
	}

	public static function date($timestamp, $format = null)
	{
		return XenForo_Locale::date($timestamp, $format, self::$_language);
	}

	public static function time($timestamp, $format = null)
	{
		return XenForo_Locale::time($timestamp, $format, self::$_language);
	}

	public static function dateTime($timestamp, $format = null)
	{
		if ($format == 'html')
		{
			return self::callHelper('datetimehtml', array($timestamp));
		}
		else
		{
			return XenForo_Locale::dateTime($timestamp, $format, self::$_language);
		}
	}

	public static function helperDateTimeHtml($timestamp, $attributes = array())
	{
		$class = (empty($attributes['class']) ? '' : ' ' . htmlspecialchars($attributes['class']));

		unset($attributes['time'], $attributes['class']);

		$attribs = self::getAttributes($attributes);

		$time = XenForo_Locale::dateTime($timestamp, 'separate', self::$_language);

		if ($time['relative'])
		{
			$tag = 'abbr';
			$data = ' data-time="' . $timestamp . '" data-diff="' . (XenForo_Application::$time - $timestamp)
				. '" data-datestring="' . $time['date'] . '" data-timestring="' . $time['time'] . '"';
			$value = $time['string'];
		}
		else
		{
			$tag = 'span';
			$data = ' title="' . $time['string'] . '"'; // empty this to remove tooltip from non-relative dates
			$value = $time['date'];
		}

		return "<{$tag} class=\"DateTime{$class}\"{$attribs}{$data}>{$value}</{$tag}>";
	}

	public static function helperFollowHtml(array $user, array $attributes, $wrapTag = '')
	{
		$visitor = $this->vF->user;

		if (!$visitor['user_id'] OR $visitor['user_id'] == $user['user_id'])
		{
			return '';
		}

		if ($visitor->isFollowing($user['user_id']))
		{
			$action = 'unfollow';
		}
		else
		{
			$action = 'follow';
		}

		$link =  XenForo_Link::buildPublicLink("members/$action", $user, array('_xfToken' => $visitor['csrf_token_page']));

		$class = (empty($attributes['class']) ? '' : ' ' . htmlspecialchars($attributes['class']));

		unset($attributes['user'], $attributes['class']);

		if (!isset($attributes['title']) && isset($user['following']))
		{
			if (self::_getModelFromCache('XenForo_Model_User')->isFollowing($visitor['user_id'], $user))
			{
				$attributes['title'] = new XenForo_Phrase('user_is_following_you', array('user' => $user['username']));
			}
			else
			{
				$attributes['title'] = new XenForo_Phrase('user_is_not_following_you', array('user' => $user['username']));
			}
		}

		if (!empty($attributes['tag']))
		{
			$tag = $attributes['tag'];
			unset($attributes['tag']);
		}

		$attribs = self::getAttributes($attributes);

		$link = "<a href=\"{$link}\" class=\"FollowLink{$class}\"  {$attribs}>" . new XenForo_Phrase($action) . '</a>';

		if (!empty($tag))
		{
			return "<$tag>$link</$tag>";
		}
		else
		{
			return $link;
		}
	}

	public static function helperLikesHtml($number, $likesLink, $likeDate = 0, array $users = array())
	{
		$number = intval($number);

		if (empty($users))
		{
			return new XenForo_Phrase('likes_x_people_like_this', array(
				'likes' => self::numberFormat($number),
				'likesLink' => $likesLink
			));
		}

		$userCount = count($users);
		if ($userCount < 5 && $number > $userCount) // indicates some users are deleted
		{
			for ($i = 0; $i < $number; $i++)
			{
				if (empty($users[$i]))
				{
					$users[$i] = array(
						'user_id' => 0,
						'username' => new XenForo_Phrase('deleted_user_parentheses') // costs a query, but edge case
					);
				}
			}
		}

		if ($likeDate)
		{
			$youLikeThis = true;

			$visitorId = XenForo_Visitor::getUserId();
			foreach ($users AS $key => $user)
			{
				if ($user['user_id'] == $visitorId)
				{
					unset($users[$key]);
					break;
				}
			}

			if (count($users) == 3)
			{
				unset($users[2]);
			}

			$users = array_values($users);
		}
		else
		{
			$youLikeThis = false;
		}

		$user1 = $user2 = $user3 = '';

		if ($users[0])
		{
			$user1 = self::callHelper('username', array($users[0]));

			if ($users[1])
			{
				$user2 = self::callHelper('username', array($users[1]));

				if ($users[2])
				{
					$user3 = self::callHelper('username', array($users[2]));
				}
			}
		}

		$phraseParams = array(
			'user1' => $user1,
			'user2' => $user2,
			'user3' => $user3,
			'others' => self::numberFormat($number - 3),
			'likesLink' => $likesLink
		);

		switch ($number)
		{
			case 1: return new XenForo_Phrase(($youLikeThis
				? 'likes_you_like_this'
				: 'likes_user1_likes_this'), $phraseParams, false);

			case 2: return new XenForo_Phrase(($youLikeThis
				? 'likes_you_and_user1_like_this'
				: 'likes_user1_and_user2_like_this'), $phraseParams, false);

			case 3: return new XenForo_Phrase(($youLikeThis
				? 'likes_you_user1_and_user2_like_this'
				: 'likes_user1_user2_and_user3_like_this'), $phraseParams, false);

			case 4: return new XenForo_Phrase(($youLikeThis
				? 'likes_you_user1_user2_and_1_other_like_this'
				: 'likes_user1_user2_user3_and_1_other_like_this'), $phraseParams, false);

			default: return new XenForo_Phrase(($youLikeThis
				? 'likes_you_user1_user2_and_x_others_like_this'
				: 'likes_user1_user2_user3_and_x_others_like_this'), $phraseParams, false);
		}
	}

	public static function setDefaultLanguage(array $language = null)
	{
		self::$_language = $language;
	}

	public static function getDefaultLanguage()
	{
		return self::$_language;
	}

	public static function jsEscape($string, $context = 'double')
	{
		$quote = ($context == 'double' ? '"' : "'");

		$string = str_replace(
			array('\\',   $quote,        "\r",  "\n",  '</'),
			array('\\\\', '\\' . $quote, "\\r", "\\n", '<\\/'),
			$string
		);

		$string = preg_replace('/-(?=-)/', '-\\', $string);

		return $string;
	}

	public static function link($type, $data = null, array $extraParams = array(), $escapeCallback = 'htmlspecialchars')
	{
		$link = XenForo_Link::buildPublicLink($type, $data, $extraParams);
		if ($escapeCallback)
		{
			$link = call_user_func($escapeCallback, $link);
		}

		return $link;
	}

	public static function adminLink($type, $data = null, array $extraParams = array(), $escapeCallback = 'htmlspecialchars')
	{
		$link = XenForo_Link::buildAdminLink($type, $data, $extraParams);
		if ($escapeCallback)
		{
			$link = call_user_func($escapeCallback, $link);
		}

		return $link;
	}

	public static function helperPageNavHtml($callType, $perPage, $totalItems, $page, $linkType,
		$linkData = false, $linkParams = false, $unreadLink = false, $options = false)
	{
		if (!is_array($linkParams))
		{
			$linkParams = array();
		}
		if (!is_array($options))
		{
			$options = array();
		}

		if ($unreadLink)
		{
			$options['unreadLink'] = $unreadLink;
		}

		if ($callType == 'admin')
		{
			return self::adminPageNav($perPage, $totalItems, $page, $linkType, $linkData, $linkParams, $options);
		}
		else
		{
			return self::pageNav($perPage, $totalItems, $page, $linkType, $linkData, $linkParams, $options);
		}
	}

	public static function pageNav($perPage, $totalItems, $page, $linkType,
		$linkData = null, array $linkParams = array(), array $options = array()
	)
	{
		return self::_getPageNav('XenForo_Template_Public', 'link', $perPage, $totalItems, $page,
			$linkType, $linkData, $linkParams, $options
		);
	}

	public static function adminPageNav($perPage, $totalItems, $page, $linkType,
		$linkData = null, array $linkParams = array(), array $options = array()
	)
	{
		return self::_getPageNav('XenForo_Template_Admin', 'adminLink', $perPage, $totalItems, $page,
			$linkType, $linkData, $linkParams, $options
		);
	}

	protected static function _getPageNav($templateClass, $linkFunction, $perPage, $totalItems, $currentPage,
		$linkType, $linkData = null, array $linkParams = array(), array $options = array()
	)
	{
		// abort if there are insufficient items to make multiple pages
		if ($totalItems < 1 OR $perPage < 1)
		{
			return '';
		}

		$options = array_merge(
			array(
				'unreadLink' => '',
				'template' => 'page_nav',
				'displayRange' => 2 //TODO: make this come from an option?
			),
			$options
		);
		$unreadLinkHtml = htmlspecialchars($options['unreadLink'], ENT_COMPAT, 'iso-8859-1', false);

		$pageTotal = ceil($totalItems / $perPage);

		// abort if there is only one page
		if ($pageTotal <= 1)
		{
			if (!empty($options['unreadLink']))
			{
				return new $templateClass($options['template'], array(
					'unreadLinkHtml' => $unreadLinkHtml,
					'pageTotal' => $pageTotal
				));
			}

			return '';
		}

		$currentPage = min(max($currentPage, 1), $pageTotal);

		// number of pages either side of the current page
		$range = $options['displayRange'];
		$scrollSize = 1 + 2 * $range;
		$scrollThreshold = $scrollSize + 2;

		if ($pageTotal >$scrollThreshold)
		{
			$startPage = max(2, $currentPage - $range);
			$endPage = min($pageTotal, $startPage + $scrollSize);

			$extraPages = $scrollSize - ($endPage - $startPage);
			if ($extraPages > 0)
			{
				$startPage -= $extraPages;
			}
		}
		else
		{
			$startPage = 2;
			$endPage = $pageTotal;
		}

		if ($endPage > $startPage)
		{
			$endPage--;
			$pages = range($startPage, $endPage);
		}
		else
		{
			$pages = array();
		}

		if (isset($linkParams['_params']) && is_array($linkParams['_params']))
		{
			$tempParams = $linkParams['_params'];
			unset($linkParams['_params']);
			$linkParams = array_merge($tempParams, $linkParams);
		}

		$templateVariables = array(
			'pageTotal' => intval($pageTotal),
			'currentPage' => $currentPage,

			'pages' => $pages,
			'range' => $range,
			'scrollThreshold' => $scrollThreshold,

			'startPage' => $startPage,
			'endPage' => $endPage,

			'prevPage' => ($currentPage > 1 ? ($currentPage - 1) : false),
			'nextPage' => ($currentPage < $pageTotal ? ($currentPage + 1) : false),

			'pageNumberSentinel' => XenForo_Application::$integerSentinel,

			'linkType' => $linkType,
			'linkData' => $linkData,
			'linkParams' => $linkParams,

			'maxDigits' => strlen($pageTotal),

			'unreadLinkHtml' => $unreadLinkHtml
		);

		$template = new $templateClass($options['template'], $templateVariables);

		return $template;
	}

	public static function callHelper($helper, array $args)
	{
		$helper = strtolower(strval($helper));
		if (!isset(self::$helperCallbacks[$helper]))
		{
			return '';
		}

		return call_user_func_array(self::$helperCallbacks[$helper], $args);
	}

	public static function helperUserTitle($user, $allowCustomTitle = true)
	{
		if (!is_array($user) OR !array_key_exists('display_style_group_id', $user))
		{
			return '';
		}

		if ($allowCustomTitle && !empty($user['custom_title']))
		{
			return htmlspecialchars($user['custom_title']);
		}

		if (empty($user['user_id']))
		{
			$user['display_style_group_id'] = XenForo_Model_User::$defaultGuestGroupId;
		}

		if (isset($user['display_style_group_id']) && isset(self::$_displayStyles[$user['display_style_group_id']]))
		{
			$style = self::$_displayStyles[$user['display_style_group_id']];
			if ($style['user_title'] !== '')
			{
				return $style['user_title'];
			}
		}

		if (empty($user['user_id']) OR !isset($user['trophy_points']))
		{
			return ''; // guest user title or nothing
		}

		foreach (self::$_userTitles AS $points => $title)
		{
			if ($user['trophy_points'] >= $points)
			{
				return $title;
			}
		}

		return '';
	}

	public static function setUserTitles($userTitles)
	{
		self::$_userTitles = $userTitles;
	}

	public static function helperRichUserName(array $user, $usernameHtml = '')
	{
		if (!is_array($user) OR (!isset($user['username']) && $usernameHtml === ''))
		{
			return '';
		}

		if ($usernameHtml === '')
		{
			$usernameHtml = htmlspecialchars($user['username']);
		}

		if (empty($user['user_id']))
		{
			$user['display_style_group_id'] = XenForo_Model_User::$defaultGuestGroupId;
		}

		if (isset($user['display_style_group_id']) && isset(self::$_displayStyles[$user['display_style_group_id']]))
		{
			$style = self::$_displayStyles[$user['display_style_group_id']];
			if ($style['username_css'])
			{
				return '<span class="style' . $user['display_style_group_id'] . '">' . $usernameHtml . '</span>';
			}
		}

		return $usernameHtml;
	}

	public static function helperUserBlurb(array $user, $includeUserTitle = true)
	{
		if (!is_array($user) OR empty($user['user_id']))
		{
			return '';
		}

		$parts = array();

		if ($includeUserTitle && $userTitle = self::callHelper('usertitle', array($user)))
		{
			$parts[] = '<span class="userTitle" itemprop="title">' . $userTitle . '</span>';
		}

		if (!empty($user['gender']))
		{
			$parts[] = new XenForo_Phrase($user['gender']);
		}

		if (!isset($user['age']) && !empty($user['show_dob_year']) && !empty($user['dob_year']))
		{
			$user['age'] = self::_getModelFromCache('XenForo_Model_UserProfile')->getUserAge($user);
		}

		if (!empty($user['age']))
		{
			$parts[] = $user['age'];
		}

		if (!empty($user['location']))
		{
			$user['locationCensored'] = XenForo_Helper_String::censorString($user['location']);

			$location = '<a href="'
				. XenForo_Link::buildPublicLink('misc/location-info', '', array('location' => $user['locationCensored']))
				. '" class="concealed" target="_blank" rel="nofollow">'
				. htmlspecialchars($user['locationCensored'])
				. '</a>';

			$parts[] = new XenForo_Phrase('from_x_location', array('location' => $location), false);
		}

		return implode(', ', $parts);
	}

	public static function setDisplayStyles(array $displayStyles)
	{
		self::$_displayStyles = $displayStyles;
	}

	public static function helperSortArrow($order, $direction, $fieldName, $descOutput = ' &darr;', $ascOutput = ' &uarr;')
	{
		if ($order != $fieldName)
		{
			return '';
		}
		else if ($direction == 'desc')
		{
			return $descOutput;
		}
		else
		{
			return $ascOutput;
		}
	}

	public static function helperSnippet($string, $maxLength = 0, array $options = array())
	{
		$options = array_merge(array(
			'term' => '',
			'fromStart' => 0,
			'emClass' => '',
			'stripQuote' => false,
		), $options);

		$string = XenForo_Helper_String::bbCodeStrip($string, $options['stripQuote']);

		if ($maxLength)
		{
			if ($options['fromStart'])
			{
				$string = XenForo_Helper_String::wholeWordTrim($string, $maxLength);
			}
			else
			{
				$string = XenForo_Helper_String::wholeWordTrimAroundSearchTerm($string, $maxLength, $options['term']);
			}
		}

		$string = trim($string);
		$string = XenForo_Helper_String::censorString($string);

		if ($options['term'] && $options['emClass'])
		{
			return XenForo_Helper_String::highlightSearchTerm($string, $options['term'], $options['emClass']);
		}
		else
		{
			return htmlspecialchars($string);
		}
	}

	public static function helperBodyText($string)
	{
		$string = XenForo_Helper_String::censorString($string);
		$string = XenForo_Helper_String::autoLinkPlainText(htmlspecialchars($string));

		return nl2br($string);
	}

	public static function helperBbCode($parser, $text)
	{
		if (!($parser instanceof XenForo_BbCode_Parser))
		{
			trigger_error(E_USER_WARNING, 'BB code parser not specified correctly.');
			return '';
		}
		else
		{
			return $parser->render($text);
		}
	}

	public static function helperStripHtml($string, $allowedTags = '')
	{
		return htmlspecialchars(strip_tags($string, $allowedTags), ENT_COMPAT, null, false);
	}

	public static function helperWrap($string, $breakLength)
	{
		return htmlspecialchars(XenForo_Helper_String::wordWrapString($string, $breakLength));
	}

	public static function helperWordTrim($string, $trimLength)
	{
		return htmlspecialchars(XenForo_Helper_String::wholeWordTrim($string, $trimLength));
	}

	public static function helperPageNumber($page)
	{
		$page = intval($page);

		if ($page > 1)
		{
			return htmlspecialchars(new XenForo_Phrase('page_x', array('page' => $page)));
		}
	}

	public static function helperDump($data)
	{
		return Zend_Debug::dump($data, null, false);
	}

	public static function helperType($data)
	{
		return gettype($data);
	}

	public static function helperImplode($data, $glue = ' ')
	{
		if (is_array($glue))
		{
			$temp = $data;
			$data = $glue;
			$glue = $temp;
		}

		return htmlspecialchars(implode($glue, $data));
	}

	public static function helperIsMemberOf(array $user, $userGroupId, $multipleIds = null)
	{
		if (!is_null($multipleIds))
		{
			// check multiple groups
			$userGroupId = array_slice(func_get_args(), 1);
		}

		return self::_getModelFromCache('XenForo_Model_User')->isMemberOfUserGroup($user, $userGroupId);
	}

	public static function helperTwitterLanguage($locale)
	{
		$lang = strtolower(substr($locale, 0, 2));

		switch ($lang)
		{
			case 'en':
			case 'fr':
			case 'de':
			case 'it':
			case 'es':
			case 'ko':
			case 'ja':
				return $lang;

			default:
				return 'en';
		}
	}

	public static function string($functionList, array $args)
	{
		$functions = explode(' ', strval($functionList));
		if (count($functions) > 1 && count($args) > 1)
		{
			return '';
		}

		foreach ($functions AS $function)
		{
			$function = strtolower(trim($function));
			if (!isset(self::$stringCallbacks[$function]))
			{
				continue;
			}

			$args = array(call_user_func_array(self::$stringCallbacks[$function], $args));
		}

		return $args[0];
	}

	public static function styleProperty($propertyName)
	{
		$props = self::$_styleProperties;

		$parts = explode('.', $propertyName, 2);
		if (!empty($parts[1]))
		{
			$propertyName = $parts[0];
			$propertyComponent = $parts[1];
		}
		else
		{
			$propertyComponent = '';
		}

		if (!isset($props[$propertyName]))
		{
			self::$_invalidStylePropertyAccess[$propertyName] = true;
			return '';
		}

		$property = $props[$propertyName];
		if (!is_array($property))
		{
			// scalar property ...
			if ($propertyComponent)
			{
				// ... with unknown sub component
				self::$_invalidStylePropertyAccess[$propertyName][$propertyComponent] = true;
				return '';
			}
			else
			{
				// ... in total
				return $property;
			}
		}

		// css properties now
		if ($propertyComponent)
		{
			if (isset($property[$propertyComponent]))
			{
				return $property[$propertyComponent];
			}
			else if (preg_match('#^border-.*-(radius|width|style|color)$#', $propertyComponent, $regexMatch))
			{
				$alternative = 'border-' . $regexMatch[1];
				if (isset($property[$alternative]))
				{
					return $property[$alternative];
				}
			}
			else if (preg_match('#^(padding|margin)-#', $propertyComponent, $regexMatch))
			{
				$alternative = $regexMatch[1] . '-all';
				if (isset($property[$alternative]))
				{
					return $property[$alternative];
				}
			}

			return '';
		}
		else
		{
			$output = '';
			foreach (array('font', 'background', 'padding', 'margin', 'border', 'extra') AS $component)
			{
				if (isset($property[$component]))
				{
					$output .= $property[$component] . "\n";
				}
			}
			if (isset($property['width']))
			{
				$output .= "width: $property[width];\n";
			}
			if (isset($property['height']))
			{
				$output .= "height: $property[height];\n";
			}

			return $output;
		}
	}

	public static function setStyleProperties(array $properties, $merge = false)
	{
		if ($merge)
		{
			self::$_styleProperties = array_merge(self::$_styleProperties, $properties);
		}
		else
		{
			self::$_styleProperties = $properties;
		}
	}

	public static function getInvalidStylePropertyAccessList()
	{
		return self::$_invalidStylePropertyAccess;
	}

	/**
	 * Resets invalid style property accesses.
	 */
	public static function resetInvalidStylePropertyAccessList()
	{
		self::$_invalidStylePropertyAccess = array();
	}

	public static function getHiddenInputsFromUrl($url)
	{
		$converted = self::convertUrlToActionAndNamedParams($url);
		return self::getHiddenInputs($converted['params']);
	}

	public static function getHiddenInputs(array $params)
	{
		$inputs = '';
		foreach ($params AS $name => $value)
		{
			$inputs .= '<input type="hidden" name="' . htmlspecialchars($name)
				. '" value="' . htmlspecialchars($value) . '" />' . "\n";
		}

		return $inputs;
	}

	public static function getUserHref(array $user, array $attributes = array())
	{
		if (empty($attributes['href']))
		{
			if ($user['user_id'])
			{
				$href = self::link('members', $user);
			}
			else
			{
				$href = '';
			}
		}
		else
		{
			$href = htmlspecialchars($attributes['href']);
		}

		return ($href ? " href=\"{$href}\"" : '');
	}

	public static function getAttributes(array $attributes = array())
	{
		$attributesHtml = '';

		foreach ($attributes AS $attribute => $value)
		{
			$attributesHtml .= ' ' . htmlspecialchars($attribute) . "=\"{$value}\"";
		}

		return $attributesHtml;
	}

	/**
	 * Converts a URL that may have a route/action and named params to a form
	 * action (script name, things before query string) and named params. A route
	 * in the query string is converted to a named param "_".
	 *
	 * @param string $url
	 *
	 * @return array Format: [action] => form action, [params] => key-value param pairs
	 */
	public static function convertUrlToActionAndNamedParams($url)
	{
		$params = array();

		if (($questPos = strpos($url, '?')) !== false)
		{
			$queryString = htmlspecialchars_decode(substr($url, $questPos + 1));
			$url = substr($url, 0, $questPos);

			if (preg_match('/^([^=&]*)(&|$)/', $queryString, $queryStringUrl))
			{
				$route = $queryStringUrl[1];
				$queryString = substr($queryString, strlen($queryStringUrl[0]));
			}
			else
			{
				$route = '';
			}


			if ($route !== '')
			{
				$params['_'] = $route;
			}

			if ($queryString)
			{
				$params = array_merge($params, XenForo_Application::parseQueryString($queryString));
			}
		}

		return array(
			'action' => htmlspecialchars($url),
			'params' => $params
		);
	}

	// -------------------------------------------------
	// Username link method

	/**
	 * Produces a <a href="members/username.123" class="username">Username</a> snippet
	 *
	 * @param array $user
	 * @param string $username Used to override the username from $user
	 * @param boolean Render rich username markup
	 * @param array Attributes for the <a> tag
	 *
	 * @return string
	 */
	public static function helperUserNameHtml(array $user, $username = '', $rich = false, array $attributes = array())
	{
		if ($username == '')
		{
			$username = htmlspecialchars($user['username']);
		}

		if ($rich)
		{
			$username = self::callHelper('richusername', array($user, $username));
		}

		$href = self::getUserHref($user, $attributes);

		$class = (empty($attributes['class']) ? '' : ' ' . htmlspecialchars($attributes['class']));

		unset($attributes['href'], $attributes['class']);

		$attribs = self::getAttributes($attributes);

		return "<a{$href} class=\"username{$class}\"{$attribs}>{$username}</a>";
	}

	public static function helperUserName(array $user, $class = '', $rich = false)
	{
		return self::callHelper('usernamehtml', array($user, '', $rich, array('class' => $class)));
	}

	// -------------------------------------------------
	// Avatar-related methods

	/**
	 * Returns an <a> tag for use as a user avatar
	 *
	 * @param array $user
	 * @param boolean If true, use an <img> tag, otherwise use a block <span> with the avatar as a background image
	 * @param array Extra tag attributes
	 * @param string Additional tag contents (inserted after image element)
	 */
	public static function helperAvatarHtml(array $user, $img, array $attributes = array(), $content = '')
	{
		if (!empty($attributes['size']))
		{
			$size = strtolower($attributes['size']);

			switch ($size)
			{
				case 'l':
				case 'm':
				case 's':
					break;

				default:
					$size = 'm';
			}
		}
		else
		{
			$size = 'm';
		}

		$forceType = (isset($attributes['forcetype']) ? $attributes['forcetype'] : null);

		$canonical = (isset($attributes['canonical']) && self::attributeTrue($attributes['canonical']));

		$src = call_user_func(self::$helperCallbacks['avatar'], $user, $size, $forceType, $canonical);

		$href = self::getUserHref($user, $attributes);
		unset($attributes['href']);

		if ($img)
		{
			$username = htmlspecialchars($user['username']);
			$dimension = XenForo_Model_Avatar::getSizeFromCode($size);

			$image = "<img src=\"{$src}\" width=\"{$dimension}\" height=\"{$dimension}\" alt=\"{$username}\" />";
		}
		else
		{
			$text = (empty($attributes['text']) ? '' : htmlspecialchars($attributes['text']));

			$image = "<span class=\"img {$size}\" style=\"background-image: url('{$src}')\">{$text}</span>";
		}

		$class = (empty($attributes['class']) ? '' : ' ' . htmlspecialchars($attributes['class']));

		unset($attributes['user'], $attributes['size'], $attributes['img'], $attributes['text'], $attributes['class']);

		$attribs = self::getAttributes($attributes);

		if ($content !== '')
		{
			$content = " {$content}";
		}

		return "<a{$href} class=\"avatar Av{$user['user_id']}{$size}{$class}\"{$attribs} data-avatarHtml=\"true\">{$image}{$content}</a>";
	}

	/**
	 * Helper to fetch the URL of a user's avatar.
	 *
	 * @param array $user User info
	 * @param string $size Size code
	 * @param boolean Serve the default gender avatar, even if the user has a custom avatar
	 * @param boolean Serve the full canonical URL
	 *
	 * @return string Path to avatar
	 */
	public static function helperAvatarUrl($user, $size, $forceType = null, $canonical = false)
	{
		if (!is_array($user))
		{
			$user = array();
		}

		if ($forceType)
		{
			switch ($forceType)
			{
				case 'default':
				case 'custom':
					break;

				default:
					$forceType = null;
					break;
			}
		}

		$url = self::getAvatarUrl($user, $size, $forceType);

		if ($canonical)
		{
			$url = XenForo_Link::convertUriToAbsoluteUri($url, true);
		}

		return htmlspecialchars($url);
	}

	/**
	 * Returns an array containing the URLs for each avatar size available for the given user
	 *
	 * @param array $user
	 *
	 * @return array [$sizeCode => $url, $sizeCode => $url...]
	 */
	public static function getAvatarUrls(array $user)
	{
		$urls = array();

		foreach (XenForo_Model_Avatar::getSizes() AS $sizeCode => $maxDimensions)
		{
			$urls[$sizeCode] = self::getAvatarUrl($user, $sizeCode);
		}

		return $urls;
	}

	/**
	 * Returns the URL to the appropriate avatar type for the given user
	 *
	 * @param array $user
	 * @param string $size (s,m,l)
	 * @param string Force 'default' or 'custom' type
	 *
	 * @return string
	 */
	public static function getAvatarUrl(array $user, $size, $forceType = '')
	{
		if (!empty($user['user_id']) && $forceType != 'default')
		{
			if ($user['gravatar'] && $forceType != 'custom')
			{
				return self::_getGravatarUrl($user, $size);
			}
			else if (!empty($user['avatar_date']))
			{
				return self::_getCustomAvatarUrl($user, $size);
			}
		}

		return self::_getDefaultAvatarUrl($user, $size);
	}

	/**
	 * Returns the default gender-specific avatar URL
	 *
	 * @param string $gender - male / female / other
	 * @param string $size (s,m,l)
	 *
	 * @return string
	 */
	protected static function _getDefaultAvatarUrl(array $user, $size)
	{
		if (!isset($user['gender']))
		{
			$user['gender'] = '';
		}

		switch ($user['gender'])
		{
			case 'male':
			case 'female':
				$gender = $user['gender'] . '_';
				break;

			default:
				$gender = '';
				break;
		}

		if (!$imagePath = self::styleProperty('imagePath'))
		{
			$imagePath = 'styles/default';
		}

		return "{$imagePath}/xenforo/avatars/avatar_{$gender}{$size}.png";
	}

	/**
	 * Returns the URL to a user's custom avatar
	 *
	 * @param array $user
	 * @param string $size (s,m,l)
	 *
	 * @return string
	 */
	protected static function _getCustomAvatarUrl(array $user, $size)
	{
		$group = floor($user['user_id'] / 1000);
		return XenForo_Application::$externalDataUrl . "/avatars/$size/$group/$user[user_id].jpg?$user[avatar_date]";
	}

	/**
	 * Returns a Gravatar URL for the user
	 *
	 * @param array $user
	 * @param string|integer $size (s,m,l)
	 * @param string Override default (useful to use '404')
	 */
	protected static function _getGravatarUrl(array $user, $size, $default = '')
	{
		$md5 = md5($user['gravatar']);

		if ($default === '')
		{
			$default = '&d=' . urlencode(XenForo_Application::get('options')->boardUrl . '/' . self::_getDefaultAvatarUrl($user, $size));
		}
		else if (!empty($default))
		{
			$default = '&d=' . urlencode($default);
		}

		if (is_string($size))
		{
			$size = XenForo_Model_Avatar::getSizeFromCode($size);
		}

		return (XenForo_Application::$secure ? 'https://secure' : 'http://www')
			. ".gravatar.com/avatar/{$md5}.jpg?s={$size}{$default}";
	}

	/**
	 * Helper to fetch the CSS rules to crop a user's avatar to their chosen square aspect
	 *
	 * @param array $user
	 */
	public static function helperAvatarCropCss($user)
	{
		if (!is_array($user)                             // not a valid user
			OR empty($user['avatar_date'])               // no custom avatar
			OR !array_key_exists('avatar_crop_x', $user) // no x crop info
			OR !array_key_exists('avatar_crop_y', $user) // no y crop info
			OR !empty($user['gravatar'])                 // using Gravatar, which is always square
		)
		{
			return '';
		}

		$css = '';

		foreach (XenForo_ViewPublic_Helper_User::getAvatarCropCss($user) AS $property => $value)
		{
			$css .= "$property: $value; ";
		}

		return $css;
	}

	public static function helperThreadPrefixGroup($prefixGroupId)
	{
		return self::_getPhraseText('thread_prefix_group_' . $prefixGroupId);
	}

	/**
	 * Helper to display a thread prefix for the specified prefix ID/thread. Can take an array.
	 *
	 * @param integer|array $prefixId Prefix ID or array with key of prefix_id
	 * @param string $outputType Type of output; options are html (marked up), plain (plain text), escaped (plain text escaped)
	 * @param string|null $append Value to append if there is a prefix (eg, a space); if null, defaults to space (html) or dash (plain)
	 *
	 * @return string
	 */
	public static function helperThreadPrefix($prefixId, $outputType = 'html', $append = null)
	{
		if (is_array($prefixId))
		{
			if (!isset($prefixId['prefix_id']))
			{
				return '';
			}

			$prefixId = $prefixId['prefix_id'];
		}

		$prefixId = intval($prefixId);
		if (!$prefixId OR !isset(self::$_threadPrefixes[$prefixId]))
		{
			return '';
		}

		$text = self::_getPhraseText('thread_prefix_' . $prefixId);
		if ($text === '')
		{
			return '';
		}

		switch ($outputType)
		{
			case 'html':
				$text = '<span class="' . htmlspecialchars(self::$_threadPrefixes[$prefixId]) . '">'
					. htmlspecialchars($text) . '</span>';
				if ($append === null)
				{
					$append = ' ';
				}
				break;

			case 'plain':
				break; // ok as is

			default:
				$text = htmlspecialchars($text); // just be safe and escape everything else
		}

		if ($append === null)
		{
			$append = ' - ';
		}

		return $text . $append;
	}

	/**
	 * Fetches the text of the specified phrase
	 *
	 * @param string $phraseName
	 *
	 * @return string
	 */
	protected static function _getPhraseText($phraseName)
	{
		if (self::$_language && isset(self::$_language['phrase_cache']))
		{
			$cache = self::$_language['phrase_cache'];
			return (isset($cache[$phraseName]) ? $cache[$phraseName] : '');
		}
		else
		{
			$phrase = new XenForo_Phrase($phraseName);
			return $phrase->render(false);
		}
	}

	/**
	 * Sets the thread prefixes.
	 *
	 * @param array $prefixes [prefix id] => class name
	 */
	public static function setThreadPrefixes($prefixes)
	{
		self::$_threadPrefixes = $prefixes;
	}

	/**
	 * Encodes the incoming data as JSON
	 *
	 * @param mixed $data
	 *
	 * @return string JSON
	 */
	public static function helperJson($data)
	{
		return XenForo_ViewRenderer_Json::jsonEncodeForOutput($data, false);
	}

	public static function helperIgnoredCss(array $ignoredUsers)
	{
		if (empty($ignoredUsers))
		{
			return '';
		}

		$selectors = array();
		foreach ($ignoredUsers AS $username)
		{
			$selectors[] = '*[data-author="' . htmlspecialchars($username) . '"]';
		}

		return '';//<style id="ignoredUserCss">' . implode(', ', $selectors) . ' { display: none; } </style>';
	}

	/**
	 * Determines whether or not the given user ID / username is being ignored by the visiting user
	 *
	 * @param integer|string $user
	 *
	 * @return boolean
	 */
	public static function helperIsIgnored($user, array &$ignoredNames = array())
	{
		$visitor = XenForo_Visitor::getInstance();

		if (!$visitor['user_id'] OR empty($visitor['ignoredUsers']))
		{
			return false;
		}

		$ignoredUser = self::_getModelFromCache('XenForo_Model_User')->isUserIgnored($visitor->toArray(), $user);

		if ($ignoredUser !== false)
		{
			$ignoredNames[$ignoredUser[0]] = $ignoredUser[1];

			return $ignoredUser;
		}

		return false;
	}

	/**
	 * Returns a string of CSS class names, identifying the current node and all its ancestors.
	 *
	 * @param array $nodeBreadCrumbs
	 * @param array $currentNode
	 *
	 * @return string 'node5 node3 node1'
	 */
	public static function helperNodeClasses(array $nodeBreadCrumbs, array $currentNode = array())
	{
		$nodeClasses = array();

		if (array_key_exists('node_id', $currentNode))
		{
			$nodeClasses[] = "node{$currentNode['node_id']}";
		}

		foreach (array_keys($nodeBreadCrumbs) AS $nodeId)
		{
			$nodeClasses[] = "node{$nodeId}";
		}

		return implode(' ', array_unique($nodeClasses));
	}

	/**
	 * Helper to fetch the title of a custom user field from its ID
	 *
	 * @param string $field
	 *
	 * @return XenForo_Phrase
	 */
	public static function helperUserFieldTitle($fieldId)
	{
		return new XenForo_Phrase(
			self::_getModelFromCache('XenForo_Model_UserField')->getUserFieldTitlePhraseName($fieldId));
	}

	/**
	 * Helper to fetch the HTML value of a custom user field
	 *
	 * @param array|string $field Either the field info array for a field, or just its field_id
	 * @param array $user User to whom the field belongs
	 * @param mixed $fieldValue Value of the field for $user
	 *
	 * @return string|boolean
	 */
	public static function helperUserFieldValue($field, array $user = array(), $fieldValue = null)
	{
		if (empty($user['user_id']))
		{
			return false;
		}

		if (!is_array($field))
		{
			if (empty(self::$_userFieldsInfo))
			{
				self::$_userFieldsInfo = XenForo_Application::get('userFieldsInfo');
			}

			if (!isset(self::$_userFieldsInfo[$field]))
			{
				return false;
			}

			$field = self::$_userFieldsInfo[$field];
		}

		$fieldId = $field['field_id'];

		if (isset(self::$_userFieldsValues[$user['user_id']][$fieldId]))
		{
			return self::$_userFieldsValues[$user['user_id']][$fieldId];
		}

		if (is_null($fieldValue))
		{
			$fieldValue = $field['field_value'];
		}

		$value = XenForo_ViewPublic_Helper_User::getUserFieldValueHtml($field, $fieldValue);

		if (is_array($value))
		{
			$value = implode(', ', $value);
		}

		self::$_userFieldsValues[$user['user_id']][$fieldId] = $value;
		return $value;
	}

	public static function helperJavaScriptUrl($scriptUrl)
	{
		$jsUrl = XenForo_Application::$javaScriptUrl;

		switch (XenForo_Application::get('options')->uncompressedJs)
		{
			case 1:
				return str_replace("$jsUrl/xenforo/", "$jsUrl/xenforo/full/", $scriptUrl);
			case 2:
				return str_replace("$jsUrl/xenforo/", "$jsUrl/xenforo/min/", $scriptUrl);
		}

		return $scriptUrl;
	}

	/**
	 * Helper to determine if an attribute value should be treated as true
	 *
	 * @param mixed $attribute
	 *
	 * @return boolean
	 */
	public static function attributeTrue($attribute)
	{
		if (!empty($attribute))
		{
			switch ((string)strtolower($attribute))
			{
				case 'on':
				case 'yes':
				case 'true':
				case '1':
					return true;
			}
		}

		return false;
	}

	/**
	 * Adds a CSS class to a class list if it is not already there, otherwise removes it
	 *
	 * @param string Class to add / remove
	 * @param string Existing class defininition
	 *
	 * @return string
	 */
	public static function toggleClass($class, &$classList = '')
	{
		if ($classList == '')
		{
			// empty class list - add
			$classList = $class;
		}
		else if ($classList == $class)
		{
			// class list contains only class - remove
			$classList = '';
		}
		else if (strpos(" $classList ", " $class ") === false)
		{
			// class list does not include class - add
			$classList = "$classList $class";
		}
		else
		{
			// class list includes class - remove
			$classList = array_keys(preg_split('/\s+/', $classList, -1, PREG_SPLIT_NO_EMPTY));
			unset($classList[$class]);

			$classList = implode($classList, ' ');
		}

		return $classList;
	}

	/**
	 * Adds a CSS class to a class list if it's not already there
	 *
	 * @param string Class to add / remove
	 * @param string Existing class defininition
	 *
	 * @return string
	 */
	public static function addClass($class, &$classList = '')
	{
		if ($classList == '')
		{
			$classList = $class;
		}
		else if (strpos(" $classList ", " $class ") === false)
		{
			$classList = "$classList $class";
		}

		return $classList;
	}

	/**
	 * Fetches a model object from the local cache
	 *
	 * @param string $modelName
	 *
	 * @return XenForo_Model
	 */
	protected static function _getModelFromCache($modelName)
	{
		if (!isset(self::$_modelCache[$modelName]))
		{
			self::$_modelCache[$modelName] = XenForo_Model::create($modelName);
		}

		return self::$_modelCache[$modelName];
	}

	public static function numberFormat($number, $precision = 0, array $language = null)
	{
		if (!$language)
		{
			$language = self::$_language;
		}

		if (!$language)
		{
			$decimalSep = '.';
			$thousandsSep = ',';
		}
		else
		{
			$decimalSep = $language['decimal_point'];
			$thousandsSep = $language['thousands_separator'];
		}

		if ($precision === 'size')
		{
			if ($number >= 1048576) // 1 MB
			{
				$number = number_format($number / 1048576, 1, $decimalSep, $thousandsSep);
				$unit = ' MB';
				$phrase = 'x_mb';
			}
			else if ($number >= 1024) // 1 KB
			{
				$number = number_format($number / 1024, 1, $decimalSep, $thousandsSep);
				$unit = ' KB';
				$phrase = 'x_kb';
			}
			else
			{
				$number = number_format($number, 1, $decimalSep, $thousandsSep);
				$unit = ' bytes';
				$phrase = 'x_bytes';
			}

			// return $number, not $number.0 when the decimal is 0.
			if (substr($number, -2) == $decimalSep . '0')
			{
				$number = substr($number, 0, -2);
			}

			if (!$language OR !isset($language['phrase_cache'][$phrase]))
			{
				return $number . $unit;
			}
			else
			{
				return str_replace('{size}', $number, $language['phrase_cache'][$phrase]);
			}
		}
		else
		{
			return number_format($number, $precision, $decimalSep, $thousandsSep);
		}
	}
}