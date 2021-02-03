<?php

namespace aki\socket\models;

use Yii;

/**
 * This is the model class for table "socket_resource".
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $resource_id
 */
class SocketResource extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'socket_resource';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'resource_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'resource_id' => 'Resource ID',
        ];
    }
}
