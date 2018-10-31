<?php


use Phinx\Migration\AbstractMigration;

class AddFlysystemStorageEngine extends AbstractMigration
{
    public function up()
    {
        $this->execute("
            CREATE TABLE `storage_engine_flysystem_files` (
                `document_id` BINARY(16) NOT NULL,
                `location` VARCHAR(63) NOT NULL,
                `filename` VARCHAR(255) NOT NULL,
                `mime_type` VARCHAR(127) NOT NULL,
                PRIMARY KEY (`document_id`),
                CONSTRAINT `fk_flysystem_files_documents`
                    FOREIGN KEY (`document_id`)
                    REFERENCES `documents` (`document_id`)
                    ON DELETE RESTRICT
                    ON UPDATE RESTRICT
            ) ENGINE = InnoDB
        ");
    }

    public function down()
    {
        $this->execute("DROP TABLE `storage_engine_flysystem_files`");
    }
}
