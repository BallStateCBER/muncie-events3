<?php
namespace App\View\Helper;

use Cake\Routing\Router;
use Cake\View\Helper;

class IconHelper extends Helper
{
    /**
     * Outputs a category icon
     *
     * @param string $categoryName of category
     * @param string|null $mode depending on if email or site
     * @return string
     */
    public function category($categoryName, $mode = null)
    {
        switch ($mode) {
            case 'email':
                $dir = Router::url('/img/icons/categories/', true);
                $filename = 'meicon_' . strtolower(str_replace(' ', '_', $categoryName)) . '_32x32.png';

                return "<img src='$dir" . "$filename' title='$categoryName' class='category' />";
            default:
                $class = 'icon-' . strtolower(str_replace(' ', '-', $categoryName));

                return "<i class='icon $class' title='$categoryName'></i>";
        }
    }
}
