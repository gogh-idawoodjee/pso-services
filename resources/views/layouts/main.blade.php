<!DOCTYPE html>
<!--
    Template Name: Icewall - HTML Admin Dashboard Template
    Author: Left4code
    Website: http://www.left4code.com/
    Contact: muhammadrizki@left4code.com
    Purchase: https://themeforest.net/user/left4code/portfolio
    Renew Support: https://themeforest.net/user/left4code/portfolio
    License: You must have a valid license purchased only from themeforest(the above link) in order to legally use the theme for your project.
    -->
<html lang="en" class="light">
<!-- BEGIN: Head -->
<head>
    <meta charset="utf-8">
    <link href="/images/logo.svg" rel="shortcut icon">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
          content="Icewall admin is super flexible, powerful, clean & modern responsive tailwind admin template with unlimited possibilities.">
    <meta name="keywords"
          content="admin template, Icewall Admin Template, dashboard template, flat admin template, responsive admin template, web app">
    <meta name="author" content="LEFT4CODE">
    <title>Dashboard - Midone - Tailwind HTML Admin Template</title>
    <!-- BEGIN: CSS Assets-->
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    @livewireStyles
    <!-- END: CSS Assets-->
</head>
<!-- END: Head -->
<body class="main">
    <!-- BEGIN: Mobile Menu -->

    <!-- END: Mobile Menu -->
    <!-- BEGIN: Top Bar -->
    <div
        class="top-bar-boxed h-[70px] z-[51] relative border-b border-white/[0.08] mt-12 md:-mt-5 -mx-3 sm:-mx-8 px-3 sm:px-8 md:pt-0 mb-12">
        <div class="h-full flex items-center">
            <!-- BEGIN: Logo -->
            <a href="" class="-intro-x hidden md:flex">
                <img alt="Midone - HTML Admin Template" class="w-6" src="/images/logo.svg">
                <span class="text-white text-lg ml-3"> IshTools </span>
            </a>
            <!-- END: Logo -->
            <!-- BEGIN: Breadcrumb -->
            <nav aria-label="breadcrumb" class="-intro-x h-full mr-auto">
                <ol class="breadcrumb breadcrumb-light">
                    <li class="breadcrumb-item"><a href="#">{{Str::studly(Request::segment(1))}}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{Str::studly(Request::segment(2))}}</li>
                </ol>
            </nav>
            <!-- END: Breadcrumb -->

            <!-- BEGIN: Notifications -->
            <div class="intro-x dropdown mr-4 sm:mr-6">
                <div class="dropdown-toggle notification notification--bullet cursor-pointer" role="button"
                     aria-expanded="false" data-tw-toggle="dropdown"><i data-lucide="bell"
                                                                        class="notification__icon dark:text-slate-500"></i>
                </div>
                <div class="notification-content pt-2 dropdown-menu">
                    <div class="notification-content__box dropdown-content">
                        <div class="notification-content__title">Notifications</div>
                        <div class="cursor-pointer relative flex items-center ">
                            <div class="w-12 h-12 flex-none image-fit mr-1">
                                <img alt="Midone - HTML Admin Template" class="rounded-full"
                                     src="/images/profile-2.jpg">
                                <div
                                    class="w-3 h-3 bg-success absolute right-0 bottom-0 rounded-full border-2 border-white"></div>
                            </div>
                            <div class="ml-2 overflow-hidden">
                                <div class="flex items-center">
                                    <a href="javascript:;" class="font-medium truncate mr-5">Al Pacino</a>
                                    <div class="text-xs text-slate-400 ml-auto whitespace-nowrap">05:09 AM</div>
                                </div>
                                <div class="w-full truncate text-slate-500 mt-0.5">There are many variations of passages
                                    of Lorem Ipsum available, but the majority have suffered alteration in some form, by
                                    injected humour, or randomi
                                </div>
                            </div>
                        </div>
                        <div class="cursor-pointer relative flex items-center mt-5">
                            <div class="w-12 h-12 flex-none image-fit mr-1">
                                <img alt="Midone - HTML Admin Template" class="rounded-full"
                                     src="/images/profile-6.jpg">
                                <div
                                    class="w-3 h-3 bg-success absolute right-0 bottom-0 rounded-full border-2 border-white"></div>
                            </div>
                            <div class="ml-2 overflow-hidden">
                                <div class="flex items-center">
                                    <a href="javascript:;" class="font-medium truncate mr-5">Russell Crowe</a>
                                    <div class="text-xs text-slate-400 ml-auto whitespace-nowrap">01:10 PM</div>
                                </div>
                                <div class="w-full truncate text-slate-500 mt-0.5">Lorem Ipsum is simply dummy text of
                                    the printing and typesetting industry. Lorem Ipsum has been the industry&#039;s
                                    standard dummy text ever since the 1500
                                </div>
                            </div>
                        </div>
                        <div class="cursor-pointer relative flex items-center mt-5">
                            <div class="w-12 h-12 flex-none image-fit mr-1">
                                <img alt="Midone - HTML Admin Template" class="rounded-full"
                                     src="/images/profile-12.jpg">
                                <div
                                    class="w-3 h-3 bg-success absolute right-0 bottom-0 rounded-full border-2 border-white"></div>
                            </div>
                            <div class="ml-2 overflow-hidden">
                                <div class="flex items-center">
                                    <a href="javascript:;" class="font-medium truncate mr-5">Brad Pitt</a>
                                    <div class="text-xs text-slate-400 ml-auto whitespace-nowrap">06:05 AM</div>
                                </div>
                                <div class="w-full truncate text-slate-500 mt-0.5">Lorem Ipsum is simply dummy text of
                                    the printing and typesetting industry. Lorem Ipsum has been the industry&#039;s
                                    standard dummy text ever since the 1500
                                </div>
                            </div>
                        </div>
                        <div class="cursor-pointer relative flex items-center mt-5">
                            <div class="w-12 h-12 flex-none image-fit mr-1">
                                <img alt="Midone - HTML Admin Template" class="rounded-full"
                                     src="/images/profile-3.jpg">
                                <div
                                    class="w-3 h-3 bg-success absolute right-0 bottom-0 rounded-full border-2 border-white"></div>
                            </div>
                            <div class="ml-2 overflow-hidden">
                                <div class="flex items-center">
                                    <a href="javascript:;" class="font-medium truncate mr-5">Johnny Depp</a>
                                    <div class="text-xs text-slate-400 ml-auto whitespace-nowrap">03:20 PM</div>
                                </div>
                                <div class="w-full truncate text-slate-500 mt-0.5">Contrary to popular belief, Lorem
                                    Ipsum is not simply random text. It has roots in a piece of classical Latin
                                    literature from 45 BC, making it over 20
                                </div>
                            </div>
                        </div>
                        <div class="cursor-pointer relative flex items-center mt-5">
                            <div class="w-12 h-12 flex-none image-fit mr-1">
                                <img alt="Midone - HTML Admin Template" class="rounded-full"
                                     src="/images/profile-1.jpg">
                                <div
                                    class="w-3 h-3 bg-success absolute right-0 bottom-0 rounded-full border-2 border-white"></div>
                            </div>
                            <div class="ml-2 overflow-hidden">
                                <div class="flex items-center">
                                    <a href="javascript:;" class="font-medium truncate mr-5">Nicolas Cage</a>
                                    <div class="text-xs text-slate-400 ml-auto whitespace-nowrap">01:10 PM</div>
                                </div>
                                <div class="w-full truncate text-slate-500 mt-0.5">There are many variations of passages
                                    of Lorem Ipsum available, but the majority have suffered alteration in some form, by
                                    injected humour, or randomi
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END: Notifications -->
            <!-- BEGIN: Account Menu -->
            <div class="intro-x dropdown w-8 h-8">
                <div class="dropdown-toggle w-8 h-8 rounded-full overflow-hidden shadow-lg image-fit zoom-in scale-110"
                     role="button" aria-expanded="false" data-tw-toggle="dropdown">
                    <img alt="Midone - HTML Admin Template" src="/images/profile-9.jpg">
                </div>
                <div class="dropdown-menu w-56">
                    <ul class="dropdown-content bg-primary/80 before:block before:absolute before:bg-black before:inset-0 before:rounded-md before:z-[-1] text-white">
                        <li class="p-2">
                            <div class="font-medium">Al Pacino</div>
                            <div class="text-xs text-white/60 mt-0.5 dark:text-slate-500">Software Engineer</div>
                        </li>
                        <li>
                            <hr class="dropdown-divider border-white/[0.08]">
                        </li>
                        <li>
                            <a href="" class="dropdown-item hover:bg-white/5"> <i data-lucide="user"
                                                                                  class="w-4 h-4 mr-2"></i> Profile </a>
                        </li>
                        <li>
                            <a href="" class="dropdown-item hover:bg-white/5"> <i data-lucide="edit"
                                                                                  class="w-4 h-4 mr-2"></i> Add Account
                            </a>
                        </li>
                        <li>
                            <a href="" class="dropdown-item hover:bg-white/5"> <i data-lucide="lock"
                                                                                  class="w-4 h-4 mr-2"></i> Reset
                                Password </a>
                        </li>
                        <li>
                            <a href="" class="dropdown-item hover:bg-white/5"> <i data-lucide="help-circle"
                                                                                  class="w-4 h-4 mr-2"></i> Help </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider border-white/[0.08]">
                        </li>
                        <li>
                            <a href="" class="dropdown-item hover:bg-white/5"> <i data-lucide="toggle-right"
                                                                                  class="w-4 h-4 mr-2"></i> Logout </a>
                        </li>
                    </ul>
                </div>
            </div>
            <!-- END: Account Menu -->
        </div>
    </div>
    <!-- END: Top Bar -->
    <div class="wrapper">
        <div class="wrapper-box">
            <!-- BEGIN: Side Menu -->
            <nav class="side-nav">
                <ul>
                    <li>
                        <a href="javascript:;.html" class="side-menu ">
                            <div class="side-menu__icon"><i data-lucide="home"></i></div>
                            <div class="side-menu__title">
                                Dashboard
                            </div>
                        </a>

                    </li>
                    <li>
                        <a href="javascript:;"
                           class="side-menu @if(Request::segment(1)=='config')side-menu--active @endif">
                            <div class="side-menu__icon"><i data-lucide="box"></i></div>
                            <div class="side-menu__title">
                                Config
                                <div class="side-menu__sub-icon "><i data-lucide="chevron-down"></i></div>
                            </div>
                        </a>
                        <ul class="@if(Request::segment(1)=='config')side-menu__sub-open @endif">
                            <li>
                                <a href="/config/environment"
                                   class="side-menu @if(Request::segment(2)=='environment')side-menu--active @endif">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title"> Environments</div>
                                </a>
                            </li>
                            <li>
                                <a href="simple-menu-light-dashboard-overview-1.html" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title"> Parameters</div>
                                </a>
                            </li>

                        </ul>
                    </li>
                    <li>
                        <a href="javascript:;"
                           class="side-menu @if(Request::segment(1)=='getcrazy')side-menu--active @endif">
                            <div class="side-menu__icon"><i data-lucide="shopping-bag"></i></div>
                            <div class="side-menu__title">
                                GET Crazy
                                <div class="side-menu__sub-icon "><i data-lucide="chevron-down"></i></div>
                            </div>
                        </a>
                        <ul class="@if(Request::segment(1)=='getcrazy')side-menu__sub-open @endif">
                            <li>
                                <a href="side-menu-light-categories.html"
                                   class="side-menu @if(Request::segment(2)=='schedule')side-menu--active @endif">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title"> Schedule</div>
                                </a>
                            </li>
                            <li>
                                <a href="side-menu-light-add-product.html" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title"> Resource</div>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:;" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title">
                                        Activity

                                    </div>
                                </a>

                            </li>
                            <li>
                                <a href="javascript:;" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title">
                                        Usage

                                    </div>
                                </a>

                            </li>

                        </ul>
                    </li>

                    <li class="side-nav__devider my-6"></li>
                    <li>
                        <a href="javascript:;" class="side-menu">
                            <div class="side-menu__icon"><i data-lucide="edit"></i></div>
                            <div class="side-menu__title">
                                Appointment Booking

                            </div>
                        </a>

                    </li>
                    <li>
                        <a href="javascript:;" class="side-menu">
                            <div class="side-menu__icon"><i data-lucide="users"></i></div>
                            <div class="side-menu__title">
                                Commit Engine

                            </div>
                        </a>

                    </li>
                    <li>
                        <a href="javascript:;" class="side-menu">
                            <div class="side-menu__icon"><i data-lucide="trello"></i></div>
                            <div class="side-menu__title">
                                Profile
                                <div class="side-menu__sub-icon "><i data-lucide="chevron-down"></i></div>
                            </div>
                        </a>
                        <ul class="">
                            <li>
                                <a href="side-menu-light-profile-overview-1.html" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title"> Overview 1</div>
                                </a>
                            </li>
                            <li>
                                <a href="side-menu-light-profile-overview-2.html" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title"> Overview 2</div>
                                </a>
                            </li>
                            <li>
                                <a href="side-menu-light-profile-overview-3.html" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title"> Overview 3</div>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="javascript:;" class="side-menu">
                            <div class="side-menu__icon"><i data-lucide="layout"></i></div>
                            <div class="side-menu__title">
                                Pages
                                <div class="side-menu__sub-icon "><i data-lucide="chevron-down"></i></div>
                            </div>
                        </a>
                        <ul class="">
                            <li>
                                <a href="javascript:;" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title">
                                        Wizards
                                        <div class="side-menu__sub-icon "><i data-lucide="chevron-down"></i></div>
                                    </div>
                                </a>
                                <ul class="">
                                    <li>
                                        <a href="side-menu-light-wizard-layout-1.html" class="side-menu">
                                            <div class="side-menu__icon"><i data-lucide="zap"></i></div>
                                            <div class="side-menu__title">Layout 1</div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="side-menu-light-wizard-layout-2.html" class="side-menu">
                                            <div class="side-menu__icon"><i data-lucide="zap"></i></div>
                                            <div class="side-menu__title">Layout 2</div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="side-menu-light-wizard-layout-3.html" class="side-menu">
                                            <div class="side-menu__icon"><i data-lucide="zap"></i></div>
                                            <div class="side-menu__title">Layout 3</div>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <a href="javascript:;" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title">
                                        Blog
                                        <div class="side-menu__sub-icon "><i data-lucide="chevron-down"></i></div>
                                    </div>
                                </a>
                                <ul class="">
                                    <li>
                                        <a href="side-menu-light-blog-layout-1.html" class="side-menu">
                                            <div class="side-menu__icon"><i data-lucide="zap"></i></div>
                                            <div class="side-menu__title">Layout 1</div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="side-menu-light-blog-layout-2.html" class="side-menu">
                                            <div class="side-menu__icon"><i data-lucide="zap"></i></div>
                                            <div class="side-menu__title">Layout 2</div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="side-menu-light-blog-layout-3.html" class="side-menu">
                                            <div class="side-menu__icon"><i data-lucide="zap"></i></div>
                                            <div class="side-menu__title">Layout 3</div>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <a href="javascript:;" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title">
                                        Pricing
                                        <div class="side-menu__sub-icon "><i data-lucide="chevron-down"></i></div>
                                    </div>
                                </a>
                                <ul class="">
                                    <li>
                                        <a href="side-menu-light-pricing-layout-1.html" class="side-menu">
                                            <div class="side-menu__icon"><i data-lucide="zap"></i></div>
                                            <div class="side-menu__title">Layout 1</div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="side-menu-light-pricing-layout-2.html" class="side-menu">
                                            <div class="side-menu__icon"><i data-lucide="zap"></i></div>
                                            <div class="side-menu__title">Layout 2</div>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <a href="javascript:;" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title">
                                        Invoice
                                        <div class="side-menu__sub-icon "><i data-lucide="chevron-down"></i></div>
                                    </div>
                                </a>
                                <ul class="">
                                    <li>
                                        <a href="side-menu-light-invoice-layout-1.html" class="side-menu">
                                            <div class="side-menu__icon"><i data-lucide="zap"></i></div>
                                            <div class="side-menu__title">Layout 1</div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="side-menu-light-invoice-layout-2.html" class="side-menu">
                                            <div class="side-menu__icon"><i data-lucide="zap"></i></div>
                                            <div class="side-menu__title">Layout 2</div>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <a href="javascript:;" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title">
                                        FAQ
                                        <div class="side-menu__sub-icon "><i data-lucide="chevron-down"></i></div>
                                    </div>
                                </a>
                                <ul class="">
                                    <li>
                                        <a href="side-menu-light-faq-layout-1.html" class="side-menu">
                                            <div class="side-menu__icon"><i data-lucide="zap"></i></div>
                                            <div class="side-menu__title">Layout 1</div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="side-menu-light-faq-layout-2.html" class="side-menu">
                                            <div class="side-menu__icon"><i data-lucide="zap"></i></div>
                                            <div class="side-menu__title">Layout 2</div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="side-menu-light-faq-layout-3.html" class="side-menu">
                                            <div class="side-menu__icon"><i data-lucide="zap"></i></div>
                                            <div class="side-menu__title">Layout 3</div>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <a href="login-light-login.html" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title"> Login</div>
                                </a>
                            </li>
                            <li>
                                <a href="login-light-register.html" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title"> Register</div>
                                </a>
                            </li>
                            <li>
                                <a href="main-light-error-page.html" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title"> Error Page</div>
                                </a>
                            </li>
                            <li>
                                <a href="side-menu-light-update-profile.html" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title"> Update profile</div>
                                </a>
                            </li>
                            <li>
                                <a href="side-menu-light-change-password.html" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title"> Change Password</div>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="side-nav__devider my-6"></li>
                    <li>
                        <a href="javascript:;" class="side-menu">
                            <div class="side-menu__icon"><i data-lucide="inbox"></i></div>
                            <div class="side-menu__title">
                                Components
                                <div class="side-menu__sub-icon "><i data-lucide="chevron-down"></i></div>
                            </div>
                        </a>
                        <ul class="">
                            <li>
                                <a href="javascript:;" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title">
                                        Table
                                        <div class="side-menu__sub-icon "><i data-lucide="chevron-down"></i></div>
                                    </div>
                                </a>
                                <ul class="">
                                    <li>
                                        <a href="side-menu-light-regular-table.html" class="side-menu">
                                            <div class="side-menu__icon"><i data-lucide="zap"></i></div>
                                            <div class="side-menu__title">Regular Table</div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="side-menu-light-tabulator.html" class="side-menu">
                                            <div class="side-menu__icon"><i data-lucide="zap"></i></div>
                                            <div class="side-menu__title">Tabulator</div>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <a href="javascript:;" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title">
                                        Overlay
                                        <div class="side-menu__sub-icon "><i data-lucide="chevron-down"></i></div>
                                    </div>
                                </a>
                                <ul class="">
                                    <li>
                                        <a href="side-menu-light-modal.html" class="side-menu">
                                            <div class="side-menu__icon"><i data-lucide="zap"></i></div>
                                            <div class="side-menu__title">Modal</div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="side-menu-light-slide-over.html" class="side-menu">
                                            <div class="side-menu__icon"><i data-lucide="zap"></i></div>
                                            <div class="side-menu__title">Slide Over</div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="side-menu-light-notification.html" class="side-menu">
                                            <div class="side-menu__icon"><i data-lucide="zap"></i></div>
                                            <div class="side-menu__title">Notification</div>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <a href="side-menu-light-tab.html" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title"> Tab</div>
                                </a>
                            </li>
                            <li>
                                <a href="side-menu-light-accordion.html" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title"> Accordion</div>
                                </a>
                            </li>
                            <li>
                                <a href="side-menu-light-button.html" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title"> Button</div>
                                </a>
                            </li>
                            <li>
                                <a href="side-menu-light-alert.html" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title"> Alert</div>
                                </a>
                            </li>
                            <li>
                                <a href="side-menu-light-progress-bar.html" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title"> Progress Bar</div>
                                </a>
                            </li>
                            <li>
                                <a href="side-menu-light-tooltip.html" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title"> Tooltip</div>
                                </a>
                            </li>
                            <li>
                                <a href="side-menu-light-dropdown.html" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title"> Dropdown</div>
                                </a>
                            </li>
                            <li>
                                <a href="side-menu-light-typography.html" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title"> Typography</div>
                                </a>
                            </li>
                            <li>
                                <a href="side-menu-light-icon.html" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title"> Icon</div>
                                </a>
                            </li>
                            <li>
                                <a href="side-menu-light-loading-icon.html" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title"> Loading Icon</div>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="javascript:;" class="side-menu">
                            <div class="side-menu__icon"><i data-lucide="sidebar"></i></div>
                            <div class="side-menu__title">
                                Forms
                                <div class="side-menu__sub-icon "><i data-lucide="chevron-down"></i></div>
                            </div>
                        </a>
                        <ul class="">
                            <li>
                                <a href="side-menu-light-regular-form.html" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title"> Regular Form</div>
                                </a>
                            </li>
                            <li>
                                <a href="side-menu-light-datepicker.html" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title"> Datepicker</div>
                                </a>
                            </li>
                            <li>
                                <a href="side-menu-light-tom-select.html" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title"> Tom Select</div>
                                </a>
                            </li>
                            <li>
                                <a href="side-menu-light-file-upload.html" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title"> File Upload</div>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:;" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title">
                                        Wysiwyg Editor
                                        <div class="side-menu__sub-icon "><i data-lucide="chevron-down"></i></div>
                                    </div>
                                </a>
                                <ul class="">
                                    <li>
                                        <a href="side-menu-light-wysiwyg-editor-classic.html" class="side-menu">
                                            <div class="side-menu__icon"><i data-lucide="zap"></i></div>
                                            <div class="side-menu__title">Classic</div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="side-menu-light-wysiwyg-editor-inline.html" class="side-menu">
                                            <div class="side-menu__icon"><i data-lucide="zap"></i></div>
                                            <div class="side-menu__title">Inline</div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="side-menu-light-wysiwyg-editor-balloon.html" class="side-menu">
                                            <div class="side-menu__icon"><i data-lucide="zap"></i></div>
                                            <div class="side-menu__title">Balloon</div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="side-menu-light-wysiwyg-editor-balloon-block.html" class="side-menu">
                                            <div class="side-menu__icon"><i data-lucide="zap"></i></div>
                                            <div class="side-menu__title">Balloon Block</div>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="side-menu-light-wysiwyg-editor-document.html" class="side-menu">
                                            <div class="side-menu__icon"><i data-lucide="zap"></i></div>
                                            <div class="side-menu__title">Document</div>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <a href="side-menu-light-validation.html" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title"> Validation</div>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="javascript:;" class="side-menu">
                            <div class="side-menu__icon"><i data-lucide="hard-drive"></i></div>
                            <div class="side-menu__title">
                                Widgets
                                <div class="side-menu__sub-icon "><i data-lucide="chevron-down"></i></div>
                            </div>
                        </a>
                        <ul class="">
                            <li>
                                <a href="side-menu-light-chart.html" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title"> Chart</div>
                                </a>
                            </li>
                            <li>
                                <a href="side-menu-light-slider.html" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title"> Slider</div>
                                </a>
                            </li>
                            <li>
                                <a href="side-menu-light-image-zoom.html" class="side-menu">
                                    <div class="side-menu__icon"><i data-lucide="activity"></i></div>
                                    <div class="side-menu__title"> Image Zoom</div>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>
            <!-- END: Side Menu -->
            <!-- BEGIN: Content -->
            <div class="content">
                <div class="grid grid-cols-12 gap-6">
                    <div class="col-span-12 2xl:col-span-12">
                        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible mt-5">
                            @yield('content')
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- END: Content -->
    </div>
    </div>
    <!-- BEGIN: Dark Mode Switcher-->
    <div data-url="/"
         class="dark-mode-switcher cursor-pointer shadow-md fixed bottom-0 right-0 box border rounded-full w-40 h-12 flex items-center justify-center z-50 mb-10 mr-10">
        <div class="mr-4 text-slate-600 dark:text-slate-200">Dark Mode</div>
        <div class="dark-mode-switcher__toggle border"></div>
    </div>
    <!-- END: Dark Mode Switcher-->

    <!-- BEGIN: JS Assets-->
    <script
        src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=[your-google-map-api]&libraries=places"></script>

    <script src="{{ mix('js/app.js') }}"></script>
    @livewireScripts
    <!-- END: JS Assets-->
</body>
</html>
