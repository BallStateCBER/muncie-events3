<?php
use Migrations\AbstractMigration;

class MakeFacebookIdNullable extends AbstractMigration
{
    /**
     * Up Method.
     *
     * @return void
     */
    public function up()
    {
        $table = $this->table('users');
        $table->changeColumn('facebook_id', 'biginteger', [
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
        $table->changeColumn('facebook_id', 'biginteger', [
            'null' => false,
            'default' => 0
        ]);
    }
}
