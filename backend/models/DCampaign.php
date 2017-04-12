<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "D_Campaign".
 *
 * @property integer $id
 * @property string $name
 * @property integer $Affiliates_id
 * @property string $Affiliates_name
 */
class DCampaign extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'D_Campaign';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'Affiliates_id', 'Affiliates_name'], 'required'],
            [['Affiliates_id'], 'integer'],
            [['name', 'Affiliates_name'], 'string', 'max' => 255],
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
            'Affiliates_id' => 'Affiliates ID',
            'Affiliates_name' => 'Affiliates Name',
        ];
    }
}
