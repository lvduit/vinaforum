<?php
vF_Check();

class vF_templateCompilerUrlEncode implements vF_templateCompilerInterface
{
	public function compile(XenForo_Template_Compiler $compiler, $function, array $arguments, array $options)
	{
		if (count($arguments) != 1)
		{
			throw $compiler->getNewCompilerArgumentException();
		}

		return 'urlencode(' . $compiler->compileAndCombineSegments($arguments[0], array_merge($options, array('varEscape' => false))) . ')';
	}
}