<?php
namespace App\View\Helper;

use Cake\View\Helper;
use Cake\Routing\Router;

class IconHelper extends Helper
{
	/**
	 * Outputs a category icon
	 * @param string $category_name
	 * @param string $mode
	 * @return string
	 */
	public function category($category_name, $mode = null) {
		switch ($mode) {
			case 'email':
				$dir = Router::url('/img/icons/categories/', true);
				$filename = 'meicon_'.strtolower(str_replace(' ', '_', $category_name)).'_32x32.png';
				return '<img src="'.$dir.$filename.'" title="'.$category_name.'" class="category" />';
			default:
				$class = 'icon-'.strtolower(str_replace(' ', '-', $category_name));
				return '<i class="icon '.$class.'" title="'.$category_name.'"></i>';
		}
	}
}
