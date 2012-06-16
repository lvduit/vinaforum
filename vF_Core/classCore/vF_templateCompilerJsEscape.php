<?php
vF_Check();

class vF_templateCompilerJsEscape implements vF_templateCompilerInterface
{
	public function compile(vF_templateCompiler $compiler, $function, array $arguments, array $options)
	{
		$argc = count($arguments);
		if ($argc != 1 AND $argc != 2)
		{
			throw $compiler->getNewCompilerArgumentException();
		}

		if (empty($arguments[1]))
		{
			$arguments[1] = 'double';
		}

		if (!is_string($arguments[1]))
		{
			throw $compiler->getNewCompilerException(vF_language::getInstance()->phrase('argument_must_be_string'));
		}

		switch ($arguments[1])
		{
			case 'double':
			case 'single':
				break;

			default:
				throw $compiler->getNewCompilerException(vF_language::getInstance()->phrase('invalid_argument'));
		}

		return 'vF_templateHelper::jsEscape(' . $compiler->compileAndCombineSegments($arguments[0], $options) . ', \'' . $arguments[1] . '\')';
	}
}