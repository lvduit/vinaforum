<?php
vF_Check();

class vF_templateCompilerPhrase implements vF_templateCompilerInterface
{
	protected $_params = array();

	public function compile(vF_templateCompiler $compiler, $function, array $arguments, array $options)
	{
		$argc = count($arguments);
		if ($argc < 1)
		{
			throw $compiler->getNewCompilerArgumentException();
		}

		$phraseName = $compiler->getArgumentLiteralValue(array_shift($arguments));
		if ($phraseName === false)
		{
			throw $compiler->getNewCompilerException(vF_language::getInstance()->phrase('phrase_name_must_be_literal'));
		}

		$phraseValue = $compiler->getPhraseValue($phraseName);
		if ($phraseValue === false)
		{
			return "'" . $compiler->escapeSingleQuotedString($phraseName) . "'";
		}

		$this->_params = $compiler->compileNamedParams($compiler->parseNamedArguments($arguments), $options);

		$phraseValueEscaped = $compiler->escapeSingleQuotedString($phraseValue);
		$phraseValueEscaped = preg_replace_callback('/\{([a-z0-9_-]+)\}/i', array($this, '_replaceParam'), $phraseValueEscaped);

		if ($phraseValueEscaped === '')
		{
			return '';
		}

		$this->_params = array();
		return "'" . $phraseValueEscaped . "'";
	}

	protected function _replaceParam(array $match)
	{
		$paramName = $match[1];

		if (!isset($this->_params[$paramName]))
		{
			return $match[0];
		}

		$code = (string)$this->_params[$paramName];
		if ($code === '')
		{
			return '';
		}

		return "' . " . $this->_params[$paramName] . " . '";
	}
}