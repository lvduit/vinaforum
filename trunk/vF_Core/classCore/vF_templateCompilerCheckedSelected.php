<?php
vF_Check();

class vF_templateCompilerCheckedSelected implements vF_templateCompilerInterface
{

	public function compile(vF_templateCompiler $compiler, $function, array $arguments, array $options)
	{
		$argc = count($arguments);
		if ($argc != 1)
		{
			throw $compiler->getNewCompilerArgumentException();
		}

		$condition = $compiler->parseConditionExpression($arguments[0], $options);
		$true = ($function == 'checked' ? 'checked="checked"' : 'selected="selected"');

		return '(' . $condition . ' ? \' ' . $true . '\' : \'\')';
	}
}