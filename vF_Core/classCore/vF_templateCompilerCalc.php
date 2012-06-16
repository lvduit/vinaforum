<?php
vF_Check();

class vF_templateCompilerCalc implements vF_templateCompilerInterface
{
	public function compile( vF_templateCompiler $compiler, $function, array $arguments, array $options)
	{
		if (count($arguments) != 1)
		{
			throw $compiler->getNewCompilerArgumentException();
		}

		$placeholders = array();

		if (is_string($arguments[0]))
		{
			$expression = $arguments[0];
		}
		else
		{
			$expression = '';
			foreach ($compiler->prepareSegmentsForIteration($arguments[0]) AS $segment)
			{
				if (is_string($segment))
				{
					if (strpos($segment, '?') !== false)
					{
						throw $compiler->getNewCompilerException(vF_language::getInstance()->phrase('invalid_math_expression'));
					}

					$expression .= $segment;
				}
				else
				{
					$expression .= '?';
					$placeholders[] = $compiler->compileSegment($segment, array_merge($options, array('varEscape' => false)));
				}
			}
		}

		return $this->_parseMathExpression($compiler, $expression, $placeholders);
	}

	protected function _parseMathExpression( vF_templateCompiler $compiler, &$expression, array &$placeholders,
		$internalExpression = false, $isFunction = false
	)
	{
		if ($internalExpression && $isFunction && strlen($expression) > 0 && $expression[0] == ')')
		{
			$expression = substr($expression, 1);
			return '()';
		}

		$state = 'value';
		$endState = 'operator';

		$compiled = '';

		do
		{
			$eatChars = 0;

			if ($state == 'value')
			{
				if (preg_match('#^\s+#', $expression, $match))
				{
					// ignore whitespace
					$eatChars = strlen($match[0]);
				}
				else if ($expression[0] == '?')
				{
					$compiled .= array_shift($placeholders);
					$state = 'operator';
					$eatChars = 1;
				}
				else if ($expression[0] == '(')
				{
					$expression = substr($expression, 1);
					$compiled .= $this->_parseMathExpression($compiler, $expression, $placeholders, true);
					$state = 'operator';
					continue; // not eating anything, so must continue
				}
				else if ($expression[0] == '-')
				{
					// negation, not subtraction
					$compiled .= '-';
					$state = 'value'; // we still need a value after this
					$eatChars = 1;
				}
				else if (preg_match('#^\d+(\.\d+)?#', $expression, $match))
				{
					$compiled .= $match[0];
					$state = 'operator';
					$eatChars = strlen($match[0]);
				}
				else if (preg_match('#^(abs|ceil|floor|max|min|pow|round)\(#i', $expression, $match))
				{
					$expression = substr($expression, strlen($match[0]));
					$compiled .= $match[1] . $this->_parseMathExpression($compiler, $expression, $placeholders, true, true);
					$state = 'operator';
					continue; // not eating anything, so must continue
				}
			}
			else if ($state == 'operator')
			{
				if (preg_match('#^\s+#', $expression, $match))
				{
					// ignore whitespace
					$eatChars = strlen($match[0]);
				}
				else
				{
					switch ($expression[0])
					{
						case '*':
						case '+':
						case '-':
						case '/':
						case '%':
							$eatChars = 1;
							$compiled .= " $expression[0] ";
							$state = 'value';
							break;

						case ',':
							if ($isFunction)
							{
								$eatChars = 1;
								$compiled .= ", ";
								$state = 'value';
							}
							else
							{
								// otherwise it wasn't expected
								throw $compiler->getNewCompilerException(vF_language::getInstance()->phrase('invalid_math_expression'));
							}
							break;

						case ')':
							if ($internalExpression)
							{
								// eat and return successfully
								$eatChars = 1;
								$state = false;
							}
							else
							{
								// otherwise it wasn't expected
								throw $compiler->getNewCompilerException(vF_language::getInstance()->phrase('invalid_math_expression'));
							}
							break;
					}
				}
			}

			if ($eatChars)
			{
				$expression = substr($expression, $eatChars);
			}
			else
			{
				// prevent infinite loops -- if you want to avoid this, use "continue"
				throw $compiler->getNewCompilerException(vF_language::getInstance()->phrase('invalid_math_expression'));
			}
		} while ($state !== false && $expression !== '' && $expression !== false);

		if ($internalExpression && $state !== false)
		{
			throw $compiler->getNewCompilerException(vF_language::getInstance()->phrase('invalid_math_expression'));
		}

		if ($state != $endState && $state !== false)
		{
			// operator is the end state -- means we're expecting an operator, so it can be anything
			throw $compiler->getNewCompilerException(vF_language::getInstance()->phrase('invalid_math_expression'));
		}

		return "($compiled)";
	}
}