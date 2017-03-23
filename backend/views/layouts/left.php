<aside class="main-sidebar">

    <section class="sidebar">

        <!-- Sidebar user panel 
        <div class="user-panel">
            <div class="pull-left image">
                <img src="<?= $directoryAsset ?>/img/user2-160x160.jpg" class="img-circle" alt="User Image"/>
            </div>
            <div class="pull-left info">
                <p>Alexander Pierce</p>

                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>
        -->

        <!-- search form -->
        <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search by id..."/>
              <span class="input-group-btn">
                <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
            </div>
        </form>
        <!-- /.search form -->

        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu'],
                'items' => [
                    ['label' => 'Dashboard', 'icon' => 'fa fa fa-dashboard', 'url' => ['/']],
                    [
                        'label' => 'Demand',
                        'icon' => 'fa fa-plug',
                        'url' => '#',
                        'items' => [
                            [
                                'label' => 'Affiliates',
                                'icon' => 'fa fa-circle-o',
                                'url' => '#',
                                'items' => [
                                    ['label' => 'New Affiliate', 'icon' => 'fa fa-circle-o', 'url' => '#',],
                                    ['label' => 'Admin', 'icon' => 'fa fa-circle-o', 'url' => '#',],
                                ],
                            ],
                            [
                                'label' => 'Campaigns',
                                'icon' => 'fa fa-circle-o',
                                'url' => '#',
                                'items' => [
                                    ['label' => 'New Campaign', 'icon' => 'fa fa-circle-o', 'url' => '#',],
                                    ['label' => 'Admin', 'icon' => 'fa fa-circle-o', 'url' => '#',],
                                ],
                            ],
                        ],
                    ],
                    [
                        'label' => 'Supply',
                        'icon' => 'fa fa-code',
                        'url' => '#',
                        'items' => [
                            [
                                'label' => 'Traffic Sources',
                                'icon' => 'fa fa-circle-o',
                                'url' => '#',
                                'items' => [
                                    [
                                    'label' => 'New Traffic Source', 
                                    'icon' => 'fa fa-circle-o', 
                                    'url' => ['/sources/create'], 
                                    'template' => '<a href="{url}" class="grid-button" data-toggle="control-sidebar">{icon}{label}</a>',
                                    ],
                                    ['label' => 'Admin', 'icon' => 'fa fa-circle-o', 'url' => ['/sources'],],
                                ],
                            ],
                            [
                                'label' => 'Placements',
                                'icon' => 'fa fa-circle-o',
                                'url' => '#',
                                'items' => [
                                    [
                                    'label' => 'New Placement', 'icon' => 'fa fa-circle-o', 'url' => ['/sources/create'],
                                    'template' => '<a href="{url}" class="grid-button" data-toggle="control-sidebar">{icon}{label}</a>',
                                    ],
                                    ['label' => 'Admin', 'icon' => 'fa fa-circle-o', 'url' => ['/placements'],],
                                ],
                            ],
                        ],
                    ],
                    [
                        'label' => 'Clusters',
                        'icon' => 'fa fa-tasks',
                        'url' => '#',
                        'items' => [
                            [
                                'label' => 'New Cluster',
                                'icon' => 'fa fa-circle-o',
                                'url' => '#',
                            ],
                            [
                                'label' => 'Admin',
                                'icon' => 'fa fa-circle-o',
                                'url' => '#',
                            ],
                        ],
                    ],
                    ['label' => 'Reporting', 'icon' => 'fa fa-database', 'url' => ['/debug']],
                    ['label' => 'Developer Menu', 'options' => ['class' => 'header']],
                    ['label' => 'Gii', 'icon' => 'fa fa-file-code-o', 'url' => ['/gii']],
                    ['label' => 'Debug', 'icon' => 'fa fa-bug', 'url' => ['/debug']],
                    ['label' => 'Login', 'url' => ['site/login'], 'visible' => Yii::$app->user->isGuest],
                ],
            ]
        ) ?>

    </section>

</aside>
