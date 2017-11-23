<?php

namespace backend\controllers;

use Yii;
use app\models;
use Predis;
use backend\components;

class AffiliatesapiController extends \yii\web\Controller
{
    CONST FROM = 'Splad - API Controller<no-reply@spladx.co>';
	const NOTIFY_INBOX = 'dev@splad.co,apastor@splad.co';
	const ALERTS_INBOX = 'dev@splad.co,apastor@splad.co';
    const OPEN_EXRATES_APPID = '3ec50944b9564026a90c196286b3e810';


	protected $_notifications;
	protected $_redis;
	protected $_changes;
	protected $_alerts;
	protected $_changed;
    protected $_exchangeRates;
    protected $_currentRule;
    protected $_blacklistAppId;
    protected $_blacklistKeyword;


	protected function _apiRules ( )
	{
		return [
            [
                'class'         => 'TestAPI',
                'affiliate_id'  => 1,
            ],              
			[
				'class' 		=> 'RegamingAPI',
				'affiliate_id'	=> 2,
			],	
			[
				'class' 		=> 'SlaviaMobileAPI',
				'affiliate_id'	=> 3,
			],	

            [
                'class'         => 'PocketMediaAPI',
                'affiliate_id'  => 4,
            ],
             
            [
                'class'         => 'MobobeatAPI',
                'affiliate_id'  => 5,
            ], 
            [
                'class'         => 'ClicksmobAPI',
                'affiliate_id'  => 6,
            ], 
            [
                'class'         => 'GlispaAPI',
                'affiliate_id'  => 7,
            ],

            [
                'class'         => 'iWoopAPI',
                'affiliate_id'  => 8,
            ],
            [
                'class'         => 'AppclientsAPI',
                'affiliate_id'  => 9,
            ],
            [
                'class'         => 'MinimobAPI',
                'affiliate_id'  => 10,
            ],
            [
                'class'         => 'AddictiveAdsAPI',
                'affiliate_id'  => 11,
            ],
            [
                'class'         => 'MobusiAPI',
                'affiliate_id'  => 12,
            ],
            [
                'class'         => 'TapticaAPI',
                'affiliate_id'  => 13,
            ],
            [
                'class'         => 'KimiaAPI',
                'affiliate_id'  => 14,
            ],
            /*              
            [
                'class'         => 'MobvistaAPI',
                'affiliate_id'  => 15,
            ],
            */        
            [
                'class'         => 'MobildaAPI',
                'affiliate_id'  => 16,
            ],
            [
                'class'         => 'AppThisAPI',
                'affiliate_id'  => 17,
            ],
            [
                'class'         => 'AirpushAPI',
                'affiliate_id'  => 18,
            ],
 
            [
                'class'         => 'PersonalyAPI',
                'affiliate_id'  => 19,
            ],
            [
                'class'         => 'PerformanceGenieAPI',
                'affiliate_id'  => 20,
            ],
            /*
            [
                'class'         => 'CurateMobileAPI',
                'affiliate_id'  => 21,
            ], 
            [
                'class'         => 'LeverageAPI',
                'affiliate_id'  => 22,
            ],                                                               */                   
		];
	}

    private function _loadBlacklists()
    {
        $this->_blacklistKeyword = models\KeywordBlacklist::find()->all();
        $this->_blacklistAppId   = [];

        $appids = models\AppidBlacklist::find()->all();

        if ( $appids )
        {
            foreach ( $appids as $appid )
            {
                $this->_blacklistAppId[] = $appid->app_id;
            }                    
        }
    }


    public function hasBlacklistedKeyword( $string )
    {
        foreach ( $this->_blacklistKeyword as $keyword )
        {   
            if ( 
                preg_match ( 
                    "/(".strtolower($keyword->keyword).")/", strtolower($string) 
                )                
            )
            {
                return true;
            }
        }

        return false;
    }


    public function appIdIsBlacklisted( $app_id )
    {
        return in_array( $app_id, $this->_blacklistAppId );
    }


    public function actionIndex( $affiliate_id = null )
    {
        set_error_handler( array( $this, 'handleErrors' ), E_ALL );

        ini_set('memory_limit','3000M');
        set_time_limit(0);

    	$this->_changes = '';
    	$this->_alerts  = '';
    	$this->_redis 	= new \Predis\Client( \Yii::$app->params['predisConString'] );

        $this->_loadBlacklists();

        $clusters = models\Clusters::find()->with(['carriers'])->all();

        foreach ( $this->_apiRules() AS $rule )
        {
            if ( !$affiliate_id )
            {
                $this->_runAPI( $rule, $clusters );
            }            
            if ( $affiliate_id == $rule['affiliate_id'] )
            {                
                $this->_runAPI( $rule, $clusters );
                break;
            }
        }

        echo '
            <html>
                <head>
                    <style>
                        td {
                            padding:10px;
                            border:1px solid;
                        }
                        table{
                            border:1px solid;
                            border-collapse:collapse;
                        }
                    </style>
                </head>
                <body>
                    <h1>Errors</h1>
                    <table>
                        <thead>
                            <td>API</td>
                            <td>HTTP STATUS</td>                                
                            <td>MESSAGE</td>
                            <td>PARAMS</td>
                        </thead>
                        <tbody>'.utf8_encode($this->_sendAlerts()).'</tbody>
                    </table>   
                    <br>
                    <hr>
                    <h1>Notifications</h1>
                    <table>
                        <thead>
                            <td>API</td>
                            <td>CAMPAIGN ID</td>
                            <td>EXT ID</td>
                            <td>PAYOUT</td>
                            <td>COUNTRY</td>
                            <td>CARRIER</td>
                            <td>CONNECTION</td>
                            <td>DEVICE</td>
                            <td>OS</td>
                            <td>OS VERSION</td>
                            <td>STATUS</td>
                            <td>AFFECTED CLUSTERS</td>
                        </thead>
                        <tbody>'.utf8_encode($this->_sendNotifications()).'</tbody>
                    </table>                    
                </body>
            </html>         
        ';
    }


    public function handleErrors ( $code, $message, $file, $line )
    {
        $message = json_encode([
            'code'      => $code,
            'message'   => $message,
            'file'      => $file,
            'line'      => $line
        ]);

        $this->_createAlert(  $this->_currentRule, 'PHP ERROR: '. $message, null );
    }


    protected function _runAPI ( array $rule, $clusters )
    {
        try
        {
            $className  = 'backend\components\\'.$rule['class'];
            $api        = new $className;
            $affiliate  = models\Affiliates::findOne( ['id' => $rule['affiliate_id'] ] );

            // if affiliate is paused, do not run
            if ( $affiliate->status == 'paused' )
                return false;

            $campaignsData  = $api->requestCampaigns( $affiliate->api_key, $affiliate->user_id );

            $externalIds = [];

            if ( $campaignsData && is_array($campaignsData) )
            {
                foreach ( $campaignsData AS $campaignData )
                {
                    $externalIds[] = $campaignData['ext_id'];

                    $campaign = models\Campaigns::findOne([ 
                        'ext_id'        => $campaignData['ext_id'],
                        'Affiliates_id' => $affiliate->id
                    ]);

                    if  ( $campaign )
                    {
                        // skip when api campaign matches a campaign manually created
                        if ( $campaign->creation=='manual' )
                            continue;

                        // save a copy with old version values
                        $campaignClone = clone $campaign;                
                    }
                    else
                    {
                        $campaignClone = false;
                        $campaign      = new models\Campaigns;     
                    }

                    // change new version status if basic data is null, false or 0
                    if ( !$campaignData['package_id'] )
                        $campaignData['status'] = 'no_appid';
                    else if ( !$campaignData['landing_url'] )
                        $campaignData['status'] = 'no_url';
                    else if ( !$campaignData['payout'] )               
                        $campaignData['status'] = 'no_payout';

                    // if existing campaign is archived or paused, do not update status with api value
                    switch ( $campaign->status )
                    {
                        case 'archived':
                        case 'paused':
                        break;
                        default:
                            $campaign->status = $campaignData['status'];
                        break;
                    }                         

                    // set model values
                    $campaign->Affiliates_id = $affiliate->id;
                    $campaign->name          = $campaignData['name'];
                    $campaign->ext_id        = $campaignData['ext_id'];
                    $campaign->info          = $campaignData['desc'];

                    if ( $campaignData['currency']=='USD' )
                        $campaign->payout = (float)$campaignData['payout'];
                    else
                    {
                        $rate = $this->getExchangeRate( $campaignData['currency'] );

                        if ( $rate )
                            $campaign->payout = (float)$campaignData['payout']/$rate;
                    }

                    // change status if affiliate's cap is 0
                    if ( 
                        isset($campaignData['daily_cap']) 
                        && $campaignData['daily_cap'] == 0
                    )
                    {
                        $campaignData['status'] = 'cap_reached';
                        $campaign->status       = 'cap_reached';
                    }

                    if ( isset($campaignData['daily_cap']) && $campaignData['daily_cap']>=0 )
                    {
                        $campaign->aff_daily_cap   = (float)$campaignData['daily_cap'];
                    }
                    else
                    {
                        $campaignData['daily_cap'] = null;
                        $campaign->aff_daily_cap   = null;
                    }


                    $campaign->landing_url     = $campaignData['landing_url'];

                    if ( empty($campaignData['package_id']) )
                        $campaign->app_id      = null;                        
                    else
                        $campaign->app_id      = json_encode($campaignData['package_id']);

                    if ( empty($campaignData['country']) )
                        $campaign->country      = null;                        
                    else
                        $campaign->country      = json_encode($campaignData['country']);

                    if ( empty($campaignData['device_type']) )
                        $campaign->device_type  = null;
                    else
                        $campaign->device_type  = json_encode($campaignData['device_type']);


                    if ( !empty($campaignData['os']) )
                    {
                        $campaign->os           = json_encode($campaignData['os']);    
                    }
                    else if ( !empty($campaignData['package_id']) )
                    {
                        // if app_id exists and os not, detect os from app_id
                        $os = $this->_getOsFromAppId( $campaignData['package_id'] );

                        if ( $os )
                            $campaign->os       = json_encode($os);
                        else
                            $campaign->os       = null;
                    }
                    else
                    {
                        $campaign->os           = null;
                    }


                    if ( empty($campaignData['os_version']) )
                        $campaign->os_version   = null;
                    else
                        $campaign->os_version   = json_encode($campaignData['os_version']);


                    if ( empty($campaignData['carrier']) )
                        $campaign->carrier      = null;                        
                    else
                        $campaign->carrier      = json_encode($campaignData['carrier']);


                    if ( empty($campaignData['connection_type']) )
                        $campaign->connection_type = null;
                    else
                        $campaign->connection_type = json_encode($campaignData['connection_type']);                        

                    // check if any app_id is blacklisted
                    if ( $campaign->app_id  )
                    {    
                        foreach ( $campaignData['package_id'] as $os => $appId )
                        {
                            if ( $this->appIdIsBlacklisted( $appId ) )
                            {
                                $campaign->status = 'blacklisted';
                                break;
                            }
                        }
                    }     


                    // check if name includes any blacklisted keyword
                    if ( 
                        $campaign->status!='blacklisted' 
                        && $this->hasBlacklistedKeyword( $campaign->name )
                    )
                    {    
                        $campaign->status = 'blacklisted';
                    }   

                    // save
                    if ( $campaign->save() )
                    {
                        // autoassign campaigns to clusters
                        if ( $affiliate->assignation_method == 'automatic' )
                            $this->_autoassign( $affiliate, $campaign, $campaignData, $clusters );
                       
                        // if campaign already exists and was assigned, update redis. Otherwise, save if was auto-assigned
                        if ( $campaignClone )
                            $this->_updateRedis( $affiliate, $campaignClone, $campaignData );
                    }
                    else
                    {
                        $this->_createAlert(  $rule['class'], $campaign->getErrors(), $api->getStatus(), json_encode($campaignData) );
                    }


                    // free ram
                    unset( $campaign );
                    unset( $campaignClone );
                }
            }
            else
            {
                $this->_createAlert( $rule['class'], $api->getMessages(), $api->getStatus() );
            }

            // set status aff_paused for existing campaings which are not present in the current api response
            $this->_clearCampaigns( $rule['affiliate_id'], $externalIds,  $rule['class'] );            
        }
        catch ( Throwable $t )
        {
            $this->_createAlert(  $rule['class'], $t, $api->getStatus() );
        }

        return true;
    }


    private function _getOsFromAppId ( array $values )
    {           
        $oss = [];

        foreach ( $values as $os => $packageId )
        {
            switch ( strtolower($os) )
            {
                case 'ios':
                    $oss[] = 'iOS';
                break;
                case 'android':
                    $oss[] = 'Android';
                break;
                case 'windows':
                    $oss[] = 'Windows';
                break;
                case 'blackberry':
                    $oss[] = 'Blackberry';
                break;
                default:
                    $oss[] = 'Other';
                break;
            }            
        }

        if ( empty( $oss ) )
            return null;

        return $oss;
    }



    private function _autoassign ( $affiliate, $campaign, $apiData, $clusters )
    {
        foreach  ( $clusters as $cluster )
        {
            if ( $campaign->status=='active' && $campaign->payout>=$cluster['min_payout'] )
            {
                // if country / os are open or cluster setting is not included in campaign setting, do not autoasign to this cluster
                if ( 
                    !$apiData['os'] 
                    || !$cluster->os
                    || !$apiData['country'] 
                    || !$cluster->country
                    || !in_array( strtolower($cluster->os), array_map('strtolower',$apiData['os'] ) )  
                    || !in_array( strtolower($cluster->country), array_map('strtolower',$apiData['country'] ) ) 
                )
                {
                    continue;
                }

                // if connection type is not open in cluster or campaign and cluster's is not included between campaign's then skip autoasign
                if ( 
                    $apiData['connection_type'] 
                    && $cluster->connection_type
                    && !in_array( strtolower($cluster->connection_type), array_map('strtolower',$apiData['connection_type'] ) ) 
                )
                {
                    continue;
                }


                if ( 
                    $apiData['device_type'] 
                    && $cluster->device_type
                    && !in_array( strtolower($cluster->device_type), array_map('strtolower',$apiData['device_type'] ) ) 
                )
                {
                    continue;
                }                

                // if os_version is not open in cluster or campaign and cluster's is not included between campaign's then skip autoasign
                if ( 
                    $apiData['os_version'] 
                    && $cluster->os_version 
                    && !in_array( strtolower($cluster->os_version), array_map('strtolower',$apiData['os_version'] ) ) 
                )
                {
                    //continue;
                }

                // if carrier is not open in cluster or campaign and cluster's is not included between campaign's then skip autoasign
                if ( 
                    $apiData['carrier'] 
                    && $cluster->carriers
                    && !in_array( strtolower($cluster->carriers->carrier_name), array_map('strtolower',$apiData['carrier'] ) )
                )
                {
                    continue;
                }

                // check if assignment already exists
                $chc = models\ClustersHasCampaigns::findOne( 
                    [
                        'Campaigns_id' => $campaign->id,
                        'Clusters_id'  => $cluster['id']
                    ] 
                );

                // if it doesn't exist, create
                if ( !$chc )
                {
                    $chc = new models\ClustersHasCampaigns();

                    $chc->Clusters_id   = $cluster->id;
                    $chc->Campaigns_id  = $campaign->id;
                    $chc->delivery_freq = 1;

                    if ( $chc->save() )
                    {
                        $this->_saveRedis( $chc, $affiliate, $campaign, $apiData );

                        models\CampaignsChangelog::log( $campaign->id, 'autoassigned', null, $cluster->id );
                    }
                }

                // free ram
                unset ( $chc );                
            }
        }
    }

    // save assigned campaign
    private function _saveRedis ( $chc, $affiliate, $campaign, $apiData )
    {
        switch ( $campaign->status )
        {
            case 'active':
                $this->_redis->hmset( 'campaign:'.$campaign->id, [
                    'callback'     => $campaign->landing_url,
                    'click_macro'  => $affiliate->click_macro,
                    'placeholders' => $affiliate->placeholders,
                    'macros'       => $affiliate->macros,
                    'ext_id'       => $campaign->ext_id
                ]);

                $packageIds = $apiData['package_id'] ? $apiData['package_id'] : []; 

                foreach ( $packageIds as $packageId )
                {
                    $this->_redis->zadd( 
                        'clusterlist:'.$chc->Clusters_id, 
                        $chc->delivery_freq,
                        $campaign->id.':'.$campaign->affiliates->id.':'.$packageId
                    );                                  
                }

                if ( is_int($apiData['daily_cap']) && (int)$apiData['daily_cap']>=0 )
                {
                    $this->_redis->zadd( 
                        'clustercaps:'.$chc->Clusters_id, 
                        $apiData['daily_cap'],
                        $campaign->id
                    );
                }
            break;
        }
    }

    private function _updateRedis ( $affiliate, $campaign, array $apiData )
    {
        $chc = models\ClustersHasCampaigns::findAll( 
            ['Campaigns_id' => $campaign->id] 
        );

        // evaluate all status cases between versions
        switch ( $campaign->status )
        {
            case 'active':
                switch ( $apiData['status'] )
                {
                    case 'aff_paused':
                    case 'no_url':
                    case 'no_appid':
                    case 'no_payout':
                    case 'cap_reached':
                        $this->_removeCampaignFromClusterList( $chc, $campaign );

                        // remove campaign data
                        $this->_redis->del( 'campaign:'.$campaign->id );
                    break;
                    case 'active':
                        $this->_updateClusterListOnPackageDiff( $chc, $campaign, $apiData );

                        $this->_updateDailyCapInRedis( $chc, $campaign, $apiData );

                        // update campaign data
                        $this->_redis->hmset( 'campaign:'.$campaign->id, [
                            'callback'     => $apiData['landing_url'],
                            'click_macro'  => $affiliate->click_macro,
                            'placeholders' => $affiliate->placeholders,
                            'macros'       => $affiliate->macros,
                            'ext_id'       => $apiData['ext_id']
                        ]);                         
                    break;
                }
            break;
            case 'aff_paused':
            case 'paused':
            case 'no_url':
            case 'no_appid':
            case 'no_payout':
            case 'cap_reached':
                switch ( $apiData['status'] )
                {
                    case 'active':
                        $this->_addCampaignToClusterList( $chc, $campaign, $apiData );

                        // set campaign data
                        $this->_redis->hmset( 'campaign:'.$campaign->id, [
                            'callback'     => $apiData['landing_url'],
                            'click_macro'  => $affiliate->click_macro,
                            'placeholders' => $affiliate->placeholders,
                            'macros'       => $affiliate->macros,
                            'ext_id'       => $apiData['ext_id']
                        ]);
                    break;
                }
            break;
        }
    }


    private function _updateDailyCapInRedis ( $chcs, $campaign, $apiData )
    {
        if ( 
            $campaign->aff_daily_cap != (int)$apiData['daily_cap'] 
        )
        {            
            foreach ( $chcs as $chc )
            {
                if ( 
                    isset($apiData['daily_cap'])
                    && (int)$apiData['daily_cap']>=0 
                )
                {                    
                    $this->_redis->zadd( 
                        'clustercaps:'.$chc->Clusters_id, 
                        $apiData['daily_cap'],
                        $campaign->id
                    );
                }            
                else if ( !isset($campaign->daily_cap) )
                {
                    $this->_redis->zrem( 
                        'clustercaps:'.$chc->Clusters_id, 
                        $campaign->id
                    );
                }                
            }
        }        
    }

    private function _updateClusterListOnPackageDiff ( $chcs, $campaign, $apiData )
    {
        $result = $this->_checkPackageIdDiff( 
            $campaign->app_id,
            $apiData['package_id']
        );

        // check if package id difference exists between versions
        if ( !empty($result['add']) || !empty($result['rem']) )
        {
            foreach ( $chcs as $chc )
            {
                // add new packages not present in the old version
                foreach ( $result['add'] as $packageId )
                {
                    $this->_redis->zadd( 
                        'clusterlist:'.$chc['Clusters_id'], 
                        $chc['delivery_freq'],
                        $campaign->id.':'.$campaign->affiliates->id.':'.$packageId
                    );  
                }

                // remove old packages not present in the new version
                foreach ( $result['rem'] as $packageId )
                {
                    $this->_redis->zrem( 
                        'clusterlist:'.$chc['Clusters_id'], 
                        $campaign->id.":".$campaign->affiliates->id.':'.$packageId
                    );  
                }                                                              
            }                    
        }        
    }


    private function _removeCampaignFromClusterList ( $chcs, $campaign )
    {
        $oldPackageIds = $campaign->app_id ? json_decode($campaign->app_id, true) : [];

        foreach ( $chcs as $chc )
        {
            $this->_redis->zrem( 
                'clustercaps:'.$chc['Clusters_id'], 
                $campaign->id
            );   

            foreach ( $oldPackageIds as $os => $packageId )
            {
                if ( $packageId )
                {
                    $this->_redis->zrem( 
                        'clusterlist:'.$chc['Clusters_id'], 
                       $campaign->id.":".$campaign->affiliates->id.':'.$packageId
                    );                           
                }                    
            }
        }    
    }


    public function _addCampaignToClusterList ( $chcs, $campaign, $apiData  )
    {
        $packageIds = $apiData['package_id'] ? $apiData['package_id'] : []; 

        foreach ( $chcs as $chc )
        {
            if ( $apiData['daily_cap'] && (int)$apiData['daily_cap']>=0 )
            {
                $this->_redis->zadd( 
                    'clustercaps:'.$chc['Clusters_id'], 
                    $apiData['daily_cap'],
                    $campaign->id
                );                                      
            }

            foreach ( $packageIds as $packageId )
            {
                $this->_redis->zadd( 
                    'clusterlist:'.$chc['Clusters_id'], 
                    $chc['delivery_freq'],
                    $campaign->id.':'.$campaign->affiliates->id.':'.$packageId
                );                 
            }
        }        
    }


    protected function _checkPackageIdDiff ( $oldData, $newData )
    {
        $oldPackageIds = $oldData ? json_decode($oldData, true) : [];
        $newPackageIds = $newData ? $newData : [];

        $add = [];
        $rem = [];

        foreach ( $oldPackageIds as $os => $oldPackageId )
        {
            if ( 
                !isset( $newPackageIds[$os] )
                || $newPackageIds[$os] != $oldPackageId
            )
            {
                $rem[] = $oldPackageId;
            }
        }

        foreach ( $newPackageIds as $os => $newPackageId )
        {
            if ( 
                !isset( $oldPackageIds[$os] )
                || $oldPackageIds[$os] != $newPackageId
            )
            {
                $add[] = $newPackageId;
            }
        }

        return [
            'add' => $add,
            'rem' => $rem
        ];       
    }

    // DEPRECATED!!!!
    protected function _checkChangesOLD ( $api_class, $campaign, array $campaignData )
    {
    	$changes = '';
    	$this->_changed = false;

    	if ( $campaign->payout != $campaignData['payout'] )
		{
			$this->_changed = true;
			$changes .= '<td>'.$campaign->payout.' => '. $campaignData['payout'].'</td>';
		}
		else
		{
			$changes .= '<td></td>';
		}


		$changes .= '
			<td>'.$this->_listChanges( json_decode($campaign->country), $campaignData['country'] ).'</td>
			<td>'.$this->_listChanges( json_decode($campaign->carrier), $campaignData['carrier'] ).'</td>
			<td>'.$this->_listChanges( json_decode($campaign->connection_type), $campaignData['connection_type'] ).'</td>
			<td>'.$this->_listChanges( json_decode($campaign->device_type), $campaignData['device_type'] ).'</td>
			<td>'.$this->_listChanges( json_decode($campaign->os), $campaignData['os'] ).'</td>
			<td>'.$this->_listChanges( json_decode($campaign->os_version), $campaignData['os_version'] ).'</td>
		';


		if ( $campaign->status != $campaignData['status'] )
		{
			$this->_changed = true;
			$changes .= '<td>'.$campaign->status .' => ' .$campaignData['status'].'</td>';
		}
		else
		{
			$changes .= '<td></td>';
		}

        $newPackageIds = $campaignData['package_id'] ? $campaignData['package_id'] : [];
        $oldPackageIds = $campaign->app_id ? json_decode($campaign->app_id, true) : [];

        $packagesDiff = array_diff_assoc ( $oldPackageIds, $newPackageIds ) + array_diff_assoc( $newPackageIds, $oldPackageIds );
      
        if ( !empty($packagesDiff) ){
            $this->_changed = true;
        }

    	if ( $this->_changed )
    	{
            $clusters             = [];
            $clustersHasCampaigns = models\ClustersHasCampaigns::findAll( ['Campaigns_id' => $campaign->id] );

            foreach ( $clustersHasCampaigns as $assign )
            {
                $clusters[] = $assign['Clusters_id'];

                switch ( $campaignData['status'] )
                {
                    case 'active': 
                        if ( !empty($packagesDiff) || $campaign->status!=$campaignData['status'] )
                        {
                            foreach ( $oldPackageIds AS $packageId )
                            {
                                 $value = $campaign->id.":".$campaign->affiliates->id.':'.$packageId;
                                 $this->_redis->zrem( 'clusterlist:'.$assign['Clusters_id'], $value );
                            }

                            foreach ( $newPackageIds AS $packageId )
                            {
                                if ( $packageId )
                                {
                                    $this->_redis->zadd( 'clusterlist:'.$assign['Clusters_id'], 
                                        $assign['delivery_freq'],
                                        $campaign->id.':'.$campaign->affiliates->id.':'.$packageId
                                    );                                    
                                }
                            }                        
                        }
                    break;
                    default:
                        $remPackageIds = array_merge( $newPackageIds, $oldPackageIds );

                        foreach ( $remPackageIds AS $packageId )
                        {
                             $value = $campaign->id.":".$campaign->affiliates->id.':'.$packageId;
                             $this->_redis->zrem( 'clusterlist:'.$assign['Clusters_id'], $value );
                        }
                    break;
                }
            }
                        

            if ( !empty($clusters) )
            {
                $this->_changes .= '
                    <tr>
                        <td>'.$api_class.'</td>
                        <td>'.$campaign->id.'</td>
                        <td>'.$campaign->ext_id.'</td>
                        '.$changes.'
                        <td>'.json_encode( $clusters ).'</td>
                    </tr>
                ';                          
            }
    	}

    }


    protected function _clearCampaigns ( $affiliate_id, array $external_ids, $api_class )
    {

        $campaigns = models\Campaigns::find()->where(['Affiliates_id' => $affiliate_id ])->andWhere(['<>', 'status', 'archived'])->andWhere(['<>', 'status', 'paused'])->andWhere(['<>', 'status', 'aff_paused'])->andWhere( ['not in' , 'ext_id', $external_ids] )->andWhere(['<>', 'creation', 'manual'])->all();
       
        //var_export( $campaigns->createCommand()->getRawSql() );die();


        foreach ( $campaigns AS $campaign )
        {
            $clustersHasCampaigns = models\ClustersHasCampaigns::findAll(['Campaigns_id' => $campaign->id]);

            $clusters = [];

            foreach ( $clustersHasCampaigns as $assign )
            {
                $clusters[] = $assign['Clusters_id'];
                $value      = '['.$campaign->id.':'.$campaign->affiliates->id;

                $packageIds = json_decode($campaign->app_id, true);

                foreach ( $packageIds AS $packageId )
                {
                     $value      = $campaign->id.":".$campaign->affiliates->id.':'.$packageId;
                     $this->_redis->zrem( 'clusterlist:'.$assign['Clusters_id'], $value );
                }
            }

            $campaign->status = 'aff_paused';
            $campaign->save();

            unset( $clusters );
            unset( $clustersHasCampaigns );
        }
    }

    public function actionClustersmaintenance ( $id = null )
    {
        $clusterId = isset($_GET['id']) ? $_GET['id'] : $id;

        if ( $clusterId )
        {
            $this->_checkclusterlist( $clusterId);
        }
        else
        {
            $clusters = models\Clusters::find()->all();

            foreach ( $clusters as $model )
            {
                echo '<hr><br><br><strong>CLUSTER:'.$model->id.'</strong><br><br><hr><br><br><br>';
                $this->_checkclusterlist( $model->id );
            }
        }
    }


    public function _checkclusterlist ( $clusterId )
    {
       $this->_redis   = new \Predis\Client( \Yii::$app->params['predisConString'] );
       $this->_redis->select( 0 ); 
       
       if ( !$clusterId )
            die('Cluster ID required');

       $clusterList = $this->_redis->zrange( 
            'clusterlist:'.$clusterId, 
            0, 
            -1,
            [
                'WITHSCORES' => true
            ]               
        );
       $redis = [];
       $sql   = [];

        $clustersHasCampaigns = models\ClustersHasCampaigns::findAll( ['Clusters_id' => $clusterId] );


       foreach ( $clusterList as $value => $frequency )
       {
            $data = explode( ':', $value );

            $id = $data[0];

            if ( isset($redis[$id]) )
            {
                $redis[$id]['app_id'] .= ', ' . $data[2];
            }
            else
            {
                $redis[$id] = [
                    'id'     => $id,
                    'aff'    => $data[1],
                    'status' => 'active',
                    'app_id' => $data[2],
                    'freq'   => $frequency
                ];                    
            }
            /*
            $campaign = models\Campaigns::findOne($data[0]);

            if ( $campaign )
            {
                $id = $campaign->id;

                if ( !isset($redis[$id]) )
                {    
                    $redis[$id] = [
                        'id'     => $campaign->id,
                        'aff'    => $campaign->Affiliates_id,
                        'status' => $campaign->status,
                        'app_id' => $campaign->app_id,
                        'freq'   => $frequency
                    ];
                }
            }
            else
            {

            }
            */
       }

       foreach ( $clustersHasCampaigns as $assign )
       {
            if ( $assign->campaigns->status=='active' && $assign->campaigns->app_id ){
                
                $id = $assign->campaigns->id;

                if ( !isset($sql[$id]) )
                {        
                    $sql[$id] = [
                        'aff'    => $assign->campaigns->Affiliates_id,
                        'status' => $assign->campaigns->status,
                        'app_id' => $assign->campaigns->app_id,
                        'freq'   => $assign->delivery_freq
                    ];                            
                }                
            }
       }       

       echo 'REDIS ROWS     : '.count($clusterList).'<br>';
       echo 'REDIS CAMPAIGNS: '.count($redis).'<br>';
       echo 'MYSQL CAMPAIGNS: '.count($sql);       
       echo '<br><br><br><hr>LEFTOVER CAMPAIGNS<hr><br><br><br>';

       foreach ( $redis as $id => $values )
       {
            if ( 
                !isset($sql[$id]) 
                || $sql[$id]['status'] != $values['status'] 
                || $sql[$id]['freq']   != $values['freq'] 
            )
            {
                $fixed = 'no';

                if ( isset($_GET['fix']) && $_GET['fix']==1 )
                {
                    $packageIds = explode( ', ', $values['app_id'] );

                    foreach ( $packageIds AS $packageId )
                    {
                         $value = $id.":".$values['aff'].':'.$packageId;
                         $this->_redis->zrem( 'clusterlist:'.$clusterId, $value );
                    }

                    $fixed = 'yes';
                }

                echo '
                    ID     :'. $id . '<br>
                    AFF    :'. $values['aff'] .'<br>
                    STATUS :'. $values['status'] . '<br>
                    APP_ID :'. $values['app_id'] . '<br>
                    FREQ   :'. $values['freq'] . '<br>
                    FIXED  :'. $fixed . '<br>
                    <hr>
                ';                  
            }
           
       }

       echo '<br><br><br><hr>MISSING CAMPAIGNS<hr><br><br><br>';
       foreach ( $sql as $id => $values )
       {
            if ( 
                !isset($redis[$id]) 
                || $redis[$id]['status'] != $values['status'] 
                || $redis[$id]['freq'] != $values['freq'] 
            )
            {        
                $fixed = 'no';

                if ( isset($_GET['fix']) && $_GET['fix']==1 )
                {
                    $packageIds = json_decode($values['app_id'], true);

                    foreach ( $packageIds AS $packageId )
                    {
                         $value = $id.":".$values['aff'].':'.$packageId;
                         $this->_redis->zadd( 'clusterlist:'.$clusterId, $values['freq'], $value );
                    }

                    $fixed = 'yes';
                }

                echo '
                    ID     :'. $id . '<br>
                    AFF    :'. $values['aff'] .'<br>
                    STATUS :'. $values['status'] . '<br>
                    APP_ID :'. $values['app_id'] . '<br>
                    FREQ   :'. $values['freq'] . '<br>
                    FIXED  :'. $fixed . '<br>
                    <hr>
                '; 
            }
       }

       echo '<br><br><br>';
    }    


    protected function _checkJsonFieldChanges ( $field, $oldJson, $newJson )
    {
        $list1 = json_decode($oldJson);
        $list2 = json_decode($newJson);

    	$changes = '';

    	if ( !$list1 )
    		$list1 = [];

    	if ( !$list2 )
    		$list2 = [];

    	$first = true;
		foreach ( array_diff($list2,$list1) AS $added )
		{
			if ( $first ){
				$first    = false;
				$changes .= '<b>Added: </b>';
			}
			else
				$changes .= ', ';

			$changes .= $added;
		}

		if ( $changes != '' )
			$changes .= '<br><br>';

		$first = true;
		foreach ( array_diff($list1,$list2) AS $removed )
		{
			if ( $first ){
				$first    = false;
				$changes .= '<b>Removed: </b>';
			}
			else
				$changes .= ', ';

			$changes .= $removed;
		}

		if ( $changes == '' )
			return false;

		return $changes;
    }



    protected function _createAlert ( $api_class, $messages, $status, $data = null )
    {
    	if ( is_array( $messages ) )
    	{
    		foreach ( $messages as $message )
    		{
    			$this->_alerts .= '
		    		<tr>
		    			<td>'.$api_class.'</td>
		    			<td>'.$status.'</td>
		    			<td>'.implode('<br>', $message).'</td>
		    			<td>'.$data.'</td>
		    		</tr>
    			';
    		}
    	}
    	else
    	{
			$this->_alerts .= '
	    		<tr>
	    			<td>'.$api_class.'</td>
	    			<td>'.$status.'</td>	    			
	    			<td>'.$messages.'</td>
		    		<td>'.$data.'</td>	    			
	    		</tr>
			';    		
    	}
    }


    protected function _sendAlerts( )
    {
    	if ( $this->_alerts && $this->_alerts != '' )
    	{
			$html = '
	            <html>
	                <head>
	                	<style>
	                		td {
	                			padding:10px;
	                			border:1px solid;
	                		}
	                		table{
	                			border:1px solid;
	                			border-collapse:collapse;
	                		}
	                	</style>
	                </head>
	                <body>
	                    <table>
	                        <thead>
	                            <td>API</td>
	                            <td>HTTP STATUS</td>	                            
	                            <td>MESSAGE</td>
	                            <td>PARAMS</td>
	                        </thead>
	                        <tbody>'.utf8_encode($this->_alerts).'</tbody>
	                    </table>
	                </body>
	            </html>		
			';

			$this->_sendMail( 
				self::FROM, 
				self::ALERTS_INBOX, 
				'AFFILIATES API ERROR '.date('Y-m-d'),  
				$html 
			);

			return $this->_alerts;
    	}  	

        return '<tr><td style="-webkit-column-span: all;column-span: all;">No errors</td></tr>';
    }


    protected function _sendNotifications( )
    {
    	if ( $this->_changes && $this->_changes != '' )
    	{
			$html = '
	            <html>
	                <head>
	                	<style>
	                		td {
	                			padding:10px;
	                			border:1px solid;
	                		}
	                		table{
	                			border:1px solid;
	                			border-collapse:collapse;
	                		}
	                	</style>	                
	                </head>
	                <body>
	                    <table>
	                        <thead>
	                            <td>API</td>
	                            <td>CAMPAIGN ID</td>
	                            <td>EXT ID</td>
	                            <td>PAYOUT</td>
	                            <td>COUNTRY</td>
	                            <td>CARRIER</td>
	                            <td>CONNECTION</td>
	                            <td>DEVICE</td>
	                            <td>OS</td>
	                            <td>OS VERSION</td>
	                            <td>STATUS</td>
	                           	<td>AFFECTED CLUSTERS</td>
	                        </thead>
	                        <tbody>'.utf8_encode($this->_changes).'</tbody>
	                    </table>
	                </body>
	            </html>		
			';

			$this->_sendMail( 
				self::FROM, 
				self::NOTIFY_INBOX, 
				'CAMPAIGN CHANGES '.date('Y-m-d'),  
				$html 
			);

			return $this->_changes;
    	}  	

        return '<tr><td  style="-webkit-column-span: all;column-span: all;">No important changes</td></tr>';
    }

    private function _sendmail ( $from, $to, $subject, $body )
    {
        $command = '
            export MAILTO="'.$to.'"
            export FROM="'.$from.'"
            export SUBJECT="'.$subject.'"
            export BODY="'.$body.'"
            (
             echo "From: $FROM"
             echo "To: $MAILTO"
             echo "Subject: $SUBJECT"
             echo "MIME-Version: 1.0"
             echo "Content-Type: text/html; charset=UTF-8"
             echo $BODY
            ) | /usr/sbin/sendmail -F $MAILTO -t -v -bm
        ';

        shell_exec( $command );
    }

    public function actionUpdatexchangerates ( )
    {
        $curl   = curl_init();

        $url    = 'https://openexchangerates.org/api/latest.json?app_id='.self::OPEN_EXRATES_APPID.'&base=USD';

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = json_decode(curl_exec($curl));

        if ( $response && $response->rates)
        {
            $sql = 'INSERT INTO Currency_Rates (code, date, rate) VALUES ';

            $first = true;
            foreach ( $response->rates AS $code => $rate )
            {
                switch ( $code )
                {
                    case 'EUR':
                        if ( !$first ){
                            $sql .= ',';
                            $first = false;
                        }

                        $sql .= '("'.$code.'", "'.date('Y-m-d', $response->timestamp).'", '.(float)$rate.')';
                    break;
                }
            }
        }
        $sql .= ' ON DUPLICATE KEY UPDATE rate=VALUES(rate);';

        $rows    = \Yii::$app->db->createCommand( $sql )->execute();

        echo 'Updated '.$rows.' rates<hr/>';
    }

    public function getExchangeRate ( $currency )
    {   
        if ( $this->_exchangeRates && isset($this->_exchangeRates[$currency]))
            return $this->_exchangeRates[$currency];

        $sql = 'SELECT * FROM Currency_Rates WHERE code=:code ORDER BY date(date) DESC';

        $row    = \Yii::$app->db->createCommand( $sql )->bindValues([':code'=>$currency])->queryOne();

        if ( $row )
        {
            $this->_exchangeRates[$currency] = $row['rate'];
            return $row['rate'];
        }
        else
        {
            return false;
        }
    }    



}