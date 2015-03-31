<?php namespace KodiCMS\API\API\Validation;

/**
 * @package		KodiCMS/API
 * @category	Exception
 * @author		butschster <butschster@gmail.com>
 * @link		http://kodicms.ru
 * @copyright  (c) 2012-2014 butschster
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
class Exception extends \Kohana_Exception
{
	/**
	* Array of validation objects
	* @var array
	*/
	protected $_errors = [];
	
	/**
	 * @param string $errors
	 * @param string $message
	 * @param array $values
	 */
	public function __construct($errors, $message = 'Failed to validate array', array $values = NULL)
	{
		$this->_errors = $errors;
		parent::__construct($message, $values, \API::ERROR_VALIDATION);
	}
	
	public function get_response()
    {
		// Lets log the Exception, Just in case it's important!
		\Kohana_Exception::log($this);

		$params = [
			'code'  => $this->getCode(),
			'message' => rawurlencode($this->getMessage()),
			'response' => NULL,
			'errors' => $this->_errors
		];

		try
		{
			return json_encode($params);
		}
		catch (\Exception $e)
		{
			return parent::get_response();
		}
	}
}
