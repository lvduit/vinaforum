<?php
vF_Check();

class vF_templateCompilerHelper implements vF_templateCompilerInterface
{
	public function compile(vF_templateCompiler $compiler, $function, array $arguments, array $options)
	{
		if (count($arguments) < 1)
		{
			throw $compiler->getNewCompilerArgumentException();
		}

		$noEscapeOptions = array_merge($options, array('varEscape' => false));

		$functionCompiled = $compiler->compileAndCombineSegments(array_shift($arguments), $noEscapeOptions);

		$outputArgs = array();
		foreach ($arguments AS $argument)
		{
			$outputArgs[] = $compiler->compileAndCombineSegments($argument, $noEscapeOptions);
		}
		$argumentsCompiled = $compiler->buildNamedParamCode($outputArgs);

		return 'vF_templateHelper::callHelper(' . $functionCompiled . ', ' . $argumentsCompiled . ')';
	}
}