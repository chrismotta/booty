<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Placements".
 *
 * @property integer $id
 * @property integer $Publishers_id
 * @property string $name
 * @property integer $frequency_cap
 * @property string $payout
 * @property string $model
 * @property string $status
 * @property string $size
 * @property integer $health_check_imps
 *
 * @property Clusters[] $clusters
 * @property Publishers $publishers
 */
class Placements extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Placements';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Publishers_id', 'frequency_cap', 'payout', 'model', 'size'], 'required'],
            [['Publishers_id', 'frequency_cap', 'health_check_imps'], 'integer'],
            [['payout'], 'number'],
            [['model', 'status'], 'string'],
            [['name', 'size'], 'string', 'max' => 255],
            [['Publishers_id'], 'exist', 'skipOnError' => true, 'targetClass' => Publishers::className(), 'targetAttribute' => ['Publishers_id' => 'id']],
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
            'frequency_cap' => 'Frequency Cap',
            'payout' => 'Payout',
            'model' => 'Model',
            'status' => 'Status',
            'size' => 'Size',
            'health_check_imps' => 'Health Check Imps',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClusters()
    {
        return $this->hasMany(Clusters::className(), ['Placements_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPublishers()
    {
        return $this->hasOne(Publishers::className(), ['id' => 'Publishers_id']);
    }
}
