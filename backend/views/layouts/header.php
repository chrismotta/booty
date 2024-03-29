<?php
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */
$username = isset( Yii::$app->user->identity->username ) ? Yii::$app->user->identity->username : null;
$userroles = common\models\User::getRolesByID(Yii::$app->user->getId());
$path = Yii::$app->homeUrl;

?>

<header class="main-header">
    <?= Html::a('<span class="logo-mini"><img src="'.$path.'img/splad-iso.png" style="vertical-align: top; width: 38px; margin-top: 3px;" /></span><span class="logo-lg"><img src="'.$path.'img/splad-logo.png" style="vertical-align: top; width: 120px; margin-top: 3px;" /></span>', $path, ['class' => 'logo']) ?>

    <nav class="navbar navbar-static-top" role="navigation">

        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>

        <div class="navbar-custom-menu">

            <ul class="nav navbar-nav">

                <li class="dropdown notifications-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-bell-o"></i>
                        <span class="label label-warning">0</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header">You have 0 notifications</li>
                        <li>
                            <!-- inner menu: contains the actual data -->
                            <!--
                            <ul class="menu">

                                <li>
                                    <a href="#">
                                        <i class="fa fa-users text-aqua"></i> 5 new members joined today
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="fa fa-warning text-yellow"></i> Very long description here that may
                                        not fit into the page and may cause design problems
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="fa fa-users text-red"></i> 5 new members joined
                                    </a>
                                </li>

                                <li>
                                    <a href="#">
                                        <i class="fa fa-shopping-cart text-green"></i> 25 sales made
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="fa fa-user text-red"></i> You changed your username
                                    </a>
                                </li>
                            </ul>
                            -->
                        </li>
                        <li class="footer"><a href="#">View all</a></li>
                    </ul>
                </li>
                
                <!-- User Account: style can be found in dropdown.less -->

                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="<?php echo Yii::$app->homeUrl ?>img/generic_user.png" class="user-image" alt="User Image"/>
                        <span class="hidden-xs"><?php echo $username ?></span>
                    </a>
                    <ul class="dropdown-menu">
                        <!-- User image -->

                        <li class="user-header">
                            <img src="<?php echo Yii::$app->homeUrl ?>img/generic_user.png" class="img-circle"
                                 alt="User Image"/>

                            <p>
                                <?php echo $username ?>
                                <!-- <small>Member role: </small> -->
                            </p>
                        </li>

                        <!-- Menu Body -->
                        <!--li class="user-body">
                            <div class="col-xs-4 text-center">
                                <a href="#">Followers</a>
                            </div>
                            <div class="col-xs-4 text-center">
                                <a href="#">Sales</a>
                            </div>
                            <div class="col-xs-4 text-center">
                                <a href="#">Friends</a>
                            </div>
                        </li-->
                        <!-- Menu Footer-->
                        <li class="user-footer">

                            <div class="pull-left">
                                <a href="#" class="btn btn-default btn-flat">Profile</a>
                            </div>
                            <div class="pull-right">
                                <?= Html::a(
                                    'Sign out',
                                    ['/site/logout'],
                                    ['data-method' => 'post', 'class' => 'btn btn-default btn-flat']
                                ) ?>
                            </div>
                        </li>
                    </ul>
                </li>

                <!-- User Account: style can be found in dropdown.less -->
                <!-- <li>
                    <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
                </li> -->
            </ul>
        </div>
    </nav>
</header>
