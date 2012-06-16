<?php
vF_Check();

class vF_templateCompilerRaw implements vF_templateCompilerInterface
{
	public function compile(vF_templateCompiler $compiler, $function, array $arguments, array $options)
	{
		if (count($arguments) > 2)
		{
			throw $compiler->getNewCompilerArgumentException();
		}

		$compileOptions = array_merge($options, array('varEscape' => false));
		$raw = $compiler->compileVarRef($arguments[0], $options);

		if (empty($arguments[1]))
		{
			return $raw;
		}
		else
		{
			return 'vF_templateHelper::rawCondition(' . $raw . ', '
				. $compiler->compileAndCombineSegments($arguments[1], $compileOptions) . ')';
		}
	}
}