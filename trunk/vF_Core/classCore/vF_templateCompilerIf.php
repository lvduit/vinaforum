<?php
vF_Check();

class vF_templateCompilerIf implements vF_templateCompilerInterface
{
	public function compile( vF_templateCompiler $compiler, $function, array $arguments, array $options )
	{
		$argc = count($arguments);
		if ($argc != 2 && $argc != 3)
		{
			throw $compiler->getNewCompilerArgumentException();
		}

		$condition = $compiler->parseConditionExpression($arguments[0], $options);
		$true = $compiler->compileAndCombineSegments($arguments[1], $options);

		if( !isset( $arguments[2] ) )
		{
			$arguments[2] = '';
		}
		$false = $compiler->compileAndCombineSegments( $arguments[2], $options );

		return '(' . $condition . ' ? (' . $true . ') : (' . $false . '))';
	}
}