<?php
namespace App\View\Helper;

use Cake\View\Helper;
use Cake\Routing\Router;

class IconHelper extends Helper
{
    /**
     * Outputs a category icon
     * @param string $categoryName
     * @param string $mode
     * @return string
     */
    public function category($categoryName, $mode = null)
    {
        switch ($mode) {
            case 'email':
                $dir = Router::url('/img/icons/categories/', true);
                $filename = 'meicon_'.strtolower(str_replace(' ', '_', $categoryName)).'_32x32.png';
                return '<img src="'.$dir.$filename.'" title="'.$categoryName.'" class="category" />';
            default:
                $class = 'icon-'.strtolower(str_replace(' ', '-', $categoryName));
                return '<i class="icon '.$class.'" title="'.$categoryName.'"></i>';
        }
    }
}
