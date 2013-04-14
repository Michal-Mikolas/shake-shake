<?php
namespace Shake\Config;

use \Nette;


/**
 * Configurator
 *
 * @author  Michal Mikoláš <nanuqcz@gmail.com>
 */
class Configurator extends Nette\Config\Configurator
{

	public function __construct()
	{
		parent::__construct();

		$this->parameters['container']['parent'] = 'Shake\DI\Container';
	}

}