<?php 
vF_Check();


class vF_exception extends Exception
{
	protected $_userPrintable = false;
	protected $_messages = null;

	public function __construct($message, $userPrintable = false)
	{
		$this->_userPrintable = (boolean)$userPrintable;

		if (is_array($message) && count($message) > 0)
		{
			$this->_messages = $message;
			$message = reset($message);
		}

		parent::__construct( $message );
	}

	public function isUserPrintable()
	{
		return $this->_userPrintable;
	}

	public function getMessages()
	{
		if (is_array($this->_messages))
		{
			return $this->_messages;
		}
		else
		{
			return $this->getMessage();
		}
	}
}