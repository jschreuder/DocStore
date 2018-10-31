<?php declare(strict_types = 1);

use Phinx\Migration\AbstractMigration;

class AddTagging extends AbstractMigration
{
    public function up()
    {
        $this->execute("
            CREATE TABLE `tags` (
                `tag` VARCHAR(63) NOT NULL,
                PRIMARY KEY (`tag`)
            ) ENGINE = InnoDB
        ");

        $this->execute("
            CREATE TABLE `publication_tags` (
                `publication_id` BINARY(16) NOT NULL,
                `tag` VARCHAR(63) NOT NULL,
                PRIMARY KEY (`tag`, `publication_id`),
                INDEX `publication_tags_publication_id` (`publication_id`),
                CONSTRAINT `fk_publication_tags_publications`
                    FOREIGN KEY (`publication_id`)
                    REFERENCES `publications` (`publication_id`)
                    ON DELETE CASCADE
                    ON UPDATE RESTRICT,
                CONSTRAINT `fk_publication_tags_tags`
                    FOREIGN KEY (`tag`)
                    REFERENCES `tags` (`tag`)
                    ON DELETE CASCADE 
                    ON UPDATE RESTRICT
            ) ENGINE = InnoDB
        ");
    }

    public function down()
    {
        $this->execute("DROP TABLE `publication_tags`");
        $this->execute("DROP TABLE `tags`");
    }
}
