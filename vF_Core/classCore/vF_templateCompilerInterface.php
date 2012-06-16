<?php
vF_Check();

interface vF_templateCompilerInterface
{
	public function compile( vF_templateCompiler $compiler, $function, array $arguments, array $options);
}