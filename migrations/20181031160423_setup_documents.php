<?php declare(strict_types = 1);

use Phinx\Migration\AbstractMigration;

class SetupDocuments extends AbstractMigration
{
    public function up()
    {
        $this->execute("
            CREATE TABLE `document_types` (
                `document_type` VARCHAR(31) NOT NULL,
                `title` VARCHAR(63) NOT NULL,
                PRIMARY KEY (`document_type`)
            ) ENGINE = InnoDB
        ");

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
                `document_type` VARCHAR(31) NOT NULL,
                `storage_engine` VARCHAR(31) NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                `created` DATETIME NOT NULL,
                `updated` DATETIME NULL,
                `removed` DATETIME NULL,
                PRIMARY KEY (`document_id`),
                INDEX `document_document_type_IDX` (`document_type` ASC),
                INDEX `document_storage_engine_IDX` (`storage_engine` ASC),
                INDEX `document_updated_IDX` (`updated` ASC),
                CONSTRAINT `fk_documents_document_types`
                    FOREIGN KEY (`document_type`)
                    REFERENCES `document_types` (`document_type`)
                    ON DELETE RESTRICT
                    ON UPDATE RESTRICT,
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
        $this->execute("DROP TABLE `document_types`");
    }
}
