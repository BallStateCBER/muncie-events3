<?php
use Migrations\AbstractMigration;

/**
 * Class MakeBioNullable
 *
 * This migration solves the problem of MySQL both requiring a default value (and throwing errors if none is set) but
 * also not allowing default values to be set.
 *
 * Reference:
 * https://laracasts.com/discuss/channels/laravel/mysql-requires-text-fields-to-have-a-default-value-in-strict-mode
 */
class MakeBioNullable extends AbstractMigration
{
    /**
     * Up Method.
     *
     * @return void
     */
    public function up()
    {
        $table = $this->table('users');
        $table->changeColumn('bio', 'text', [
            'null' => true,
            'default' => null
        ]);
    }

    /**
     * Down Method.
     *
     * @return void
     */
    public function down()
    {
        $table = $this->table('users');
        $table->changeColumn('bio', 'text', [
            'null' => false,
            'default' => ''
        ]);
    }
}
