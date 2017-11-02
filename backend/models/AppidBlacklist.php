<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "appid_blacklist".
 *
 * @property integer $id
 * @property string $app_id
 */
class AppidBlacklist extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'appid_blacklist';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['app_id'], 'string', 'max' => 45],
            [['app_id'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'app_id' => 'App ID',
        ];
    }
}
