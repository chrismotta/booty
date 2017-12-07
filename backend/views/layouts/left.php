<aside class="main-sidebar">

    <section class="sidebar">

        <!-- Sidebar user panel 
        <div class="user-panel">
            <div class="pull-left image">
                <img src="<?= $directoryAsset ?>/img/user2-160x160.jpg" class="img-circle" alt="User Image"/>
            </div>
            <div class="pull-left info">
                <p>Alexander Pierce</p>

                <a href="#"><i class="circle text-success"></i> Online</a>
            </div>
        </div>
        -->

        <!-- search form -->
        <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search by id..."/>
              <span class="input-group-btn">
                <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="search"></i>
                </button>
              </span>
            </div>
        </form>
        <!-- /.search form -->

        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu'],
                'items' => [
                    ['label' => 'Dashboard', 'icon' => 'dashboard', 'url' => ['/']],
                    [
                        'label' => 'Demand',
                        'icon' => 'plug',
                        'url' => '#',
                        'items' => [
                            [
                                'label' => 'Affiliates',
                                'icon' => 'circle-o',
                                'url' => '#',
                                'items' => [
                                    ['label' => 'New', 'icon' => 'circle-thin', 'url' => ['/affiliates/create'],],
                                    ['label' => 'Admin', 'icon' => 'circle-thin', 'url' => ['/affiliates'],],
                                ],
                            ],
                            [
                                'label' => 'Campaigns',
                                'icon' => 'circle-o',
                                'url' => '#',
                                'items' => [
                                    // ['label' => 'New', 'icon' => 'circle-thin', 'url' => '#',],
                                    ['label' => 'Admin', 'icon' => 'circle-thin', 'url' => ['/campaigns'],],
                                    ['label' => 'Active by Targeting', 'icon' => 'circle-thin', 'url' => ['/campaigns/bytarget'],],
                                    ['label' => 'Assigned by Cluster', 'icon' => 'circle-thin', 'url' => ['/campaigns/bycluster'],],
                                ],
                            ],
                        ],
                    ],
                    [
                        'label' => 'Supply',
                        'icon' => 'tasks',
                        'url' => '#',
                        'items' => [
                            /*[
                                'label' => 'Traffic Sources',
                                'icon' => 'circle-o',
                                'url' => '#',
                                'items' => [
                                    [
                                    'label' => 'New Traffic Source', 
                                    'icon' => 'circle-thin', 
                                    'url' => ['/sources/create'], 
                                    // 'template' => '<a href="{url}" class="grid-button" data-toggle="control-sidebar">{icon}{label}</a>',
                                    ],
                                    ['label' => 'Admin', 'icon' => 'circle-thin', 'url' => ['/sources'],],
                                ],
                            ],*/
                            [
                                'label' => 'Publishers',
                                'icon' => 'circle-o',
                                'url' => '#',
                                'items' => [
                                    [
                                    'label' => 'New', 'icon' => 'circle-thin', 'url' => ['/publishers/create'],
                                    // 'template' => '<a href="{url}" class="grid-button" data-toggle="control-sidebar">{icon}{label}</a>',
                                    ],
                                    ['label' => 'Admin', 'icon' => 'circle-thin', 'url' => ['/publishers'],],
                                ],
                            ],
                            [
                                'label' => 'Placements',
                                'icon' => 'circle-o',
                                'url' => '#',
                                'items' => [
                                    [
                                    'label' => 'New', 'icon' => 'circle-thin', 'url' => ['/placements/create'],
                                    // 'template' => '<a href="{url}" class="grid-button" data-toggle="control-sidebar">{icon}{label}</a>',
                                    ],
                                    ['label' => 'Admin', 'icon' => 'circle-thin', 'url' => ['/placements'],],
                                ],
                            ],
                        ],
                    ],
                    [
                        'label' => 'Operation',
                        'icon' => 'code',
                        'url' => '#',
                        'items' => [
                            [
                                'label' => 'Clusters',
                                'icon' => 'circle-o',
                                'url' => '#',
                                'items' => [
                                    [
                                    'label' => 'New', 'icon' => 'circle-thin', 'url' => ['/clusters/create'],
                                    // 'template' => '<a href="{url}" class="grid-button" data-toggle="control-sidebar">{icon}{label}</a>',
                                    ],
                                    ['label' => 'Admin', 'icon' => 'circle-thin', 'url' => ['/clusters'],],
                                ],
                            ],
                            [
                                'label' => 'Blacklists',
                                'icon' => 'circle-o',
                                'url' => '#',
                                'items' => [
                                    [
                                    'label' => 'by App ID', 'icon' => 'circle-thin', 'url' => ['/appidblacklist'],],
                                    ['label' => 'by Keyword', 'icon' => 'circle-thin', 'url' => ['/keywordblacklist'],],
                                ],
                            ],
                            [
                                'label' => 'Static Campaigns',
                                'icon' => 'circle-o',
                                'url' => '#',
                                'items' => [
                                    [
                                    'label' => 'New', 'icon' => 'circle-thin', 'url' => ['/static/create'],
                                    // 'template' => '<a href="{url}" class="grid-button" data-toggle="control-sidebar">{icon}{label}</a>',
                                    ],
                                    ['label' => 'Admin', 'icon' => 'circle-thin', 'url' => ['/static'],],
                                ],
                            ],
                            [
                                'label' => 'Test Traffic Report',
                                'icon' => 'circle-o',
                                'url' => '/testtrafficreport'
                            ],                            
                        ],
                    ],
                    ['label' => 'Reporting', 'icon' => 'bar-chart', 'url' => ['/reporting']],                   

                    ['label' => 'Developer Menu', 'options' => ['class' => 'header']],
                    ['label' => 'Gii', 'icon' => 'file-code-o', 'url' => ['/gii']],
                    ['label' => 'Debug', 'icon' => 'bug', 'url' => ['/debug']],
                    ['label' => 'Login', 'url' => ['site/login'], 'visible' => Yii::$app->user->isGuest],
                ],
            ]
        ) ?>

    </section>

</aside>
