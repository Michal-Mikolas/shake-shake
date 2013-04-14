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
	public static function toPascalCase($string)
	{
		$string = str_replace(array('-', '_'), ' ', $string);
		return str_replace(' ', '', ucwords($string));
	}



	/**
	 * @param string
	 * @return string
	 */
	public static function toCamelCase($string)
	{
		return lcfirst( self::toPascalCase($string) );
	}



	/**
	 * @param string
	 * @return string
	 */
	public static function toUnderscoreCase($string)
	{
		return strtolower(preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $string));
	}



	/**
	 * @param string
	 * @return string
	 */
	public static function singular($word)
	{
        if (preg_match('#(ses|xes|shes|ches)$#i', $word)) {
            $word = preg_replace('#es$#i', '', $word);

        } elseif (preg_match('#ies$#i', $word)) {
            $word = preg_replace('#ies$#i', 'y', $word);

        } else {
            $word = preg_replace('#s$#i', '', $word);
        }

        return $word;
	}



	/**
	 * @param string
	 * @return string
	 */
	public static function plural($word)
	{
        if (preg_match('#(s|x|sh|ch)$#i', $word)) {
            $word.= 'es';
            
        } elseif (preg_match('#y$#i', $word)) {
            $word = preg_replace('#y$#i', 'ies', $word);
            
        } else {
            $word.= 's';
        }

        return $word;
	}

}