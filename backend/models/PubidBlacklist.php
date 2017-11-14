<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pubid_blacklist".
 *
 * @property integer $Campaigns_id
 * @property string $blacklist
 *
 * @property Campaigns $campaigns
 */
class PubidBlacklist extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pubid_blacklist';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Campaigns_id'], 'required'],
            [['Campaigns_id'], 'integer'],
            [['blacklist'], 'string'],
            [['Campaigns_id'], 'exist', 'skipOnError' => true, 'targetClass' => Campaigns::className(), 'targetAttribute' => ['Campaigns_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'Campaigns_id' => 'Campaigns ID',
            'blacklist' => 'Blacklist',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCampaigns()
    {
        return $this->hasOne(Campaigns::className(), ['id' => 'Campaigns_id']);
    }
}
