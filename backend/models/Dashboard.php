<?php

namespace app\models;

use yii\data\ActiveDataProvider;
use common\models\User;
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
            [['cost', 'revenue'], 'number'],
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
            'cost'  => 'Cost',
            'revenue' => 'Revenue'
        ];
    }


    public function loadData ( array $groupBy = null, array $orderBy = null, array $filters = null, array $fields = null )
    {
        $query = Dashboard::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if ( $filters )
        {
            foreach ( $filters as $filter )
            {
                $query->andFilterWhere( $filter );
            }
        }

        // role filter
        $userroles = User::getRolesByID(Yii::$app->user->getId());
        if(in_array('Advisor', $userroles)){
            $assignedPublishers = Publishers::getPublishersByUser(Yii::$app->user->getId());
            $query->andWhere( ['in', 'Dashboard.Publishers_id', $assignedPublishers] );
        }

        if ( $groupBy )
            $query->groupBy( $groupBy );

        if ( $orderBy )
            $query->orderBy( $orderBy );

        if ( $fields )
            $query->select($fields);

        //var_export( $query->createCommand()->getRawSql() ); 
        return $dataProvider;
    }
}
