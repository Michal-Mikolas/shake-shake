<?php
namespace Shake;

use Nette\Reflection,
	Nette\ComponentModel\IContainer;


/**
 * VisualPaginator
 * shortcut for 3rd party component: \NasExt\Controls\VisualPaginator
 *
 * @author  Michal Mikoláš <nanuqcz@gmail.com>
 * @package Shake
 */
class VisualPaginator extends \NasExt\Controls\VisualPaginator
{

	/**
	 * @param IContainer|NULL
	 * @param string|NULL
	 */
	public function __construct(IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);

		// Set default NasExt's VisualPaginator template
		$reflection = Reflection\ClassType::from('NasExt\Controls\VisualPaginator');
		$dir = dirname($reflection->getFileName());
		$this->setTemplateFile($dir . DIRECTORY_SEPARATOR . 'VisualPaginator.latte');
	}

}