<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="content-type" content="text/html;charset=UTF-8"/>
    <meta charset="utf-8"/>
    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no"/>
    <link rel="apple-touch-icon" href="/pages/ico/60.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/pages/ico/76.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/pages/ico/120.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/pages/ico/152.png">
    <link rel="icon" type="image/x-icon" href="favicon.ico"/>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta content="Meet pages - The simplest and fastest way to build web UI for your dashboard or app."
          name="description"/>
    <meta content="Ace" name="author"/>
    <link href="/assets/plugins/pace/pace-theme-flash.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="/assets/plugins/jquery-scrollbar/jquery.scrollbar.css" rel="stylesheet" type="text/css" media="screen"/>
    <link href="/assets/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" media="screen"/>
    <link class="main-stylesheet" href="/pages/css/themes/modern.css" rel="stylesheet" type="text/css"/>
    <!-- Please remove the file below for production: Contains demo classes -->
    <link class="main-stylesheet" href="/assets/css/style.css" rel="stylesheet" type="text/css"/>
            <meta name="csrf-token" content="{{ csrf_token() }}">
    <script type="text/javascript">
        window.onload = function () {
            // fix for windows 8
            if (navigator.appVersion.indexOf("Windows NT 6.2") !== -1)
                document.head.innerHTML += '<link rel="stylesheet" type="text/css" href="/pages/css/windows.chrome.fix.css" />'
        }
    </script>
</head>
<body class="fixed-header ">
    <div class="register-container full-height sm-p-t-30">
        <div class="d-flex justify-content-center flex-column full-height ">
            <img src="/assets/img/logo.png" alt="logo" data-src="/assets/img/logo.png"
                 data-src-retina="/assets/img/logo_2x.png" width="78" height="22">
            <h3>Pages makes it easy to enjoy what matters the most in your life</h3>
            <p>
                Create a pages account. If you have a facebook account, log into it for this process. Sign in with <a
                    href="#" class="text-info">Facebook</a> or <a href="#" class="text-info">Google</a>
            </p>
            <form id="form-register" class="p-t-15" role="form" method="POST" action="{{ route('register') }}">
                @csrf
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-group-default">
                            <label>Name</label>
                            <input type="text" id="name" name="name" placeholder="Johnny B Good" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-group-default">
                            <label>Email</label>
                            <input type="email" id="email" name="email" placeholder="johnny@begood.com"
                                   class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-group-default">
                            <label>Password</label>
                            <input type="password" id="password" name="pass" placeholder="Make it tough"
                                   class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-group-default">
                            <label>Confirm Password</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Make it tough but the same as above"
                                   class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="row m-t-10">
                    <div class="col-lg-6">
                        <p><small>I agree to the <a href="#" class="text-info">Pages Terms</a> and <a href="#"
                                                                                                      class="text-info">Privacy</a>.</small>
                        </p>
                    </div>
                    <div class="col-lg-6 text-right">
                        <a href="#" class="text-info small">Help? Contact Support</a>
                    </div>
                </div>
                <button aria-label="" class="btn btn-primary btn-cons m-t-10" type="submit">Create a new account
                </button>
            </form>
        </div>
    </div>
    <div class=" full-width">
        <div class="register-container m-b-10 clearfix">
            <div class="m-b-30 sm-m-t-20 sm-p-r-15 sm-p-b-20 clearfix d-flex-md-up">
                <div class="col-md-2 no-padding d-flex align-items-center">
                    <img src="/assets/img/demo/pages_icon.png" alt="" class=""
                         data-src="/assets/img/demo/pages_icon.png" data-src-retina="/assets/img/demo/pages_icon_2x.png"
                         width="60" height="60">
                </div>
                <div class="col-md-9 no-padding d-flex align-items-center">
                    <p class="hinted-text small inline sm-p-t-10">No part of this website or any of its contents may be
                        reproduced, copied, modified or adapted, without the prior written consent of the author, unless
                        otherwise indicated for stand-alone materials.</p>
                </div>
            </div>
        </div>
    </div>
    <!-- START OVERLAY -->

    <!-- END OVERLAY -->
    <!-- BEGIN VENDOR JS -->
{{--    <script src="/assets/plugins/pace/pace.min.js" type="text/javascript"></script>--}}
    <!--  A polyfill for browsers that don't support ligatures: remove liga.js if not needed-->
{{--    <script src="/assets/plugins/liga.js" type="text/javascript"></script>--}}
    <script src="/assets/plugins/jquery/jquery-3.2.1.min.js" type="text/javascript"></script>
    <script src="/assets/plugins/modernizr.custom.js" type="text/javascript"></script>
    <script src="/assets/plugins/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
    <script src="/assets/plugins/popper/umd/popper.min.js" type="text/javascript"></script>
    <script src="/assets/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="/assets/plugins/jquery/jquery-easy.js" type="text/javascript"></script>
    <script src="/assets/plugins/jquery-unveil/jquery.unveil.min.js" type="text/javascript"></script>
    <script src="/assets/plugins/jquery-ios-list/jquery.ioslist.min.js" type="text/javascript"></script>
    <script src="/assets/plugins/jquery-actual/jquery.actual.min.js"></script>
    <script src="/assets/plugins/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <script type="text/javascript" src="/assets/plugins/select2/js/select2.full.min.js"></script>
    <script type="text/javascript" src="/assets/plugins/classie/classie.js"></script>
    <script src="/assets/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
    <!-- END VENDOR JS -->
    <script src="/pages/js/pages.min.js"></script>
    <script>
        $(function () {
            $('#form-register').validate()
        })
    </script>
</body>
</html>
