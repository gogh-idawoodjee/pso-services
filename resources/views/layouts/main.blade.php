<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="content-type" content="text/html;charset=UTF-8"/>
    <meta charset="utf-8"/>
    <title>Pages - Admin Dashboard UI Kit - Blank Page</title>
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no"/>
    <link rel="apple-touch-icon" href="/pages/ico/60.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/pages/ico/76.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/pages/ico/120.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/pages/ico/152.png">
    <link rel="icon" type="image/x-icon" href="/favicon.ico"/>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta content="" name="description"/>
    <meta content="" name="author"/>
    <link href="/assets/plugins/pace/pace-theme-flash.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/plugins/font-awesome/css/font-awesome.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/plugins/jquery-scrollbar/jquery.scrollbar.css" rel="stylesheet" type="text/css" media="screen"/>
    <link href="/assets/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" media="screen"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/9000.0.1/themes/prism.min.css" rel="stylesheet" />

{{--        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/11.6.0/styles/default.min.css">--}}
    <link class="main-stylesheet" href="/pages/css/pages.min.css" rel="stylesheet" type="text/css"/>
    @livewireStyles
</head>

<body class="fixed-header horizontal-menu horizontal-app-menu">
    <!-- START PAGE-CONTAINER -->
    <div class="header p-r-0 bg-primary">
        <div class="header-inner header-md-height m-t-5 m-l-10 m-r-10">
            <a href="/#" class="btn-link toggle-sidebar d-lg-none text-white sm-p-l-0 btn-icon-link"
               data-toggle="horizontal-menu">
                <i class="pg-icon">menu</i>
            </a>
            <div class="">
                <div class="brand inline no-border d-sm-inline-block">
                    <img src="/assets/img/logo_white.png" alt="logo" data-src="/assets/img/logo_white.png"
                         data-src-retina="/assets/img/logo_white_2x.png" width="78" height="22">
                </div>
                <!-- START NOTIFICATION LIST -->
                <ul class="d-lg-inline-block d-none  notification-list no-margin b-grey b-l b-r no-style p-l-30 p-r-20">
                    <li class="p-r-10 inline">
                        <div class="dropdown">
                            <a href="/javascript:;" id="notification-center" class="header-icon btn-icon-link"
                               data-toggle="dropdown">
                                <i class="pg-icon">world</i>
                                <span class="bubble"></span>
                            </a>
                            <!-- START Notification Dropdown -->
                            <div class="dropdown-menu notification-toggle" role="menu"
                                 aria-labelledby="notification-center">
                                <!-- START Notification -->
                                <div class="notification-panel">
                                    <!-- START Notification Body-->
                                    <div class="notification-body scrollable">
                                        <!-- START Notification Item-->
                                        <div class="notification-item unread clearfix">
                                            <!-- START Notification Item-->
                                            <div class="heading open">
                                                <a href="/#" class="text-complete pull-left d-flex align-items-center">
                                                    <i class="pg-icon m-r-10">map</i>
                                                    <span class="bold">Carrot Design</span>
                                                    <span class="fs-12 m-l-10">{{ Auth::user()->name }}</span>
                                                </a>
                                                <div class="pull-right">
                                                    <div
                                                        class="thumbnail-wrapper d16 circular inline m-t-15 m-r-10 toggle-more-details">
                                                        <div><i class="pg-icon">chevron_down</i>
                                                        </div>
                                                    </div>
                                                    <span class=" time">few sec ago</span>
                                                </div>
                                                <div class="more-details">
                                                    <div class="more-details-inner">
                                                        <h5 class="semi-bold fs-16">“Apple’s Motivation - Innovation
                                                            <br>
                                                            distinguishes between <br>
                                                            A leader and a follower.”</h5>
                                                        <p class="small hint-text">
                                                            Commented on john Smiths wall.
                                                            <br> via pages framework.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- END Notification Item-->
                                            <!-- START Notification Item Right Side-->
                                            <div class="option" data-toggle="tooltip" data-placement="left"
                                                 title="mark as read">
                                                <a href="/#" class="mark"></a>
                                            </div>
                                            <!-- END Notification Item Right Side-->
                                        </div>
                                        <!-- START Notification Body-->
                                        <!-- START Notification Item-->
                                        <div class="notification-item  clearfix">
                                            <div class="heading">
                                                <a href="/#" class="text-danger pull-left">
                                                    <i class="pg-icon m-r-10">alert_warning</i>
                                                    <span class="bold">98% Server Load</span>
                                                    <span class="fs-12 m-l-10">Take Action</span>
                                                </a>
                                                <span class="pull-right time">2 mins ago</span>
                                            </div>
                                            <!-- START Notification Item Right Side-->
                                            <div class="option">
                                                <a href="/#" class="mark"></a>
                                            </div>
                                            <!-- END Notification Item Right Side-->
                                        </div>
                                        <!-- END Notification Item-->
                                        <!-- START Notification Item-->
                                        <div class="notification-item  clearfix">
                                            <div class="heading">
                                                <a href="/#" class="text-warning pull-left">
                                                    <i class="pg-icon m-r-10">alert_warning</i>
                                                    <span class="bold">Warning Notification</span>
                                                    <span class="fs-12 m-l-10">Buy Now</span>
                                                </a>
                                                <span class="pull-right time">yesterday</span>
                                            </div>
                                            <!-- START Notification Item Right Side-->
                                            <div class="option">
                                                <a href="/#" class="mark"></a>
                                            </div>
                                            <!-- END Notification Item Right Side-->
                                        </div>
                                        <!-- END Notification Item-->
                                        <!-- START Notification Item-->
                                        <div class="notification-item unread clearfix">
                                            <div class="heading">
                                                <div
                                                    class="thumbnail-wrapper d24 circular b-white m-r-5 b-a b-white m-t-10 m-r-10">
                                                    <img width="30" height="30"
                                                         data-src-retina="/assets/img/profiles/1x.jpg"
                                                         data-src="/assets/img/profiles/1.jpg" alt=""
                                                         src="/assets/img/profiles/1.jpg">
                                                </div>
                                                <a href="/#" class="text-complete pull-left">
                                                    <span class="bold">Revox Design Labs</span>
                                                    <span class="fs-12 m-l-10">Owners</span>
                                                </a>
                                                <span class="pull-right time">11:00pm</span>
                                            </div>
                                            <!-- START Notification Item Right Side-->
                                            <div class="option" data-toggle="tooltip" data-placement="left"
                                                 title="mark as read">
                                                <a href="/#" class="mark"></a>
                                            </div>
                                            <!-- END Notification Item Right Side-->
                                        </div>
                                        <!-- END Notification Item-->
                                    </div>
                                    <!-- END Notification Body-->
                                    <!-- START Notification Footer-->
                                    <div class="notification-footer text-center">
                                        <a href="/#" class="">Read all notifications</a>
                                        <a data-toggle="refresh" class="portlet-refresh text-black pull-right"
                                           href="/#">
                                            <i class="pg-refresh_new"></i>
                                        </a>
                                    </div>
                                    <!-- START Notification Footer-->
                                </div>
                                <!-- END Notification -->
                            </div>
                            <!-- END Notification Dropdown -->
                        </div>
                    </li>
                    <li class="p-r-10 inline">
                        <a href="/#" class="header-icon btn-icon-link">
                            <i class="pg-icon">link_alt</i>
                        </a>
                    </li>
                    <li class="p-r-10 inline">
                        <a href="/#" class="header-icon btn-icon-link">
                            <i class="pg-icon">grid_alt</i>
                        </a>
                    </li>
                </ul>
                <!-- END NOTIFICATIONS LIST -->

            </div>
            <div class="d-flex align-items-center">
                <!-- START User Info-->
                <div class="pull-left p-r-10 fs-14 font-heading d-lg-inline-block d-none text-white">
                    <span class="semi-bold">{{ Auth::user()->name }}</span>
                </div>
                <div class="dropdown pull-right d-lg-block">
                    <button class="profile-dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false" aria-label="profile dropdown">
                        <span class="thumbnail-wrapper d32 circular inline">
                            <img src="/assets/img/profiles/avatar.jpg" alt="" data-src="/assets/img/profiles/avatar.jpg"
                                 data-src-retina="/assets/img/profiles/avatar_small2x.jpg" width="32" height="32">
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right profile-dropdown" role="menu">
                        <a href="/#" class="dropdown-item"><span>Signed in as
                                <br/><b>{{ Auth::user()->name }} </b></span></a>
                        <div class="dropdown-divider"></div>
                        <a href="/#" class="dropdown-item">Your Profile</a>
                        <a href="/#" class="dropdown-item">Your Activity</a>
                        <a href="/#" class="dropdown-item">Your Archive</a>
                        <div class="dropdown-divider"></div>
                        <a href="/#" class="dropdown-item">Settings</a>
                        <a href="/#" class="dropdown-item">Logout</a>
                        <div class="dropdown-divider"></div>
                        <span class="dropdown-item fs-12 hint-text"></span>
                    </div>
                </div>
                <!-- END User Info-->

            </div>
        </div>
        <div class="bg-white">
            <div class="container">
                <div class="menu-bar header-sm-height" data-pages-init='horizontal-menu' data-hide-extra-li="4">
                    <a href="/#" class="btn-link header-icon toggle-sidebar d-lg-none" data-toggle="horizontal-menu">
                        <i class="pg-icon">close</i>
                    </a>
                    <ul>
                        <li>
                            <a href="/#">Home</a>
                        </li>
                        <li>
                            <a><span class="title">PSO Assist</span>
                                <span class=" arrow"></span></a>
                            <ul class="">
                                <li class="">
                                    <a href="/assist/init"><i class="pg-icon">grid_alt</i> Initial Load</a>
                                </li>
                                <li class="">
                                    <a href="/assist/rota"><i class="pg-icon">flag</i> Rota To DSE</a>
                                </li>
                                <li class="">
                                    <a href="/assist/usage">Usage</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                    <a href="/#" class="search-link d-flex justify-content-between align-items-center d-lg-none"
                       data-toggle="search">Tap here to search <i class="pg-search float-right"></i></a>
                </div>
            </div>
        </div>
    </div>
    <div class="page-container ">
        <!-- START PAGE CONTENT WRAPPER -->
        <div class="page-content-wrapper ">
            <!-- START PAGE CONTENT -->
            <div class="content ">
                <div class="bg-white">
                    <div class="container">
                        <ol class="breadcrumb breadcrumb-alt">
                            <li class="breadcrumb-item"><a href="/#">Pages</a></li>
                            <li class="breadcrumb-item active">Blank template</li>
                        </ol>
                    </div>
                </div>
                <!-- START CONTAINER FLUID -->
                <div class=" container container-fixed-lg">
                    <!-- BEGIN PlACE PAGE CONTENT HERE -->
                    @yield('content')
                    <!-- END PLACE PAGE CONTENT HERE -->
                </div>
                <!-- END CONTAINER FLUID -->
            </div>
            <!-- END PAGE CONTENT -->
            <!-- START COPYRIGHT -->
            <!-- START CONTAINER FLUID -->
            <div class=" container   container-fixed-lg footer">
                <div class="copyright sm-text-center">
                    <p class="small-text no-margin pull-left sm-pull-reset">

                        <span class="hint-text m-l-15">v0.02</span>
                    </p>
                    <p class="small no-margin pull-right sm-pull-reset">
                        Mostly <span class="hint-text"> made by Ish</span>
                    </p>
                    <div class="clearfix"></div>
                </div>
            </div>
            <!-- END COPYRIGHT -->
        </div>
        <!-- END PAGE CONTENT WRAPPER -->
    </div>
    <!-- END PAGE CONTAINER -->
    <!--START QUICKVIEW -->


    <!-- BEGIN VENDOR JS -->
    <!-- BEGIN VENDOR JS -->
    {{--    <script src="/assets/plugins/pace/pace.min.js" type="text/javascript"></script>--}}
    <script src="/assets/plugins/jquery/jquery-3.2.1.min.js" type="text/javascript"></script>
    <script src="/assets/plugins/modernizr.custom.js" type="text/javascript"></script>
    {{--    <script src="/assets/plugins/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>--}}
    <script src="/assets/plugins/popper/umd/popper.min.js" type="text/javascript"></script>
    <script src="/assets/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="/assets/plugins/jquery/jquery-easy.js" type="text/javascript"></script>
    <script src="/assets/plugins/jquery-unveil/jquery.unveil.min.js" type="text/javascript"></script>
    <script src="/assets/plugins/jquery-ios-list/jquery.ioslist.min.js" type="text/javascript"></script>
    <script src="/assets/plugins/jquery-actual/jquery.actual.min.js"></script>
    <script src="/assets/plugins/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <script type="text/javascript" src="/assets/plugins/jquery-inputmask/jquery.inputmask.min.js"></script>
    <script type="text/javascript" src="/assets/plugins/select2/js/select2.full.min.js"></script>
    <script type="text/javascript" src="/assets/plugins/classie/classie.js"></script>
    <script type="text/javascript" src="/assets/plugins/jquery-autonumeric/autoNumeric.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/9000.0.1/components/prism-core.min.js"></script>
    {{--    <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/11.6.0/highlight.min.js"></script>--}}
    <!-- END VENDOR JS -->
    <!-- BEGIN CORE TEMPLATE JS -->
    <script src="/pages/js/pages.min.js" type="text/javascript"></script>
    <script>
        (function ($) {

            'use strict';

            var getBaseURL = function () {
                var url = document.URL;
                return url.substr(0, url.lastIndexOf('/'));
            }

            $(document).ready(function () {
                    // Input mask - Input helper
                    $(function ($) {
                        $("#date").mask("99/99/9999");
                        $("#phone").mask("(999) 999-9999");
                        $("#tin").mask("99-9999999");
                        $("#ssn").mask("999-99-9999");
                    });
                    // Autonumeric plug-in - automatic addition of dollar signs,etc controlled by tag attributes
                    $('.autonumeric').autoNumeric('init');

                    // hljs.highlightAll();

                }
            )
        })(window.jQuery);
    </script>
    <!-- END CORE TEMPLATE JS -->
    <!-- BEGIN PAGE LEVEL JS -->
    <script src="/assets/js/scripts.js" type="text/javascript"></script>
    <!-- END PAGE LEVEL JS -->
    @livewireScripts
</body>

</html>
