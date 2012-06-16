<?php

class vF_templateCompilerNumber implements vF_templateCompilerInterface
{
	public function compile(vF_templateCompiler $compiler, $function, array $arguments, array $options)
	{
		$argc = count($arguments);
		if ($argc != 1 && $argc != 2)
		{
			throw $compiler->getNewCompilerArgumentException();
		}

		if (empty($arguments[1]))
		{
			$arguments[1] = '0';
		}

		return 'vF_templateHelper::numberFormat(' . $compiler->compileAndCombineSegments($arguments[0], array_merge($options, array('varEscape' => false))) . ', '
			. $compiler->compileAndCombineSegments($arguments[1], array_merge($options, array('varEscape' => false))) . ')';
	}
}