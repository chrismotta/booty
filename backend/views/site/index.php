<?php
/* @var $this yii\web\View */
use dosamigos\chartjs\ChartJs;

$this->title = 'Splad Dashboard';

$totals    = $totalsProvider->getModels();
$byDate    = $byDateProvider->getModels();
$byCountry = $byCountryProvider->getModels();

$totalImps  = isset($totals[0]) ? $totals[0]['imps'] : 0; 
$totalUsers = isset($totals[0]) ? $totals[0]['unique_users'] : 0;
$totalConvs = isset($totals[0]) ? $totals[0]['installs'] : 0;

//var_export($byCountry);
?>
<div class="site-index">

    <!-- Main content -->
    <section class="content">
      <!-- Small boxes (Stat box) -->
      <div class="row">
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-gray">
            <div class="inner">
              <h3><?php echo $totalImps ?></h3>

              <p>Today Impressions</p>
            </div>
            <div class="icon">
              <i class="ion ion-bag"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-gray">
            <div class="inner">
              <h3><?php echo $totalUsers ?></h3>

              <p>Today Unique Visitors</p>
            </div>
            <div class="icon">
              <i class="ion ion-pie-graph"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-gray">
            <div class="inner">
              <h3><?php echo $totalConvs ?></h3>

              <p>Today Installations</p>
            </div>
            <div class="icon">
              <i class="ion ion-person-add"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-gray">
            <div class="inner">
              <h3><?php echo $totalImps*$totalConvs/100 ?><sup style="font-size: 20px">%</sup></h3>

              <p>Today Conversion Rate</p>
            </div>
            <div class="icon">
              <i class="ion ion-stats-bars"></i>
            </div>
            <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
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
              <li class="active"><a href="#revenue-chart" data-toggle="tab">Area</a></li>
              <li><a href="#sales-chart" data-toggle="tab">Donut</a></li>
              <li class="pull-left header"><i class="fa fa-inbox"></i> Rates</li>
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
                          'labels' => ["January", "February", "March", "April", "May", "June", "July"],
                          'datasets' => [
                              [
                                  'label' => "Spend",
                                  'backgroundColor' => "rgba(179,181,198,0.2)",
                                  'borderColor' => "rgba(179,181,198,1)",
                                  'pointBackgroundColor' => "rgba(179,181,198,1)",
                                  'pointBorderColor' => "#fff",
                                  'pointHoverBackgroundColor' => "#fff",
                                  'pointHoverBorderColor' => "rgba(179,181,198,1)",
                                  'data' => [65, 59, 90, 81, 56, 55, 40]
                              ],
                              [
                                  'label' => "Revenue",
                                  'backgroundColor' => "rgba(255,99,132,0.2)",
                                  'borderColor' => "rgba(255,99,132,1)",
                                  'pointBackgroundColor' => "rgba(255,99,132,1)",
                                  'pointBorderColor' => "#fff",
                                  'pointHoverBackgroundColor' => "#fff",
                                  'pointHoverBorderColor' => "rgba(255,99,132,1)",
                                  'data' => [28, 48, 40, 19, 96, 27, 100]
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
            <div class="box-header">
              <!-- tools box -->
              <div class="pull-right box-tools">
                <button type="button" class="btn btn-primary btn-sm daterange pull-right" data-toggle="tooltip" title="Date range">
                  <i class="fa fa-calendar"></i></button>
                <button type="button" class="btn btn-primary btn-sm pull-right" data-widget="collapse" data-toggle="tooltip" title="Collapse" style="margin-right: 5px;">
                  <i class="fa fa-minus"></i></button>
              </div>
              <!-- /. tools -->

              <i class="fa fa-map-marker"></i>

              <h3 class="box-title">
                Geo Impressions
              </h3>
            </div>
            <div class="box-body">
              <div id="world-map" style="height: 250px; width: 100%;"></div>
            </div>
            <!-- /.box-body-->
            <div class="box-footer no-border">
              <div class="row">
                <div class="col-xs-4 text-center" style="border-right: 1px solid #f4f4f4">
                  <div id="sparkline-1"></div>
                  <div class="knob-label">Impressions</div>
                </div>
                <!-- ./col -->
                <div class="col-xs-4 text-center" style="border-right: 1px solid #f4f4f4">
                  <div id="sparkline-2"></div>
                  <div class="knob-label">Unique Users</div>
                </div>
                <!-- ./col -->
                <div class="col-xs-4 text-center">
                  <div id="sparkline-3"></div>
                  <div class="knob-label">Installations</div>
                </div>
                <!-- ./col -->
              </div>
              <!-- /.row -->
            </div>
          </div>
          <!-- /.box -->
        </section>

          
    </section>

</div>
