<?php 
vF_Check();


class vF_templateCompilerStatementCollection extends vF_templateCompilerStatement
{
	public function getPartialStatement()
	{
		$output = '';
		$partial = '';
		foreach ($this->_statements as $statement)
		{
			if( is_string( $statement ) )
			{
				if( $statement !== '' )
				{
					$partial .= ( $partial === '' ? $statement : ' . ' . $statement );
				}
			}
			else
			{
				throw new vF_templateCompilerException( 'Statement contains more than just partial statements and only partial statements were requested' );
			}
		}

		if( $partial === '' )
		{
			return "''";
		}
		else
		{
			return $partial;
		}
	}

	public function getFullStatements( $outputVar )
	{
		$output = '';
		$partial = '';
		foreach ($this->_statements AS $statement)
		{
			if( is_string( $statement ) )
			{
				if( $statement !== '' )
				{
					$partial .= ( $partial === '' ? $statement : ' . ' . $statement );
				}
			}
			else
			{
				if( $partial )
				{
					$output .= $this->_getFullStatementFromPartial( $partial, $outputVar );
					$partial = '';
				}

				$childStatement = $statement->getFullStatements( $outputVar );
				if( $childStatement !== '' )
				{
					$output .= $childStatement;
				}
			}
		}

		if( $partial )
		{
			$output .= $this->_getFullStatementFromPartial( $partial, $outputVar );
		}

		return $output;
	}

	protected function _getFullStatementFromPartial( $partial, $outputVar )
	{
		return '$' . $outputVar . ' .= ' . $partial . ";\n";
	}
}