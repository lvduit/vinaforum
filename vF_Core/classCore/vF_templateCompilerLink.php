<?php
vF_Check();

class vF_templateCompilerLink implements vF_templateCompilerInterface
{
	public function compile(vF_templateCompiler $compiler, $function, array $arguments, array $options)
	{
		$argc = count($arguments);
		if ($argc < 1)
		{
			throw $compiler->getNewCompilerArgumentException();
		}

		$type = array_shift($arguments);

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

		$phpFunction = ($function == 'adminlink' ? 'adminLink' : 'link');

		if ($options['varEscape'] != 'htmlspecialchars')
		{
			$varEscapeParam = ', ' . ($options['varEscape'] ? "'$options[varEscape]'" : 'false');
		}
		else
		{
			$varEscapeParam = '';
		}

		return 'vF_templateHelper::' . $phpFunction . "("
			. $compiler->compileAndCombineSegments($type, $options) . ', ' . $data . ', ' . $params . $varEscapeParam . ')';
	}
}