<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Placements".
 *
 * @property integer $id
 * @property integer $Sources_id
 * @property string $name
 * @property integer $frequency_cap
 * @property string $payout
 * @property string $model
 * @property string $status
 * @property string $size
 * @property integer $health_check_imps
 *
 * @property Clusters[] $clusters
 * @property Sources $sources
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
            [['Sources_id', 'frequency_cap', 'payout', 'model', 'size'], 'required'],
            [['Sources_id', 'frequency_cap', 'health_check_imps'], 'integer'],
            [['payout'], 'number'],
            [['model', 'status'], 'string'],
            [['name', 'size'], 'string', 'max' => 255],
            [['Sources_id'], 'exist', 'skipOnError' => true, 'targetClass' => Sources::className(), 'targetAttribute' => ['Sources_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'Sources_id' => 'Sources ID',
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
    public function getSources()
    {
        return $this->hasOne(Sources::className(), ['id' => 'Sources_id']);
    }
}
