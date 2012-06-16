<?php
vF_Check();

class vF_templateCompilerEscape implements vF_templateCompilerInterface
{
	public function compile(vF_templateCompiler $compiler, $function, array $arguments, array $options)
	{
		if (count($arguments) < 1)
		{
			throw $compiler->getNewCompilerArgumentException();
		}

		$compileOptions = array_merge($options, array('varEscape' => false));

		if (!empty($arguments[1]))
		{
			$doubleEncode = $compiler->parseConditionExpression($arguments[1], $options);
		}
		else
		{
			$doubleEncode = 'true';
		}

		// note: ISO-8859-1 is fine since we use UTF-8 and are only replacing basic chars
		return 'htmlspecialchars(' . $compiler->compileAndCombineSegments($arguments[0], $compileOptions)
			. ', ENT_COMPAT, \'ISO-8859-1\', ' . $doubleEncode . ')';
	}
}