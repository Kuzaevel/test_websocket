<?php

use yii\db\Migration;

/**
 * Class m250123_204147_test_socket
 */
class m250123_204147_test_socket extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('users', [
            'id'          => $this->primaryKey(),
            'username'    => $this->string(255)->notNull(),
            'password'    => $this->string(512)->notNull(),
            'email'       => $this->string(255)->notNull(),
            'description' => $this->string(255)->defaultValue(null),
            'status'      => $this->integer(11)->defaultValue(10),
            'created_at'  => $this->integer(11)->defaultValue(null),
            'updated_at'  => $this->integer(11)->defaultValue(null)
        ]);

        $this->createTable('token', [
            'id'         => $this->primaryKey(),
            'user_id'    => $this->integer(11)->notNull(),
            'token'      => $this->string(255)->notNull(),
            'expired_at' => $this->integer(11)->notNull()
        ]);

        $this->addForeignKey(
            'idx-token-user_id',
            'token',
            'user_id',
            'users',
            'id',
            'CASCADE'
        );

        $this->createTable('message', [
            'id' => $this->primaryKey(),
            'chat_id' => $this->integer()->notNull(),
            'username' => $this->string(255)->notNull(),
            'text' => $this->text()->notNull(),
            'create_time' => $this->bigInteger(16)->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250123_204147_test_socket cannot be reverted.\n";

        return false;
    }
}
