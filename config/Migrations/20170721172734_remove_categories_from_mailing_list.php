<?php
// @codingStandardsIgnoreFile

use Phinx\Migration\AbstractMigration;

class RemoveCategoriesFromMailingList extends AbstractMigration
{
    /**
     * migrate up
     *
     * @return void
     */
    public function up()
    {
        $table = $this->table('mailing_list');
        $table->removeColumn('categories')
            ->save();
    }

    /**
     * migrate down
     *
     * @return void
     */
    public function down()
    {
        $this->execute('ALTER TABLE mailing_list ADD COLUMN categories VARCHAR(50) NULL DEFAULT NULL AFTER all_categories');

        $masterArray = [];

        $rows = $this->fetchAll('SELECT * FROM categories_mailing_list ORDER BY mailing_list_id ASC');
        foreach ($rows as $row) {
            if (!isset($masterArray[$row['mailing_list_id']])) {
                $masterArray[$row['mailing_list_id']] = [];
            }
            $masterArray[$row['mailing_list_id']][] += $row['category_id'];
        }

        foreach ($masterArray as $key => $val) {
            $string = implode(', ', $val);
            $this->execute("UPDATE mailing_list SET categories='$string' WHERE id='$key'");
        }
    }
}
