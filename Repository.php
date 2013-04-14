<?php
namespace Shake;

use \Shake\Utils\Strings;
use \Nette\Object,
	\Nette\Database\Connection,
	\Nette\Database\Table\Selection,
	\Nette\Database\Table\ActiveRow,
	\Nette\MemberAccessException;


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
	protected $conn;

	/** @var string */
	private $tableName;



	public function __construct(Connection $conn)
	{
		$this->conn = $conn;
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
	 * @return ActiveRow|FALSE
	 */
	public function get($id) 
	{
		return $this->select()
					->where('id', $id)
					->limit(1)
					->fetch();
	}



	/**
	 * @return Selection
	 */
	public function getList() 
	{
		return $this->select();
	}



	/**
	 * @param array
	 * @return ActiveRow|FALSE
	 */
	public function create($values) 
	{
		return $this->conn->table($this->getTableName())->insert($values);
	}



	/**
	 * @param int
	 * @param array
	 * @return int
	 */
	public function update($id, $values) 
	{
		return $this->conn->table($this->getTableName())->get($id)->update($values);
	}



	/**
	 * @param int
	 * @return int
	 */
	public function delete($id) 
	{
		return $this->conn->table($this->getTableName())->get($id)->delete();
	}



	/**
	 * @param string
	 * @param array
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		// getBy<column>
		if (Strings::startsWith($name, 'getBy')) {
			$column = substr($name, 5);
			return $this->getBy($column, $args[0]);

		// findBy<column>
		} elseif (Strings::startsWith($name, 'findBy')) {
			$column = substr($name, 6);
			return $this->findBy($column, $args[0]);

		// updateBy<column>
		} elseif (Strings::startsWith($name, 'updateBy')) {
			$column = substr($name, 8);
			return $this->updateBy($column, $args[0]);

		// deleteBy<column>
		} elseif (Strings::startsWith($name, 'deleteBy')) {
			$column = substr($name, 8);
			return $this->deleteBy($column, $args[0]);
		}

		throw new MemberAccessException("Call to undefined method " . get_class($this) . "::$name().");
	}



	/**
	 * @return Selection
	 */
	protected function select()
	{
		return $this->conn->table($this->getTableName());
	}



	/**
	 * @param string
	 * @param mixed|ActiveRow
	 * @return ActiveRow|FALSE
	 */
	protected function getBy($name, $value)
	{
		if ($value instanceof ActiveRow) {
			return $value->{$this->getTableName()};

		} else {
			$name = $this->toUnderscoreCase($name);

			return $this->select()
						->where($name, $value)
						->limit(1)
						->fetch();			
		}
	}



	/**
	 * @param string
	 * @param mixed|ActiveRow
	 * @return Selection
	 */
	protected function findBy($name, $value)
	{
		if ($value instanceof ActiveRow) {
			return $value->related($this->getTableName());

		} else {
			$name = $this->toUnderscoreCase($name);

			return $this->select()
						->where($name, $value);
		}
	}



	/**
	 * @param string
	 * @param mixed
	 * @param array
	 * @return int
	 */
	protected function updateBy($name, $value, $values)
	{
		return $this->findBy($name, $value)->update($values);
	}



	/**
	 * @param string
	 * @param mixed
	 * @return ActiveRow|FALSE
	 */
	protected function deleteBy($name, $value)
	{
		return $this->findBy($name, $value)->delete();
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

}