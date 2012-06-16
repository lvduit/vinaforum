<?php
vF_Check();

class vF_templateCompilerString implements vF_templateCompilerInterface
{
	public function compile(XenForo_Template_Compiler $compiler, $function, array $arguments, array $options)
	{
		if (count($arguments) < 2)
		{
			throw $compiler->getNewCompilerArgumentException();
		}

		$noEscapeOptions = array_merge($options, array('varEscape' => false));

		$functionCompiled = $compiler->compileAndCombineSegments(array_shift($arguments), $noEscapeOptions);

		$outputArgs = array();
		foreach ($arguments AS $argument)
		{
			$outputArgs[] = $compiler->compileAndCombineSegments($argument, $options);
		}
		$argumentsCompiled = $compiler->buildNamedParamCode($outputArgs);

		return 'vF_templateHelper::string(' . $functionCompiled . ', ' . $argumentsCompiled . ')';
	}
}