<?php declare(strict_types = 1);

use Phinx\Migration\AbstractMigration;

class SetupDocuments extends AbstractMigration
{
    public function up()
    {
        $this->execute("
            CREATE TABLE `storage_engines` (
                `storage_engine` VARCHAR(31) NOT NULL,
                `title` VARCHAR(63) NOT NULL,
                PRIMARY KEY (`storage_engine`)
            ) ENGINE = InnoDB
        ");

        $this->execute("
            CREATE TABLE `documents` (
                `document_id` BINARY(16) NOT NULL,
                `storage_engine` VARCHAR(31) NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                `filename` VARCHAR(255) NOT NULL,
                `filesize` INTEGER UNSIGNED NOT NULL,
                `mime_type` VARCHAR(127) NOT NULL,
                `created` DATETIME NOT NULL,
                `updated` DATETIME NULL,
                `removed` DATETIME NULL,
                PRIMARY KEY (`document_id`),
                INDEX `document_storage_engine_IDX` (`storage_engine` ASC),
                INDEX `document_updated_IDX` (`updated` ASC),
                CONSTRAINT `fk_documents_storage_engines`
                    FOREIGN KEY (`storage_engine`)
                    REFERENCES `storage_engines` (`storage_engine`)
                    ON DELETE RESTRICT
                    ON UPDATE RESTRICT
            ) ENGINE = InnoDB
        ");
    }

    public function down()
    {
        $this->execute("DROP TABLE `documents`");
        $this->execute("DROP TABLE `storage_engines`");
    }
}
