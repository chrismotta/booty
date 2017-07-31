<?php
/* @var $this yii\web\View */
use dosamigos\chartjs\ChartJs;
use conquer\jvectormap\JVectorMapWidget;


$this->title  = 'Splad Dashboard';

$totals       = $totalsProvider->getModels();
$byDate       = $byDateProvider->getModels();
$byCountry    = $byCountryProvider->getModels();

$totalImps    = isset($totals[0]) ? $totals[0]['imps'] : 0; 
$totalUsers   = isset($totals[0]) ? $totals[0]['unique_users'] : 0;
$totalConvs   = isset($totals[0]) ? $totals[0]['installs'] : 0;

$revByDate    = [];
$spendByDate  = [];
$dates        = [];

$profitByDate = [];

foreach ( $byDate as $data )
{
    $revByDate[]    = $data['revenue'];
    $spendByDate[]  = $data['cost'];

    $profit = $data['revenue']-$data['cost'];

    if ( $profit < 0 )
      $profitByDate[] = 0;
    else
      $profitByDate[] = $profit;
    
    $dates[]        = date('Y-m-d', strtotime($data['date']) );
}


$impsByCountry = [];

foreach ( $byCountry as $data )
{
    $code = strtoupper($data['country']);
    $impsByCountry[$code] = $data['imps'];
}



//var_export($byCountry);
?>
<div class="site-index">

    <!-- Main content -->
    <section class="content">
      <!-- Small boxes (Stat box) -->
      <div class="row">
        <div class="col-lg-3 col-xs-6">
          <!-- Apply any bg-* class to to the info-box to color it -->
          <div class="info-box bg-aqua">
            <span class="info-box-icon"><i class="fa fa-users"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Impressions</span>
              <span class="info-box-number"><?php echo $totalImps ?></span>
              <!-- The progress section is optional -->
              <div class="progress">
                <div class="progress-bar" style="width: 70%"></div>
              </div>
              <span class="progress-description">
                Yesterday %
              </span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->

        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">

          <!-- Apply any bg-* class to to the info-box to color it -->
          <div class="info-box bg-aqua">
            <span class="info-box-icon"><i class="fa fa-user"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Unique</span>
              <span class="info-box-number"><?php echo $totalUsers ?></span>
              <!-- The progress section is optional -->
              <div class="progress">
                <div class="progress-bar" style="width: 0%"></div>
              </div>
              <span class="progress-description">
                Yesterday %
              </span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->

        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">

          <!-- Apply any bg-* class to to the info-box to color it -->
          <div class="info-box bg-aqua">
            <span class="info-box-icon"><i class="fa fa-download"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Instals</span>
              <span class="info-box-number"><?php echo $totalConvs ?></span>
              <!-- The progress section is optional -->
              <div class="progress">
                <div class="progress-bar" style="width: 70%"></div>
              </div>
              <span class="progress-description">
                Yesterday %
              </span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->

        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">

          <!-- Apply any bg-* class to to the info-box to color it -->
          <div class="info-box bg-aqua">
            <span class="info-box-icon"><i class="fa fa-database"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Conversion Rate</span>
              <span class="info-box-number"><?php echo $totalImps*$totalConvs/100 ?><sup style="font-size: 10px">%</sup></span>
              <!-- The progress section is optional -->
              <div class="progress">
                <div class="progress-bar" style="width: 70%"></div>
              </div>
              <span class="progress-description">
                Yesterday %
              </span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->

        </div>

      </div>
      <!-- /.row -->

      <!-- Main row -->
      <div class="row">
        <!-- Left col -->
        <section class="col-lg-7 connectedSortable">
          <!-- Custom tabs (Charts with tabs)-->
          <div class="nav-tabs-custom bg-gray">
            <!-- Tabs within a box -->
            <ul class="nav nav-tabs pull-right">
              <li class="active"><a href="#revenue-chart" data-toggle="tab">Rates</a></li>
              <!--
              <li><a href="#sales-chart" data-toggle="tab">Donut</a></li>
              -->
              <li class="pull-left header"><i class="fa fa-signal"></i>Weekly</li>
            </ul>
            <div class="tab-content no-padding">
              <!-- Morris chart - Sales -->            
              <div class="chart tab-pane active" id="revenue-chart" style="position: relative;padding:15px;">               
                  <?= ChartJs::widget([
                      'type' => 'line',
                      'options' => [
                          'height' => 200,
                          'width' => 400
                      ],
                      'data' => [
                          'labels' => $dates,
                          'datasets' => [
                              [
                                  'label' => "Spend",
                                  'backgroundColor' => "rgba(255,99,132,0.2)",
                                  'borderColor' => "rgba(255,99,132,1)",
                                  'pointBackgroundColor' => "rgba(255,99,132,1)",
                                  'pointBorderColor' => "#fff",
                                  'pointHoverBackgroundColor' => "#fff",
                                  'pointHoverBorderColor' => "rgba(255,99,132,1)",
                                  'data' => $spendByDate
                              ],
                              [
                                  'label' => "Revenue",
                                  'backgroundColor' => "rgba(0,108,212,0.2)",
                                  'borderColor' => "rgba(0,108,212,1)",
                                  'pointBackgroundColor' => "rgba(0,108,212,1)",
                                  'pointBorderColor' => "#fff",
                                  'pointHoverBackgroundColor' => "#fff",
                                  'pointHoverBorderColor' => "rgba(0,108,212,1)",
                                  'data' => $revByDate
                              ],
                              [
                                  'label' => "Profit",
                                  'backgroundColor' => "rgba(0,160,0,0.2)",
                                  'borderColor' => "rgba(0,160,0,1)",
                                  'pointBackgroundColor' => "rgba(0,160,0,1)",
                                  'pointBorderColor' => "#fff",
                                  'pointHoverBackgroundColor' => "#fff",
                                  'pointHoverBorderColor' => "rgba(0,160,0,1)",
                                  'data' => $profitByDate
                              ]                            
                          ]
                      ]
                  ]);
                  ?>             
              </div>
              <div class="chart tab-pane" id="sales-chart" style="position: relative; height: 300px;">
              </div>
            </div>
          </div>
          <!-- /.nav-tabs-custom -->
          <!-- /.box -->

        </section>
        <!-- /.Left col -->

        <!-- right col (We are only adding the ID to make the widgets sortable)-->
        <section class="col-lg-5 connectedSortable">

          <!-- Map box -->
          <div class="box box-solid bg-gray">
            <div class="box-header" style="color: #444">
              <!-- tools box -->
              <div class="pull-right box-tools">
                <button type="button" class="btn btn-primary btn-sm daterange pull-right" data-toggle="tooltip" title="Date range">
                  <i class="fa fa-calendar"></i></button>
              </div>
              <!-- /. tools -->

              <i class="fa fa-map-marker"></i>

              <h3 class="box-title">
                Geo Impressions
              </h3>
            </div>
            <div class="box-body">
              <div id="world-map" style="width:100%;"></div>
                <?= JVectorMapWidget::widget([
                    'map'    => 'world_mill_en',
                    'htmlOptions' => [
                      'style'   => 'height:280px;width:100%;'
                    ],
                    'options' => [
                      'series' => [
                        'regions' => [
                          [
                            'values'            => $impsByCountry,
                            'scale'             => ['#C8EEFF', '#0071A4'],
                            'normalizeFunction' => 'polynomial'
                          ]
                        ]  
                      ]                    
                    ]
                ]); ?>              
            </div>
            <!-- /.box-body-->
            <!--
            <div class="box-footer no-border">
              <div class="row">
                <div class="col-xs-4 text-center" style="border-right: 1px solid #f4f4f4">
                  <div id="sparkline-1"></div>
                  <div class="knob-label">Impressions</div>
                </div>

                <div class="col-xs-4 text-center" style="border-right: 1px solid #f4f4f4">
                  <div id="sparkline-2"></div>
                  <div class="knob-label">Unique Users</div>
                </div>

                <div class="col-xs-4 text-center">
                  <div id="sparkline-3"></div>
                  <div class="knob-label">Installations</div>
                </div>

              </div>

            </div>
            -->
          </div>
          <!-- /.box -->
        </section>

          
    </section>

</div>

<br>