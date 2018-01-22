<?php
// @codingStandardsIgnoreFile

use Phinx\Migration\AbstractMigration;

class ChangeEmailToUnique extends AbstractMigration
{
    /**
     * migrate up
     *
     * @return void
     */
    public function up()
    {
        $this->execute('DELETE FROM users WHERE id = 479');
        $this->execute('ALTER TABLE users
          ADD CONSTRAINT uq UNIQUE (email)');
    }

    /**
     * migrate down
     *
     * @return void
     */
    public function down()
    {
        $this->execute('DROP INDEX uq ON users');
    }
}
