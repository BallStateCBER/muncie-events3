<?php

use Phinx\Migration\AbstractMigration;

class RemoveCategoriesFromMailingList extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        /**
         *
         * WARNING: THIS MIGRATION SHOULD NOT BE RUN IN A PRODUCTION SETTING
         * IT WILL JUST DUPLICATE DATA AND RUIN YOUR SCHEMA FOR REASONS I
         * HAVE YET TO FIGURE OUT
         *
         */
        $table = $this->table('mailing_list');
        $joinTable = $this->table('categories_mailing_list');
        $table->renameColumn('categories', 'selected_categories');

        $rows = $this->fetchAll('SELECT * FROM mailing_list WHERE selected_categories is NOT NULL');

        foreach ($rows as $row) {
            $selectedCategories = explode(',', $row['selected_categories']);
            $user = $row['id'];
            print_r('New user:' . $user);

            foreach ($selectedCategories as $category) {
                $joinData = [
                    'mailing_list_id' => $user,
                    'category_id' => $category
                ];

                $joinTable->insert($joinData);
                $joinTable->saveData();
                print_r('Category ' . $category . ' added to user ' . $user);
            }
        }

        $table->removeColumn('selected_categories')
            ->save();
    }
}
