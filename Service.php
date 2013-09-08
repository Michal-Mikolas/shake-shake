<?php
namespace Shake;

use Nette\Object,
	Nette\DI\Container;


/**
 * Service
 *
 * @author  Michal Mikoláš <nanuqcz@gmail.com>
 */
class Service extends Object
{
	/** @var Nette\DI\Container */
	protected $context;

	/** @var string */
	private $repositoryName;


	
	/**
	 * @param Nette\DI\Container
	 */
	public function __construct(Container $context)
	{
		$this->context = $context;
	}



	/**
	 * If service method doesn't exist, call repository
	 * @param string
	 * @param array
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		$repository = $this->context->{ $this->getRepositoryName() };

		return call_user_func_array(array($repository, $name), $args);
	}



	/**
	 * Get main repository name for this service
	 * @return string
	 */
	public function getRepositoryName()
	{
		if (!$this->repositoryName)
			$this->repositoryName = $this->detectRepositoryName();

		return $this->repositoryName;
	}



	/**
	 * Set main repository name for this service
	 * @param string
	 * @return void
	 */
	public function setRepositoryName($repositoryName)
	{
		$this->repositoryName = $repositoryName;
	}



	/**
	 * Detect repository name based on actual sevice name
	 * @return string
	 */
	protected function detectRepositoryName()
	{
		$name = get_class($this);                     // FooBarService
		$name = substr($name, 0, strlen($name) - 7);  // FooBar
		$name = lcfirst($name);                       // fooBar

		return $name . 'Repository';                  // fooBarRepository
	}

}