<?php
namespace App\Shell;

use App\Model\Table\CategoriesTable;
use App\Model\Table\TagsTable;
use Cake\Cache\Cache;
use Cake\Console\Shell;
use Cake\ORM\TableRegistry;

class CacheShell extends Shell
{
    /**
     * Display help for this console.
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->addSubcommand('tags', [
            'help' => 'Invalidates and rebuilds cached tag information',
        ]);

        return $parser;
    }

    /**
     * Invalidates and rebuilds cached tag information
     *
     * @return void
     */
    public function tags()
    {
        /**
         * @var CategoriesTable $categoriesTable
         * @var TagsTable $tagsTable
         */
        $categoriesTable = TableRegistry::getTableLocator()->get('Categories');
        $tagsTable = TableRegistry::getTableLocator()->get('Tags');
        $categories = $categoriesTable->find('list');
        $directions = ['future', 'past'];
        $run = function ($filter) use ($tagsTable) {
            $cacheKey = 'getTagsWithCounts-' . implode('-', $filter);
            Cache::delete($cacheKey, 'daily');
            $start = microtime(true);
            $this->out('Populating ' . $cacheKey . '...');
            $tagsTable->getWithCounts($filter);
            $duration = round((microtime(true) - $start) * 1000);
            $this->out("Done ({$duration}ms)\n");
        };

        foreach ($directions as $direction) {
            // All categories
            $filter = compact('direction');
            $run($filter);

            // Specific categories
            foreach ($categories as $categoryId => $category) {
                $filter['categories'] = $categoryId;
                $run($filter);
            }
        }

        $this->out('Finished');
    }
}
