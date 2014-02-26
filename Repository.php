<?php
namespace Shake;

use Shake\Utils\Strings;
use Nette\Object,
	Nette\Database\Connection,
	Nette\Database\Table\Selection,
	Nette\Database\Table\GroupedSelection,
	Nette\Database\Table\ActiveRow,
	Nette\MemberAccessException,
	Nette\InvalidArgumentException,
	Nette\Application\BadRequestException;


/**
 * Repository 
 * Base repository with conventional functions.
 *
 * @author  Michal Mikoláš <nanuqcz@gmail.com>
 * @package Shake
 */
class Repository extends Object
{
	/** @var Connection */
	private $connection;

	/** @var string */
	private $tableName;



	public function __construct(Connection $connection)
	{
		$this->connection = $connection;
	}



	/**
	 * @return string
	 */
	public function getTableName()
	{
		if (!$this->tableName)
			$this->tableName = $this->detectTableName();

		return $this->tableName;
	}



	/**
	 * @param string
	 * @return void
	 */
	public function setTableName($tableName)
	{
		$this->tableName = $tableName;
	}



	/**
	 * @param int
	 * @return ActiveRow
	 * @throws Nette\Application\BadRequestException
	 */
	public function get($id) 
	{
		$row = $this->find($id);

		if ($row === FALSE)
			throw new BadRequestException('Entry not found', 404);

		return $row;
	}



 	/**
	 * @param int
	 * @param int|array
	 * @return ActiveRow|FALSE
	 */
	public function find($conditions) 
	{
		$selection = $this->select();

		if (is_array($conditions)) {
			$conditions = $this->fixConditions($conditions);
			$selection->where($conditions);
		} else {
			$selection->where($this->prefix('id'), $conditions);
		}

		return $selection->limit(1)->fetch();
 	}



	/**
	 * @param array|NULL
	 * @param array|NULL
	 * @return Selection
	 */
	public function search($conditions = NULL, $limit = NULL) 
	{
		$selection = $this->select();

		if ($conditions) {
			$conditions = $this->fixConditions($conditions);
			$selection->where($conditions);
		}

		if ($limit) {
			$selection->limit($limit[0], $limit[1]);
		}

		return $selection;
	}



	/**
	 * @param string  key column name
	 * @param string  value column name
	 * @param array|NULL
	 * @return array
	 */
	public function fetchPairs($key, $value = NULL, $conditions = NULL)
	{
		$selection = $this->search($conditions);

		return $selection->fetchPairs($key, $value);
	}



	/**
	 * @param array
	 * @return ActiveRow|FALSE
	 */
	public function create($values) 
	{
		return $this->connection->table($this->getTableName())->insert($values);
	}



	/**
	 * @param int
	 * @param array
	 * @return int
	 */
	public function update($id, $values) 
	{
		return $this->connection->table($this->getTableName())->get($id)->update($values);
	}



	/**
	 * @param int
	 * @return int
	 */
	public function delete($id) 
	{
		return $this->connection->table($this->getTableName())->get($id)->delete();
	}



	/**
	 * @param array
	 * @return int
	 */
	public function count($data)
	{
		if ($data instanceof Selection) {
			return $data->count('*');

		} else {
			return count($data);
		}
	}



	/**
	 * @param array
	 * @param int
	 * @param int
	 * @return array
	 */
	public function applyLimit($data, $limit, $offset)
	{
		// Selection
		if (($data instanceof Selection) && !($data instanceof GroupedSelection)) {
			return $data->limit($limit, $offset);

		// GroupedSelection
		} elseif ($data instanceof Iterator) {
			$data = iterator_to_array($data);
			return array_slice($data, $offset, $limit);
			
		// Array
		} elseif ($data instanceof ArrayAccess) {
			return array_slice($data, $offset, $limit);
		
		// Bad argument?
		} else {
			if (is_object($data)) 
				throw new InvalidArgumentException("Can't apply limit to instance of " . get_class($data) . ".");
			else
				throw new InvalidArgumentException("Can't apply limit to " . gettype($data) . " type variable.");
		}
	}



	/**
	 * @return Connection
	 */
	public function getConnection()
	{
		return $this->connection;
	}



	/**
	 * Alias for getConnection()
	 * @return Connection
	 */
	public function getConn()
	{
		return $this->getConnection();
	}



	/**
	 * @param string
	 * @param array
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		// findBy<column>
		if (Strings::startsWith($name, 'getBy')) {
			$column = substr($name, 5);
			return $this->getBy($column, $args[0]);

		// findBy<column>
		} elseif (Strings::startsWith($name, 'findBy')) {
			$column = substr($name, 6);
			return $this->findBy($column, $args[0]);

		// searchBy<column>
		} elseif (Strings::startsWith($name, 'searchBy')) {
			$column = substr($name, 8);
			return $this->searchBy($column, $args[0]);

		// updateBy<column>
		} elseif (Strings::startsWith($name, 'updateBy')) {
			$column = substr($name, 8);
			return $this->updateBy($column, $args[0], $args[1]);

		// deleteBy<column>
		} elseif (Strings::startsWith($name, 'deleteBy')) {	
			$column = substr($name, 8);
			return $this->deleteBy($column, $args[0], $args[1]);
		}

		throw new MemberAccessException("Call to undefined method " . get_class($this) . "::$name().");
	}



	/**
	 * @param string
	 * @param mixed|ActiveRow
	 * @return ActiveRow|FALSE
	 * @throws Nette\Application\BadRequestException
	 */
	public function getBy($name, $value)
	{
		$row = $this->findBy($name, $value);

		if ($row === FALSE)
			throw new BadRequestException('Entry not found', 404);

		return $row;
	}



	/**
	 * @param string
	 * @param mixed|ActiveRow
	 * @return ActiveRow|FALSE
	 */
	public function findBy($name, $value)
	{
		if ($value instanceof ActiveRow) {
			return $value->{$this->getTableName()};

		} else {
			$name = $this->toUnderscoreCase($name);
			$name = $this->prefix($name);

			return $this->select()
						->where($name, $value)
						->limit(1)
						->fetch();			
		}
	}



	/**
	 * @param string
	 * @param mixed|ActiveRow
	 * @param array|NULL
	 * @return Selection
	 */
	public function searchBy($name, $value, $limit = NULL)
	{
		if ($value instanceof ActiveRow) {
			return $value->related($this->getTableName());

		} else {
			$name = $this->toUnderscoreCase($name);
			$name = $this->prefix($name);

			$selection = $this->select()
				->where($name, $value);

			if ($limit)
				$selection->limit($limit[0], $limit[1]);

			return $selection;
		}
	}



	/**
	 * @param string
	 * @param mixed
	 * @param array
	 * @return int
	 */
	public function updateBy($name, $value, $values)
	{
		return $this->findBy($name, $value)->update($values);
	}



	/**
	 * @param string
	 * @param mixed
	 * @return ActiveRow|FALSE
	 */
	public function deleteBy($name, $value)
	{
		return $this->findBy($name, $value)->delete();
	}



	/**
	 * @return Selection
	 */
	protected function select()
	{
		$tableName = $this->getTableName();

		return $this->connection->table($tableName)->select("$tableName.*");
	}



	/**
	 * @return string
	 */
	private function detectTableName()
	{
		$tableName = get_class($this);                                          // FooBarRepository
		$tableName = substr($tableName, 0, strrpos($tableName, 'Repository'));  // FooBar
		$tableName = $this->toUnderscoreCase($tableName);                       // foo_bar

		return $tableName;
	}



	/**
	 * @param string
	 * @return string
	 */
	private function toUnderscoreCase($name)
	{
		return strtolower(preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $name));
	}



	/**
	 * Prepend column name with table name
	 * @param string
	 * @return string
	 */
	private function prefix($columnName)
	{
		if (strpos($columnName, '.') || strpos($columnName, ':'))
			return $columnName;

		return $this->getTableName() . ".$columnName";
	}



	/**
	 * Fix condition's column names for SELECT with JOINs
	 * @param array
	 * @return array
	 */
	private function fixConditions($conditions)
	{
		$fixedConditions = array();
		foreach ($conditions as $key => $value) {
			$prefixedKey = $this->prefix($key);
			$fixedConditions[$prefixedKey] = $conditions[$key];
		}

		return $fixedConditions;
	}

}