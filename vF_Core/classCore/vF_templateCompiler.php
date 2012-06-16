<?php 
vF_Check();


class vF_templateCompiler
{
	protected static $_templateCache = array();
	protected static $_compilerType = 'public';
	protected $_text = '';
	protected $_tagHandlers = array();
	protected $_functionHandlers = array();
	protected $_options = array(
		'varEscape' => 'htmlspecialchars',
		'allowRawStatements' => true,
		'disableVarMap' => false
	);
	protected $_outputVar = 'vF_output';
	protected $_uniqueVarCount = 0;
	protected $_uniqueVarPrefix = 'vF_compilerVar';
	protected $_followExternal = true;

	protected $_styleId = 0;
	protected $_languageId = 0;
	protected $_title = '';
	protected $_includedTemplates = array();

	protected static $_phraseCache = array();
	protected $_includedPhrases = array();
	protected $_enableDynamicPhraseLoad = true;

	protected $_lineNumber = 0;
	protected $_variableMap = array();

	public function __construct( $text = '' )
	{
		if ( $text !== '' )
		{
			$this->addText( $text );
		}

		$this->_setupDefaults();
	}

	public function addFunctionHandlers( array $functions )
	{
		foreach ( $functions AS $function => $handler )
		{
			$this->addFunctionHandler($function, $handler);
		}

		return $this;
	}

	public function addTagHandler($tag, XenForo_Template_Compiler_Tag_Interface $handler)
	{
		$this->_tagHandlers[strtolower($tag)] = $handler;
		return $this;
	}

	public function setDefaultOptions( array $options )
	{
		$this->_options = array_merge( $this->_options, $options );
		return $this;
	}

	public function getDefaultOptions()
	{
		return $this->_options;
	}

	public function addText( $text )
	{
		$this->_text = strval($text);
	}

	public function addTagHandlers(array $tags)
	{
		foreach ($tags AS $tag => $handler)
		{
			$this->addTagHandler($tag, $handler);
		}

		return $this;
	}

	public function addFunctionHandler($function, XenForo_Template_Compiler_Function_Interface $handler)
	{
		$this->_functionHandlers[strtolower($function)] = $handler;
		return $this;
	}

	public function getOutputVar()
	{
		return $this->_outputVar;
	}

	public function setOutputVar($_outputVar)
	{
		$this->_outputVar = strval($_outputVar);
	}

	public function getUniqueVar()
	{
		return $this->_uniqueVarPrefix . ++$this->_uniqueVarCount;
	}

	public function compile($title = '', $styleId = 0, $languageId = 0)
	{
		$segments = $this->lexAndParse();
		return $this->compileParsed($segments, $title, $styleId, $languageId);
	}

	public function compileParsed($segments, $title, $styleId, $languageId)
	{
		$this->_title = $title;
		$this->_styleId = $styleId;
		$this->_languageId = $languageId;
		$this->_includedTemplates = array();

		if (!is_string($segments) && !is_array($segments))
		{
			throw vF_error::getInstane()->exception('Got unexpected, non-string/non-array segments for compilation.');
		}

		$this->_findAndLoadPhrasesFromSegments($segments);

		$statements = $this->compileSegments($segments);
		return $this->getOutputVarInitializer() . $statements->getFullStatements($this->_outputVar);
	}

	public function compileParsedPlainText($segments, $title, $styleId, $languageId)
	{
		$existingOptions = $this->getDefaultOptions();
		$this->setDefaultOptions(array('varEscape' => false));

		$compiled = $this->compileParsed($segments, $title, $styleId, $languageId);

		$this->setDefaultOptions($existingOptions);
		return $compiled;
	}

	public function compileIntoVariable($segments, &$var = '', array $options = null, $generateVar = true)
	{
		if ($generateVar)
		{
			$var = $this->getUniqueVar();
		}

		$oldOutputVar = $this->getOutputVar();
		$this->setOutputVar($var);

		$output =
			$this->getOutputVarInitializer()
			. $this->compileSegments($segments, $options)->getFullStatements($var);

		$this->setOutputVar($oldOutputVar);

		return $output;
	}

	public function getOutputVarInitializer()
	{
		return '$' . $this->_outputVar . " = '';\n";
	}

	public function compileAndCombineSegments($segments, array $options = null)
	{
		if (!is_array($options))
		{
			$options = $this->_options;
		}
		$options = array_merge($options, array('allowRawStatements' => false));

		return $this->compileSegments($segments, $options)->getPartialStatement();
	}

	public function lexAndParse()
	{
		$lexer = new vF_templateCompilerLexer( $this->_text );
		$parser = new vF_templateCompilerParser();

		try
		{
			while ($lexer->yylex() !== false)
			{
				$parser->doParse($lexer->match[0], $lexer->match[1]);
				$parser->setLineNumber($lexer->line); // if this is before the doParse, it seems to give wrong numbers
			}
			$parser->doParse(0, 0);
		}
		catch (Exception $e)
		{
			// from lexer, can't use the base exception, re-throw
			throw new vF_templateCompilerException(vF_language::getInstance()->phrase('line_x_template_syntax_error', array('number' => $lexer->line)), true);
		}
		// vF_templateCompilerException: ok -- no need to catch and rethrow

		return $parser->getOutput();
	}

	public function compileSegments($segments, array $options = null)
	{
		$segments = $this->prepareSegmentsForIteration($segments);

		if (!is_array($options))
		{
			$options = $this->_options;
		}

		$statement = $this->getNewStatementCollection();

		foreach ($segments AS $segment)
		{
			$compiled = $this->compileSegment($segment, $options);
			if ($compiled !== '' && $compiled !== null)
			{
				$statement->addStatement($compiled);
			}
		}

		return $statement;
	}

	public function prepareSegmentsForIteration($segments)
	{
		if (!is_array($segments))
		{
			// likely a string (simple literal)
			$segments = array($segments);
		}
		else if (isset($segments['type']))
		{
			// a simple curly var/function
			$segments = array($segments);
		}

		return $segments;
	}

	public function compileSegment($segment, array $options)
	{
		if (is_string($segment))
		{
			$this->setLastVistedSegment($segment);
			return $this->compilePlainText($segment, $options);
		}
		else if (is_array($segment) && isset($segment['type']))
		{
			$this->setLastVistedSegment($segment);

			switch ($segment['type'])
			{
				case 'TAG':
					return $this->compileTag(
						$segment['name'], $segment['attributes'],
						isset($segment['children']) ? $segment['children'] : array(),
						$options
					);

				case 'CURLY_VAR':
					return $this->compileVar($segment['name'], $segment['keys'], $options);

				case 'CURLY_FUNCTION':
					return $this->compileFunction($segment['name'], $segment['arguments'], $options);
			}
		}
		else if ($segment === null)
		{
			return '';
		}

		throw $this->getNewCompilerException(vF_language::getInstance()->phrase('internal_compiler_error_unknown_segment_type'));
	}

	public function setLastVistedSegment($segment)
	{
		if (is_array($segment) && isset($segment['type']))
		{
			if (!empty($segment['line']))
			{
				$this->_lineNumber = $segment['line'];
			}
		}
	}

	public function escapeSingleQuotedString($string)
	{
		return str_replace(array('\\', "'"), array('\\\\', "\'"), $string);
	}

	public function compilePlainText($text, array $options)
	{
		return "'" . $this->escapeSingleQuotedString($text) . "'";
	}

	public function compileTag($tag, array $attributes, array $children, array $options)
	{
		$tag = strtolower($tag);

		if (isset($this->_tagHandlers[$tag]))
		{
			return $this->_tagHandlers[$tag]->compile($this, $tag, $attributes, $children, $options);
		}
		else
		{
			throw $this->getNewCompilerException(vF_language::getInstance()->phrase('unknown_tag_x', array('tag' => $tag)));
		}
	}

	public function compileVar($name, $keys, array $options)
	{
		$name = $this->resolveMappedVariable($name, $options);

		$varName = '$' . $name;

		if (!empty($keys) && is_array($keys))
		{
			foreach ($keys AS $key)
			{
				if (is_string($key))
				{
					$varName .= "['" . $this->escapeSingleQuotedString($key) . "']";
				}
				else if (isset($key['type']) && $key['type'] == 'CURLY_VAR')
				{
					$varName .= '[' . $this->compileVar($key['name'], $key['keys'], array_merge($options, array('varEscape' => false))) . ']';
				}
			}
		}

		if (!empty($options['varEscape']))
		{
			return $options['varEscape'] . '(' . $varName . ')';
		}
		else
		{
			return $varName;
		}
	}

	public function compileFunction($function, array $arguments, array $options)
	{
		$function = strtolower($function);

		if (isset($this->_functionHandlers[$function]))
		{
			return $this->_functionHandlers[$function]->compile($this, $function, $arguments, $options);
		}
		else
		{
			throw $this->getNewCompilerException(vF_language::getInstance()->phrase('unknown_function_x', array('function' => $function)));
		}
	}

	public function compileVarRef($varRef, array $options)
	{
		$replacements = array();

		if (is_array($varRef))
		{
			if (!isset($varRef[0]))
			{
				$varRef = array($varRef);
			}

			$newVarRef = '';
			foreach ($varRef AS $segment)
			{
				if (is_string($segment))
				{
					$newVarRef .= $segment;
				}
				else
				{
					$newVarRef .= '?';
					$replacements[] = $segment;
				}
			}

			$varRef = $newVarRef;
		}

		$parts = explode('.', $varRef);

		$variable = array_shift($parts);
		if ($variable == '?')
		{
			$variable = $this->compileSegment(array_shift($replacements), array_merge($options, array('varEscape' => false)));
			if (!preg_match('#^\$[a-zA-Z_]#', $variable))
			{
				throw $this->getNewCompilerException(vF_language::getInstance()->phrase('invalid_variable_reference'));
			}
		}
		else if (!preg_match('#^\$([a-zA-Z_][a-zA-Z0-9_]*)$#', $variable))
		{
			throw $this->getNewCompilerException(vF_language::getInstance()->phrase('invalid_variable_reference'));
		}

		$keys = array();
		foreach ($parts AS $part)
		{
			if ($part == '?')
			{
				$part = $this->compileSegment(array_shift($replacements), array_merge($options, array('varEscape' => false)));
			}
			else if ($part === '' || strpos($part, '?') !== false)
			{
				// empty key or simply contains a replacement
				throw $this->getNewCompilerException(vF_language::getInstance()->phrase('invalid_variable_reference'));
			}
			else
			{
				$part = "'" . $this->escapeSingleQuotedString($part) . "'";
			}

			$keys[] = '[' . $part . ']';
		}

		$variable = '$' . $this->resolveMappedVariable(substr($variable, 1), $options);

		return $variable . implode('', $keys);
	}

	function parseNamedArguments(array $arguments)
	{
		$params = array();
		foreach ($arguments AS $argument)
		{
			if (!isset($argument[0]) || !is_string($argument[0]) || !preg_match('#^([a-z0-9_\.]+)=#i', $argument[0], $match))
			{
				throw $this->getNewCompilerException(vF_language::getInstance()->phrase('named_parameter_not_specified_correctly'));
			}

			$name = $match[1];

			$nameRemoved = substr($argument[0], strlen($match[0]));
			if ($nameRemoved === false)
			{
				// we ate the whole string, remove the argument
				unset($argument[0]);
			}
			else
			{
				$argument[0] = $nameRemoved;
			}

			$nameParts = explode('.', $name);
			if (count($nameParts) > 1)
			{
				$pointer =& $params;
				foreach ($nameParts AS $namePart)
				{
					if (!isset($pointer[$namePart]))
					{
						$pointer[$namePart] = array();
					}
					$pointer =& $pointer[$namePart];
				}
				$pointer = $argument;
			}
			else
			{
				$params[$name] = $argument;
			}
		}

		return $params;
	}

	public function compileNamedParams(array $params, array $options, array $compileAsCondition = array())
	{
		$compiled = array();
		foreach ($params AS $name => $value)
		{
			if (in_array($name, $compileAsCondition))
			{
				$compiled[$name] = $this->parseConditionExpression($value, $options);
			}
			else
			{
				if (is_array($value))
				{
					// if an associative array, not a list of segments
					reset($value);
					list($key, ) = each($value);
					if (is_string($key))
					{
						$compiled[$name] = $this->compileNamedParams($value, $options);
						continue;
					}
				}

				$compiled[$name] = $this->compileAndCombineSegments($value, $options);
			}
		}

		return $compiled;
	}

	public function buildNamedParamCode(array $compiled)
	{
		if (!$compiled)
		{
			return 'array()';
		}

		$output = "array(\n";
		$i = 0;
		foreach ($compiled AS $name => $value)
		{
			if (is_array($value))
			{
				$value = $this->buildNamedParamCode($value);
			}

			if ($i > 0)
			{
				$output .= ",\n";
			}
			$output .= "'" . $this->escapeSingleQuotedString($name) . "' => $value";

			$i++;
		}

		$output .= "\n)";

		return $output;
	}

	public function getNamedParamsAsPhpCode(array $params, array $options, array $compileAsCondition = array())
	{
		$compiled = $this->compileNamedParams($params, $options, $compileAsCondition);
		return $this->buildNamedParamCode($compiled);
	}

	public function getNewRawStatement($statement = '')
	{
		return new vF_templateCompilerStatementRaw( $statement );
	}

	public function getNewStatementCollection()
	{
		return new vF_templateCompilerStatementCollection();
	}

	public function getNewCompilerException( $message = '', $segment = false )
	{
		if (is_array($segment) AND !empty($segment['line']))
		{
			$lineNumber = $segment['line'];
		}
		else if (is_int($segment) AND !empty( $segment ) )
		{
			$lineNumber = $segment;
		}
		else
		{
			$lineNumber = $this->_lineNumber;
		}

		if( $lineNumber )
		{
			$message = vF_language::getInstance()->phrase('line_x', array('line' => $lineNumber)) . ': ' . $message;
		}

		$e = new vF_templateCompilerException( $message, true );
		$e->setLineNumber( $lineNumber );

		return $e;
	}

	public function getNewCompilerArgumentException( $segment = false )
	{
		return $this->getNewCompilerException(vF_language::getInstance()->phrase('incorrect_arguments'), $segment);
	}

	public function isSegmentNamedTag( $segment, $tagName )
	{
		return ( is_array( $segment ) AND isset( $segment['type'] ) AND $segment['type'] == 'TAG' AND $segment['name'] == $tagName );
	}

	public function parseConditionExpression( $origCondition, array $options )
	{
		$placeholders = array();
		$placeholderChar = "\x1A"; // substitute character in ascii

		if ($origCondition === '')
		{
			throw $this->getNewCompilerException(vF_language::getInstance()->phrase('invalid_condition_expression'));
		}

		if( is_string( $origCondition ) )
		{
			$condition = $origCondition;
		}
		else
		{
			$condition = '';
			foreach( $this->prepareSegmentsForIteration( $origCondition ) AS $segment )
			{
				if ( is_string( $segment ) )
				{
					if( strpos( $segment, $placeholderChar ) !== false )
					{
						throw $this->getNewCompilerException(vF_language::getInstance()->phrase('invalid_condition_expression'));
					}

					$condition .= $segment;
				}
				else
				{
					$condition .= $placeholderChar;
					$placeholders[] = $this->compileSegment( $segment, array_merge($options, array('varEscape' => false ) ) );
				}
			}
		}

		return $this->_parseConditionExpression($condition, $placeholders);
	}

	protected function _parseConditionExpression( &$expression, array &$placeholders, $internalExpression = false, $isFunction = false )
	{
		if( $internalExpression AND $isFunction AND strlen( $expression ) > 0 AND $expression[0] == ')' )
		{
			$expression = substr( $expression, 1 );
			return '()';
		}

		$state = 'value';
		$endState = 'operator';

		$compiled = '';

		$allowedFunctions = 'is_array|is_object|is_string|isset|empty'
			. '|array|array_key_exists|count|in_array|array_search'
			. '|preg_match|preg_match_all|strpos|stripos|strlen'
			. '|ceil|floor|round|max|min|mt_rand|rand';

		do
		{
			$eatChars = 0;

			if ($state == 'value')
			{
				if (preg_match('#^\s+#', $expression, $match))
				{
					// ignore whitespace
					$eatChars = strlen($match[0]);
				}
				else if ($expression[0] == "\x1A")
				{
					$compiled .= array_shift($placeholders);
					$state = 'operator';
					$eatChars = 1;
				}
				else if ($expression[0] == '(')
				{
					$expression = substr($expression, 1);
					$compiled .= $this->_parseConditionExpression($expression, $placeholders, true);
					$state = 'operator';
					continue; // not eating anything, so must continue
				}
				else if (preg_match('#^(\-|!)#', $expression, $match))
				{
					$compiled .= $match[0];
					$state = 'value'; // we still need a value after this, simply modifies the following value
					$eatChars = strlen($match[0]);
				}
				else if (preg_match('#^(\d+(\.\d+)?|true|false|null)#', $expression, $match))
				{
					$compiled .= $match[0];
					$state = 'operator';
					$eatChars = strlen($match[0]);
				}
				else if (preg_match('#^(' . $allowedFunctions . ')\(#i', $expression, $match))
				{
					$expression = substr($expression, strlen($match[0]));
					$compiled .= $match[1] . $this->_parseConditionExpression($expression, $placeholders, true, true);
					$state = 'operator';
					continue; // not eating anything, so must continue
				}
				else if (preg_match('#^(\'|")#', $expression, $match))
				{
					$quoteClosePos = strpos($expression, $match[0], 1); // skip initial
					if ($quoteClosePos === false)
					{
						throw $this->getNewCompilerException(vF_language::getInstance()->phrase('invalid_condition_expression'));
					}

					$quoted = substr($expression, 1, $quoteClosePos - 1);

					$string = array();
					$i = 0;
					foreach (explode("\x1A", $quoted) AS $quotedPart)
					{
						if ($i % 2 == 1)
						{
							// odd parts have a ? before them
							$string[] = array_shift($placeholders);
						}

						if ($quotedPart !== '')
						{
							$string[] = "'" . $this->escapeSingleQuotedString($quotedPart) . "'";
						}

						$i++;
					}

					if (!$string)
					{
						$string[] = "''";
					}

					$compiled .= '(' . implode(' . ', $string) . ')';

					$eatChars = strlen($quoted) + 2; // 2 = quotes on either side
					$state = 'operator';
				}
			}
			elseif( $state == 'operator' )
			{
				if (preg_match('#^\s+#', $expression, $match))
				{
					// ignore whitespace
					$eatChars = strlen($match[0]);
				}
				elseif( preg_match('#^(\*|\+|\-|/|%|===|==|!==|!=|>=|<=|<|>|\|\||AND|and|or|xor|&|\|)#i', $expression, $match ) )
				{
					$eatChars = strlen($match[0]);
					$compiled .= " $match[0] ";
					$state = 'value';
				}
				elseif( $expression[0] == ')' AND $internalExpression )
				{
					// eat and return successfully
					$eatChars = 1;
					$state = false;
				}
				elseif( $expression[0] == ',' AND $isFunction )
				{
					$eatChars = 1;
					$compiled .= ", ";
					$state = 'value';
				}
			}

			if( $eatChars )
			{
				$expression = substr($expression, $eatChars);
			}
			else
			{
				// prevent infinite loops -- if you want to avoid this, use "continue"
				throw $this->getNewCompilerException(vF_language::getInstance()->phrase('invalid_condition_expression'));
			}
		}
		while ($state !== false AND $expression !== '' AND $expression !== false);

		if ($state != $endState AND $state !== false)
		{
			// operator is the end state -- means we're expecting an operator, so it can be anything
			throw $this->getNewCompilerException(vF_language::getInstance()->phrase('invalid_condition_expression'));
		}

		return "($compiled)";
	}

	public function getArgumentLiteralValue($argument)
	{
		if (is_string($argument))
		{
			return $argument;
		}
		else if (is_array($argument) AND sizeof($argument) == 1 AND is_string($argument[0]))
		{
			return $argument[0];
		}
		else
		{
			return false;
		}
	}

	public function getNamedAttributes(array $attributes, array $wantedAttributes)
	{
		$output = array();
		foreach ($wantedAttributes AS $wanted)
		{
			if (isset($attributes[$wanted]))
			{
				$output[$wanted] = $attributes[$wanted];
			}
		}

		return $output;
	}

	public function setFollowExternal($value)
	{
		$this->_followExternal = (bool)$value;
	}

	public function setLineNumber($lineNumber)
	{
		$this->_lineNumber = intval($lineNumber);
	}

	public function getLineNumber()
	{
		return $this->_lineNumber;
	}

	public function mergePhraseCache(array $phraseData)
	{
		foreach ($phraseData AS $languageId => $phrases)
		{
			if (!is_array($phrases))
			{
				continue;
			}

			if (isset(self::$_phraseCache[$languageId]))
			{
				self::$_phraseCache[$languageId] = array_merge(self::$_phraseCache[$languageId], $phrases);
			}
			else
			{
				self::$_phraseCache[$languageId] = $phrases;
			}
		}
	}

	public static function resetPhraseCache()
	{
		self::$_phraseCache = array();
	}

	public function getPhraseValue($title)
	{
		if (!$this->_followExternal)
		{
			return false;
		}

		$this->_includedPhrases[$title] = true;

		if (isset(self::$_phraseCache[$this->_languageId][$title]))
		{
			return self::$_phraseCache[$this->_languageId][$title];
		}
		else
		{
			return false;
		}
	}

	public function disableDynamicPhraseLoad()
	{
		$this->_enableDynamicPhraseLoad = false;
	}

	public function getIncludedPhrases()
	{
		return array_keys($this->_includedPhrases);
	}

	public function includeParsedTemplate($title)
	{
		if ($title == $this->_title)
		{
			throw $this->getNewCompilerException(vF_language::getInstance()->phrase('circular_reference_found_in_template_includes'));
		}

		if (!$this->_followExternal)
		{
			return '';
		}

		if (!isset(self::$_templateCache[$this->getCompilerType()][$this->_styleId][$title]))
		{
			self::$_templateCache[$this->getCompilerType()][$this->_styleId][$title] = $this->_getParsedTemplateFromModel($title, $this->_styleId);
		}

		$info = self::$_templateCache[$this->getCompilerType()][$this->_styleId][$title];
		if (is_array($info))
		{
			if (empty($this->_includedTemplates[$info['id']]))
			{
				// cache phrases for this template as we haven't included it
				$this->_findAndLoadPhrasesFromSegments($info['data']);
			}

			$this->_includedTemplates[$info['id']] = true;
			return $info['data'];
		}
		else
		{
			return '';
		}
	}

	protected function _setupDefaults()
	{
		$this->addFunctionHandlers(array(
			'raw'       => new vF_templateCompilerRaw(),
			'escape'    => new vF_templateCompilerEscape(),
			'urlencode' => new vF_templateCompilerUrlEncode(),
			'jsescape'  => new vF_templateCompilerJsEscape(),

			'phrase'    => new vF_templateCompilerPhrase(),
			'property'  => new vF_templateCompilerProperty(),
			'pagenav'   => new vF_templateCompilerPageNav(),

			'if'        => new vF_templateCompilerIf(),
			'checked'   => new vF_templateCompilerCheckedSelected(),
			'selected'  => new vF_templateCompilerCheckedSelected(),

			'date'      => new vF_templateCompilerDateTime(),
			'time'      => new vF_templateCompilerDateTime(),
			'datetime'  => new vF_templateCompilerDateTime(),

			'number'    => new vF_templateCompilerNumber(),

			'link'      => new vF_templateCompilerLink(),
			'adminlink' => new vF_templateCompilerLink(),

			'calc'      => new vF_templateCompilerCalc(),
			'array'     => new vF_templateCompilerArray(),
			'count'     => new vF_templateCompilerCount(),
			'helper'    => new vF_templateCompilerHelper(),
			'string'    => new vF_templateCompilerString(),
		));

		$this->addTagHandlers(array(
			'foreach'      => new vF_templateCompilerTagForeach(),

			'if'           => new vF_templateCompilerTagIf(),
			'elseif'       => new vF_templateCompilerTagIf(),
			'else'         => new vF_templateCompilerTagIf(),
			'contentcheck' => new vF_templateCompilerTagIf(),

			'navigation'   => new vF_templateCompilerTagNavigation(),
			'breadcrumb'   => new vF_templateCompilerTagNavigation(),

			'title'        => new vF_templateCompilerTagTitle(),
			'description'  => new vF_templateCompilerTagDescription(),
			'h1'           => new vF_templateCompilerTagH1(),
			'sidebar'      => new vF_templateCompilerTagSidebar(),
			'topctrl'      => new vF_templateCompilerTagTopCtrl(),
			'container'    => new vF_templateCompilerTagContainer(),

			'require'      => new vF_templateCompilerTagRequire(),
			'include'      => new vF_templateCompilerTagInclude(),
			'edithint'     => new vF_templateCompilerTagEditHint(),
			'set'          => new vF_templateCompilerTagSet(),
			'hook'         => new vF_templateCompilerTagHook(),

			'formaction'   => new vF_templateCompilerTagFormAction(),

			'datetime'     => new vF_templateCompilerTagDateTime(),
			'avatar'       => new vF_templateCompilerTagAvatar(),
			'username'     => new vF_templateCompilerTagUsername(),
			'likes'        => new vF_templateCompilerTagLikes(),
			'follow'       => new vF_templateCompilerTagFollow(),
			'pagenav'      => new vF_templateCompilerTagPageNav(),
		));
	}

	protected function _findAndLoadPhrasesFromSegments($segments)
	{
		if (!$this->_enableDynamicPhraseLoad)
		{
			return;
		}

		$phrasesUsed = $this->identifyPhrasesInParsedTemplate($segments);
		foreach ($phrasesUsed AS $key => $title)
		{
			if (isset(self::$_phraseCache[$this->_languageId][$title]))
			{
				unset($phrasesUsed[$key]);
			}
		}

		if ($phrasesUsed)
		{
			$phraseData = vF_language::getInstance()->getEffectivePhraseValuesInAllLanguages( $phrasesUsed );
			$this->mergePhraseCache( $phraseData );
		}
	}

	protected function _getParsedTemplateFromModel($title, $styleId)
	{
		$template = vF_themes::getinstance()->getEffectiveTemplateByTitle($title, $styleId);
		if ( isset( $template['template_parsed'] ) )
		{
			return array(
				'id' => $template['template_map_id'],
				'data' => unserialize($template['template_parsed'])
			);
		}
		else
		{
			return false;
		}
	}

	public static function setTemplateCache(array $templates, $styleId = 0)
	{
		self::_setTemplateCache($templates, $styleId, self::$_compilerType);
	}

	protected static function _setTemplateCache(array $templates, $styleId, $compilerType)
	{
		if (empty(self::$_templateCache[$compilerType][$styleId]))
		{
			self::$_templateCache[$compilerType][$styleId] = $templates;
		}
		else
		{
			self::$_templateCache[$compilerType][$styleId] = array_merge(self::$_templateCache[$compilerType][$styleId], $templates);
		}

	}

	public static function resetTemplateCache($styleId = true)
	{
		self::_resetTemplateCache($styleId, self::$_compilerType);
	}

	protected static function _resetTemplateCache($styleId, $compilerType)
	{
		if ($styleId === true)
		{
			self::$_templateCache[$compilerType] = array();
		}
		else
		{
			self::$_templateCache[$compilerType][$styleId] = array();
		}
	}

	public static function removeTemplateFromCache($title)
	{
		self::_removeTemplateFromCache($title, self::$_compilerType);
	}

	protected static function _removeTemplateFromCache($title, $compilerType)
	{
		if( !$title OR !isset(self::$_templateCache[$compilerType]))
		{
			return;
		}

		foreach( self::$_templateCache[$compilerType] AS $styleId => $style)
		{
			if( isset($style[$title]))
			{
				unset(self::$_templateCache[$compilerType][$styleId][$title]);
			}
		}
	}

	public function getIncludedTemplates()
	{
		return array_keys($this->_includedTemplates);
	}

	public function getCompilerType()
	{
		return self::$_compilerType;
	}

	public function identifyPhrasesInParsedTemplate($segments)
	{
		$phrases = $this->_identifyPhrasesInSegments($segments);
		return array_unique($phrases);
	}

	protected function _identifyPhrasesInSegments($segments)
	{
		$phrases = array();

		foreach( $this->prepareSegmentsForIteration($segments) AS $segment)
		{
			if( !is_array( $segment ) OR !isset( $segment['type'] ) )
			{
				continue;
			}

			switch ($segment['type'])
			{
				case 'TAG':
					$phrases = array_merge( $phrases, $this->_identifyPhrasesInSegments( $segment['children'] ) );

					foreach( $segment['attributes'] AS $attribute)
					{
						$phrases = array_merge( $phrases, $this->_identifyPhrasesInSegments($attribute));
					}
					break;

				case 'CURLY_FUNCTION':
					if( $segment['name'] == 'phrase' AND isset($segment['arguments'][0]))
					{
						$literalValue = $this->getArgumentLiteralValue($segment['arguments'][0]);
						if( $literalValue !== false)
						{
							$phrases[] = $literalValue;
						}
					}

					foreach( $segment['arguments'] AS $argument)
					{
						$phrases = array_merge($phrases, $this->_identifyPhrasesInSegments($argument));
					}
			}
		}

		return $phrases;
	}

	public function resolveMappedVariable($name, array $options)
	{
		if( !empty( $options['disableVarMap'] ) )
		{
			return $name;
		}

		$visited = array(); // loop protection

		while( isset( $this->_variableMap[$name] ) AND !isset( $visited[$name] ) )
		{
			$visited[$name] = true;
			$name = $this->_variableMap[$name];
		}

		return $name;
	}

	public function getVariableMap()
	{
		return $this->_variableMap;
	}

	public function setVariableMap(array $map, $merge = false)
	{
		if( $merge)
		{
			if( $map)
			{
				$this->_variableMap = array_merge($this->_variableMap, $map);
			}
		}
		else
		{
			$this->_variableMap = $map;
		}
	}
}