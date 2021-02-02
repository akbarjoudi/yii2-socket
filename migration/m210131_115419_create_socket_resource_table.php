<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%socket_resource}}`.
 */
class m210131_115419_create_socket_resource_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%socket_resource}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'resource_id' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%socket_resource}}');
    }
}
