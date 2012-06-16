<?php
vF_Check();

class vF_templateCompilerDateTime implements vF_templateCompilerInterface
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
			$arguments[1] = '';
		}

		switch ($function)
		{
			case 'date':
			case 'time':
			case 'datetime':
				$phpFunction = $function;
				break;

			default:
				$phpFunction = 'datetime';
		}

		return 'vF_templateHelper::' . $phpFunction
			. '(' . $compiler->compileAndCombineSegments($arguments[0], array_merge($options, array('varEscape' => false))) . ', '
			. $compiler->compileAndCombineSegments($arguments[1], array_merge($options, array('varEscape' => false))) . ')';
	}
}