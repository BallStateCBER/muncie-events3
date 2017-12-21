<?php
use Migrations\AbstractMigration;

class SetCharacterToUtf8mb4 extends AbstractMigration
{
    /**
     * Up Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function up()
    {
        $this->execute('ALTER DATABASE database_name CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;');
    }

    /**
     * Down Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function down()
    {
        $this->execute('ALTER DATABASE database_name CHARACTER SET = utf8 COLLATE = utf8_unicode_ci;');
    }
}
