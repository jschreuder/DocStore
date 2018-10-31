<?php declare(strict_types = 1);

use Phinx\Migration\AbstractMigration;

class SetupPublications extends AbstractMigration
{
    public function up()
    {
        $this->execute("
            CREATE TABLE `publication_types` (
                `publication_type` VARCHAR(31) NOT NULL,
                `title` VARCHAR(63) NOT NULL,
                PRIMARY KEY (`publication_type`)
            ) ENGINE = InnoDB
        ");

        $this->execute("
            CREATE TABLE `publications` (
                `publication_id` BINARY(16) NOT NULL,
                `publication_type` VARCHAR(31) NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                `description` TEXT NOT NULL,
                `created` DATETIME NOT NULL,
                `published` DATETIME NULL,
                `removed` DATETIME NULL,
                PRIMARY KEY (`publication_id`),
                INDEX `publication_publication_type_IDX` (`publication_type` ASC),
                INDEX `publication_created_IDX` (`created` ASC),
                INDEX `publication_published_IDX` (`published` ASC),
                CONSTRAINT `fk_publications_publication_types`
                    FOREIGN KEY (`publication_type`)
                    REFERENCES `publication_types` (`publication_type`)
                    ON DELETE RESTRICT
                    ON UPDATE RESTRICT
            ) ENGINE = InnoDB
        ");

        $this->execute("
            ALTER TABLE `documents`
                ADD COLUMN `publication_id` BINARY(16) NOT NULL
                    AFTER `document_id`,
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
        $this->execute("DROP TABLE `publication_types`");
    }
}
