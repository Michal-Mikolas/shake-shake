<?php
namespace Shake\Utils;

use \Nette;


/**
 * Strings
 *
 * @author  Michal Mikoláš <nanuqcz@gmail.com>
 */
class Strings extends Nette\Utils\Strings
{

	/**
	 * @param string
	 * @return string
	 */
	public static function toUnderscoreCase($string)
	{
		return strtolower(preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $string));
	}

}