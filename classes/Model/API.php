<?php namespace KodiCMS\API\Model;

use Kohana\Database\Model\Database as Model_Database;
use \KodiCMS\API\HTTP\API\Exception as API_Exception;

/**
 * @package		KodiCMS/API
 * @category	Model
 * @author		butschster <butschster@gmail.com>
 * @link		http://kodicms.ru
 * @copyright  (c) 2012-2014 butschster
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
class API extends Model_Database {
	
	/**
	 * Table columns
	 * @var array
	 */
	protected $_table_columns;
	
	/**
	 * Secured table columns
	 * @var array
	 */
	protected $_secured_columns = [];
	
	/**
	 *
	 * @var array 
	 */
	protected $_params = [];

	/**
	 * Table name
	 * @var string
	 */
	protected $_table_name;
	
	/**
	 * 
	 * @param string $db
	 */
	public function __construct($db = NULL)
	{
		parent::__construct($db);

		$this->_table_columns = $this->list_columns();
	}

	/**
	 * @return string
	 */
	public function table_name()
	{
		return $this->_table_name;
	}
	
	/**
	 * @return array
	 */
	public function table_columns()
	{
		return $this->_table_columns;
	}
	
	/**
	 * @return array
	 */
	public function secured_columns()
	{
		return $this->_secured_columns;
	}

	/**
	 * @param $fields
	 * @param array $remove_fields
	 * @return array
	 * @throws \HTTP_Exception
	 */
	public function filtered_fields($fields, $remove_fields = [])
	{
		if (!is_array($fields))
		{
			$fields = [$fields];
		}

		$secured_fields = array_intersect($this->_secured_columns, $fields);

		// Exclude fields
		$fields = array_diff($fields, $remove_fields);

		// TODO сделать проверку токена, выдаваемого под API
		if (!empty($secured_fields) AND ! \Auth::is_logged_in('login'))
		{
			throw API_Exception::factory(\API::ERROR_PERMISSIONS, 'You don`t have permissions to access to this fields (:fields).', [
				':fields' => implode(', ', $secured_fields)
			]);
		}

		$fields = array_intersect(array_keys($this->_table_columns), $fields);

		foreach ($fields as $i => $field)
		{
			$fields[$i] = $this->table_name() . '.' . $field;
		}

		return $fields;
	}

	/**
	 * @param array $params
	 * @return $this
	 */
	public function set_params(array $params)
	{
		$this->_params = $params;
		return $this;
	}

	/**
	 * 
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name) 
	{
		return $this->get($name);
	}
	
	/**
	 * 
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name) 
	{
		return isset($this->_params[$name]);
	}

	/**
	 * @param $name
	 * @param null $default
	 * @return mixed
	 */
	public function get($name, $default = NULL)
	{
		return \Arr::get($this->_params, $name, $default);
	}

	/**
	 * 
	 * @param mixed $param
	 * @param mixed $filter
	 * @return array
	 */
	public function prepare_param($param, $filter = NULL)
	{
		if (!is_array($param) AND strpos($param, ',') !== FALSE)
		{
			$param = explode(',', $param);
		}

		if (is_array($param) AND $filter !== NULL)
		{
			$param = array_filter($param, $filter);
		}

		return $param;
	}

	/**
	 * Proxy method to Database list_columns.
	 *
	 * @return array
	 */
	public function list_columns()
	{
		if (\Kohana::$caching === TRUE)
		{
			$cache = \Cache::instance();
			if (($result = $cache->get('table_columns_' . $this->_table_name)) !== NULL)
			{
				return $result;
			}

			$cache->set('table_columns_' . $this->_table_name, $this->_db->list_columns($this->_table_name));
		}

		// Proxy to database
		return $this->_db->list_columns($this->_table_name);
	}
}