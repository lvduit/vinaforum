<?php 
vF_Check();

abstract class vF_templateCompilerStatement
{
	protected $_statements = array();

	public function __construct( $statement = '' )
	{
		$this->addStatement( $statement );
	}

	public function addStatement( $statement )
	{
		if( !is_string( $statement ) && !( $statement instanceof self ) )
		{
			throw new vF_templateCompilerException( vF_language::getInstance()->phrase('invalid_statement'), true);
		}

		if ($statement !== '')
		{
			$this->_statements[] = $statement;
		}

		return $this;
	}

	abstract public function getFullStatements($outputVar);
}