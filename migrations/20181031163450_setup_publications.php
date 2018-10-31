<?php declare(strict_types = 1);

use Phinx\Migration\AbstractMigration;

class SetupPublications extends AbstractMigration
{
    public function up()
    {
        $this->execute("
            CREATE TABLE `publications` (
                `publication_id` BINARY(16) NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                `description` TEXT NOT NULL,
                `published` DATETIME NULL,
                `removed` DATETIME NULL,
                PRIMARY KEY (`publication_id`),
                INDEX `document_published_IDX` (`published` ASC)
            ) ENGINE = InnoDB
        ");

        $this->execute("
            ALTER TABLE `documents`
                ADD COLUMN `publication_id` BINARY(16) NOT NULL
                    AFTER `removed`,
                ADD INDEX `document_publication_id_IDX` (`publication_id`),
                ADD CONSTRAINT `fk_documents_publications`
                    FOREIGN KEY (`publication_id`)
                    REFERENCES `publications` (`publication_id`)
                    ON DELETE RESTRICT 
                    ON UPDATE RESTRICT
        ");
    }

    public function down()
    {
        $this->execute("
            ALTER TABLE `documents`
                DROP FOREIGN KEY `fk_documents_publications`,
                DROP INDEX `document_publication_id_IDX`,
                DROP COLUMN `publication_id`
        ");

        $this->execute("DROP TABLE `publications`");
    }
}
