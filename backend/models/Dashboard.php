<?php

namespace app\models;

use yii\data\ActiveDataProvider;
use Yii;

/**
 * This is the model class for table "Dashboard".
 *
 * @property integer $id
 * @property integer $imps
 * @property integer $unique_users
 * @property integer $installs
 * @property integer $conv_rate
 * @property string $country
 */
class Dashboard extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Dashboard';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['imps', 'unique_users', 'installs', 'conv_rate'], 'integer'],
            [['country'], 'required'],
            [['country'], 'string', 'max' => 2],
            [['country'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'imps' => 'Imps',
            'unique_users' => 'Unique Users',
            'installs' => 'Installs',
            'conv_rate' => 'Conv Rate',
            'country' => 'Country',
        ];
    }


    public function loadData ( array $groupBy = null, array $fields = null )
    {
        $query = Dashboard::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $query->filterWhere(['>=', 'date', new \yii\db\Expression('NOW() - INTERVAL 7 DAY')]);

        if ( $groupBy )
            $query->groupBy( $groupBy );

        if ( $fields )
            $query->select($fields);

        return $dataProvider;
    }
}
