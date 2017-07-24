<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Affiliates".
 *
 * @property integer $id
 * @property string $name
 * @property string $short_name
 * @property string $user_id
 * @property string $api_key
 * @property integer $admin_user
 *
 * @property User $adminUser
 * @property Campaigns[] $campaigns
 */
class Affiliates extends \yii\db\ActiveRecord
{
    public $username;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Affiliates';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['admin_user'], 'integer'],
            [['name', 'user_id', 'api_key'], 'string', 'max' => 255],
            [['short_name'], 'string', 'max' => 3],
            [['admin_user'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['admin_user' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'short_name' => 'Short Name',
            'user_id' => 'User ID',
            'api_key' => 'Api Key',
            'admin_user' => 'Admin User',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdminUser()
    {
        return $this->hasOne(User::className(), ['id' => 'admin_user']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCampaigns()
    {
        return $this->hasMany(Campaigns::className(), ['Affiliates_id' => 'id']);
    }
}
