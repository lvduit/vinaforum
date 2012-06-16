<?php

/**
* Examples:
* {vf:count $arr}        -> 1,234
* {vf:count $arr, 2}     -> 1,234.00
* {vf:count $arr, false} -> 1234
*
*/
class vF_templateCompilerCount implements vF_templateCompilerInterface
{
	public function compile(vF_templateCompiler $compiler, $function, array $arguments, array $options)
	{
		$argc = count($arguments);
		if (($argc != 1 && $argc != 2) || !is_array($arguments[0]))
		{
			throw $compiler->getNewCompilerArgumentException();
		}

		if (empty($arguments[1]))
		{
			$arguments[1] = '0';
		}
		else if ($arguments[1] === 'false')
		{
			return 'count(' . $compiler->compileAndCombineSegments($arguments[0], array_merge($options, array('varEscape' => false))) . ')';
		}

		return 'vF_templateHelper::numberFormat(count('
			. $compiler->compileAndCombineSegments($arguments[0], array_merge($options, array('varEscape' => false))) . '), '
			. $compiler->compileAndCombineSegments($arguments[1], array_merge($options, array('varEscape' => false))) . ', '
			. self::$languageId
		. ')';
	}
}