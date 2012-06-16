<?php
vF_Check();

class vF_templateCompilerPageNav implements vF_templateCompilerInterface
{
	public function compile(vF_templateCompiler $compiler, $function, array $arguments, array $options)
	{
		$argc = count($arguments);
		if ($argc < 4)
		{
			throw $compiler->getNewCompilerArgumentException();
		}

		$perPage = array_shift($arguments);
		$totalItems = array_shift($arguments);
		$page = array_shift($arguments);
		$linkType = array_shift($arguments);

		$data = 'false';
		if (isset($arguments[0]))
		{
			$dataRef = array_shift($arguments);
			$data = $compiler->compileAndCombineSegments($dataRef, array_merge($options, array('varEscape' => false)));
		}

		$params = $compiler->getNamedParamsAsPhpCode(
			$compiler->parseNamedArguments($arguments),
			array_merge($options, array('varEscape' => false))
		);

		$phpFunction = ($function == 'adminpagenav' ? 'adminPageNav' : 'pageNav');

		return 'vF_templateHelper::' . $phpFunction . '('
			. $compiler->compileAndCombineSegments($perPage, $options) . ', '
			. $compiler->compileAndCombineSegments($totalItems, $options) . ', '
			. $compiler->compileAndCombineSegments($page, $options) . ', '
			. $compiler->compileAndCombineSegments($linkType, $options) . ', '
			. $data . ', ' . $params . ')';
	}
}