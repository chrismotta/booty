<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Publishers".
 *
 * @property integer $id
 * @property string $name
 * @property string $short_name
 * @property integer $admin_user
 *
 * @property Placements[] $placements
 * @property User $adminUser
 */
class Publishers extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Publishers';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['admin_user'], 'integer'],
            [['name'], 'string', 'max' => 255],
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
            'admin_user' => 'Admin User',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlacements()
    {
        return $this->hasMany(Placements::className(), ['Publishers_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdminUser()
    {
        return $this->hasOne(User::className(), ['id' => 'admin_user']);
    }
}
