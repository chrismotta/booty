<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "D_Placement".
 *
 * @property integer $id
 * @property integer $Publishers_id
 * @property string $name
 * @property string $Publishers_name
 * @property string $model
 * @property string $status
 */
class DPlacement extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'D_Placement';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Publishers_id'], 'integer'],
            [['model', 'status'], 'string'],
            [['name', 'Publishers_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'Publishers_id' => 'Publishers ID',
            'name' => 'Name',
            'Publishers_name' => 'Publishers Name',
            'model' => 'Model',
            'status' => 'Status',
        ];
    }
}
