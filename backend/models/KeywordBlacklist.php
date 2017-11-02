<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "keyword_blacklist".
 *
 * @property integer $id
 * @property string $keyword
 */
class KeywordBlacklist extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'keyword_blacklist';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['keyword'], 'string', 'max' => 255],
            [['keyword'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'keyword' => 'Keyword',
        ];
    }
}
