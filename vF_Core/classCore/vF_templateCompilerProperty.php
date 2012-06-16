<?php
vF_Check();

class vF_templateCompilerProperty implements vF_templateCompilerInterface
{
	public function compile(vF_templateCompiler $compiler, $function, array $arguments, array $options)
	{
		if (count($arguments) != 1)
		{
			throw $compiler->getNewCompilerArgumentException();
		}

		return "vF_templateHelper::styleProperty("
			. $compiler->compileAndCombineSegments($arguments[0], array_merge($options, array('varEscape' => false)))
			. ")";
	}
}