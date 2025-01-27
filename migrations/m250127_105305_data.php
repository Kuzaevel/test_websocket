<?php

use yii\db\Migration;

/**
 * Class m250127_105305_data
 */
class m250127_105305_data extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $users = [
            ['admin', '$2y$13$BPSfIVibw60IAIQQSwXWRuAl.5oPMI33EjJIUqoBFxbMuFXf4iA4q',
                'admin@base.ru', 'admin user', 10],
            ['user', '$2y$13$BPSfIVibw60IAIQQSwXWRuAl.5oPMI33EjJIUqoBFxbMuFXf4iA4q',
                'user@mail.ru',null, 10],
            ['user1', '$2y$13$BPSfIVibw60IAIQQSwXWRuAl.5oPMI33EjJIUqoBFxbMuFXf4iA4q',
                'user1@mail.ru',null, 10],
        ];

        $this->batchInsert('users', ['username', 'password', 'email', 'description', 'status'], $users);

        $tokensArr = [
            [1, '44H67gG2NAuF2Ng0IgnO_ofNJK4iEu13', 1740622187],
            [2, 'ykMp9PKzPv39bDQL78UwsxzZunwVVgO5', 1740622187],
            [3, 'ZI8qMNlQeCdmjraw6wVWE8WlL61QM2-V', 1740622187]
        ];

        foreach ($tokensArr as $token) {
            $this->insert('token', ['user_id' => $token[0], 'token' => $token[1], 'expired_at' => $token[2]]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250127_105305_data cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250127_105305_data cannot be reverted.\n";

        return false;
    }
    */
}
