<div>
    <div class="content">
        <div class="relative">
            <div class="grid grid-cols-12 gap-6">
                <div class="col-span-12 xl:col-span-9 2xl:col-span-9 z-10">
                    <div class="mt-6 -mb-6 intro-y">
                        <div class="alert alert-dismissible show box bg-primary text-white flex items-center mb-6"
                             role="alert">
                            <span>
                                Some cool stuff here <a
                                    href="https://themeforest.net/item/midone-jquery-tailwindcss-html-admin-template/26366820"
                                    class="underline ml-1" target="blank">something something</a>.
                                <button
                                    class="rounded-md bg-white bg-opacity-20 hover:bg-opacity-30 py-0.5 px-2 -my-3 ml-2">
                                    Something else
                                </button>
                            </span>
                            <button type="button" class="btn-close text-white" data-tw-dismiss="alert"
                                    aria-label="Close"><i data-lucide="x" class="w-4 h-4"></i></button>
                        </div>
                    </div>
                    <div class="mt-14 mb-3 grid grid-cols-12 sm:gap-10 intro-y">
                        <div
                            class="col-span-12 sm:col-span-6 md:col-span-4 py-6 sm:pl-5 md:pl-0 lg:pl-5 relative text-center sm:text-left">
                            <div class="absolute pt-0.5 2xl:pt-0 mt-5 2xl:mt-6 top-0 right-0 dropdown">
                                <a class="dropdown-toggle block" href="javascript:;" aria-expanded="false"
                                   data-tw-toggle="dropdown"> <i data-lucide="more-vertical"
                                                                 class="w-5 h-5 text-slate-500"></i> </a>
                                <div class="dropdown-menu w-40">
                                    <ul class="dropdown-content">
                                        <li>
                                            <a href="" class="dropdown-item"> <i data-lucide="file-text"
                                                                                 class="w-4 h-4 mr-2"></i> Monthly
                                                Report </a>
                                        </li>
                                        <li>
                                            <a href="" class="dropdown-item"> <i data-lucide="file-text"
                                                                                 class="w-4 h-4 mr-2"></i> Annual Report
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div
                                class="text-base 2xl:text-lg justify-center sm:justify-start flex items-center text-slate-600 leading-3">
                                Resource ID {{$resource['Resources']['id']}} </div>
                            <div class="2xl:flex mt-5 mb-3">
                                <div class="flex items-center justify-center sm:justify-start">
                                    <div class="relative text-2xl 2xl:text-3xl font-medium leading-6 ">
                                        {{$resource['Resources']['first_name'].' '.$resource['Resources']['surname']}} </div>
                                    {{--                                    <a class="text-slate-500 ml-4 2xl:ml-16" href=""> <i data-lucide="refresh-ccw" class="w-4 h-4"></i> </a>--}}
                                </div>

                            </div>
                            <div class="text-slate-500">Type: {{$resource['Resources']['resource_type_id']}}</div>
                            <div class="2xl:text-base text-slate-600 mt-6 -mb-1">
                                Memo: {{$resource['Resources']['memo']}} </div>
                            <div class="2xl:text-base text-slate-600 mt-6 -mb-1">
                                <button class="btn btn-primary w-42 mr-2 mb-2">
                                    <i data-lucide="alarm-clock" class="w-4 h-4 mr-2"></i> Attention On
                                </button>
                                <button class="btn btn-dark  mr-2 mb-2">
                                    <i data-lucide="alarm-clock-off" class="w-4 h-4 mr-2"></i> Attention Off
                                </button>
                                <button class="btn btn-success  mr-2 mb-2"><i data-lucide="power"
                                                                              class="w-4 h-4 mr-2"></i> Sign On
                                </button>
                                <button class="btn btn-warning w-42 mr-2 mb-2"><i data-lucide="power-off"
                                                                                  class="w-4 h-4 mr-2"></i> Sign Off
                                </button>

                            </div>

                            {{--                            <div class="mt-14 2xl:mt-24 dropdown">--}}
                            {{--                                <button class="dropdown-toggle btn btn-rounded-primary w-44 2xl:w-52 px-4 relative justify-start" aria-expanded="false" data-tw-toggle="dropdown">--}}
                            {{--                                    Download Reports--}}
                            {{--                                    <span class="w-8 h-8 absolute flex justify-center items-center right-0 top-0 bottom-0 my-auto ml-auto mr-1"> <i data-lucide="chevron-down" class="w-4 h-4"></i> </span>--}}
                            {{--                                </button>--}}
                            {{--                                <div class="dropdown-menu w-44 2xl:w-52">--}}
                            {{--                                    <ul class="dropdown-content">--}}
                            {{--                                        <li>--}}
                            {{--                                            <a href="" class="dropdown-item"> <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Monthly Report </a>--}}
                            {{--                                        </li>--}}
                            {{--                                        <li>--}}
                            {{--                                            <a href="" class="dropdown-item"> <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Annual Report </a>--}}
                            {{--                                        </li>--}}
                            {{--                                    </ul>--}}
                            {{--                                </div>--}}
                            {{--                            </div>--}}
                        </div>
                        <div
                            class="row-start-2 md:row-start-auto col-span-12 md:col-span-4 py-6 border-black border-opacity-10 border-t md:border-t-0 md:border-l md:border-r border-dashed px-10 sm:px-28 md:px-5 -mx-5">
                            <div class="flex flex-wrap items-center">
                                <div
                                    class="flex items-center w-full sm:w-auto justify-center sm:justify-start mr-auto mb-5 2xl:mb-0">
                                    <div class="w-2 h-2 bg-primary rounded-full -mt-4"></div>
                                    <div class="ml-3.5">
                                        <div
                                            class="relative text-xl 2xl:text-2xl font-medium leading-6 2xl:leading-5 pl-3.5 2xl:pl-4">
                                            <span
                                                class="absolute text-base 2xl:text-xl top-0 left-0 2xl:-mt-1.5">$</span>
                                            47,578.77
                                        </div>
{{--                                        <div class="text-slate-500 mt-2">Yearly budget</div>--}}
                                    </div>
                                </div>
                                <select
                                    class="form-select bg-transparent border-black border-opacity-10 mx-auto sm:mx-0 py-1.5 px-3 w-auto -mt-2">
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Yearly</option>
                                    <option value="custom-date">Custom Date</option>
                                </select>
                            </div>

                            <div class="mt-6">
                                <div class="report-maps mt-5 bg-slate-200 rounded-md"
                                     data-center="{{$locations[0]['latitude'].','.$locations[0]['longitude']}}"></div>
                            </div>
                        </div>
                        <div
                            class="col-span-12 sm:col-span-6 md:col-span-4 py-6 border-black border-opacity-10 border-t sm:border-t-0 border-l md:border-l-0 border-dashed -ml-4 pl-4 md:ml-0 md:pl-0">
                            {{--                            <ul class=" nav nav-pills w-3/4 2xl:w-4/6 bg-slate-200 rounded-md mx-auto p-1 "--}}
                            {{--                                role="tablist">--}}
                            {{--                                <li id="active-users-tab" class="nav-item flex-1" role="presentation">--}}
                            {{--                                    <button class="nav-link w-full py-1.5 px-2 active" data-tw-toggle="pill"--}}
                            {{--                                            data-tw-target="#active-users" type="button" role="tab"--}}
                            {{--                                            aria-controls="active-users" aria-selected="true"> Active--}}
                            {{--                                    </button>--}}
                            {{--                                </li>--}}
                            {{--                                <li id="inactive-users-tab" class="nav-item flex-1" role="presentation">--}}
                            {{--                                    <button class="nav-link w-full py-1.5 px-2" data-tw-toggle="pill"--}}
                            {{--                                            data-tw-target="#inactive-users" type="button" role="tab"--}}
                            {{--                                            aria-selected="false"> Inactive--}}
                            {{--                                    </button>--}}
                            {{--                                </li>--}}
                            {{--                            </ul>--}}
                            <div class="tab-content mt-6">
                                <div class="tab-pane active" id="active-users" role="tabpanel"
                                     aria-labelledby="active-users-tab">
                                    <div class="relative mt-8">
                                        <div class="h-[215px]">
                                            <canvas id="report-donut-chart-3"></canvas>
                                        </div>
                                        <div
                                            class="flex flex-col justify-center items-center absolute w-full h-full top-0 left-0">
                                            <div
                                                class="text-xl 2xl:text-2xl font-medium">{{count($shifts)}}</div>
                                            <div class="text-slate-500 mt-0.5">Generated @if(count($shifts)>1)
                                                    Shifts
                                                @else
                                                    Shift
                                                @endif</div>
                                        </div>
                                    </div>
{{--                                    <div class="mx-auto w-10/12 2xl:w-2/3 mt-8">--}}
{{--                                        <div class="flex items-center">--}}
{{--                                            <div class="w-2 h-2 bg-primary rounded-full mr-3"></div>--}}
{{--                                            <span class="truncate">17 - 30 Years old</span> <span--}}
{{--                                                class="font-medium ml-auto">62%</span>--}}
{{--                                        </div>--}}
{{--                                        <div class="flex items-center mt-4">--}}
{{--                                            <div class="w-2 h-2 bg-pending rounded-full mr-3"></div>--}}
{{--                                            <span class="truncate">31 - 50 Years old</span> <span--}}
{{--                                                class="font-medium ml-auto">33%</span>--}}
{{--                                        </div>--}}
{{--                                        <div class="flex items-center mt-4">--}}
{{--                                            <div class="w-2 h-2 bg-warning rounded-full mr-3"></div>--}}
{{--                                            <span class="truncate">>= 50 Years old</span> <span--}}
{{--                                                class="font-medium ml-auto">10%</span>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="report-box-3 px-5 pt-8 pb-14 col-span-12 z-10">
                    <div class="grid grid-cols-12 gap-6 relative intro-y">
                        <div class="col-span-12 sm:col-span-4 xl:col-span-3 px-0 lg:px-6 xl:px-0 2xl:px-6">
                            <div class="flex items-center flex-wrap lg:flex-nowrap gap-3">
                                <div class="sm:w-full lg:w-auto text-lg font-medium truncate mr-auto">Utilization Per
                                    Shift
                                </div>
                                <div
                                    class="py-1 px-2.5 rounded-full text-xs bg-slate-300/50 text-slate-600 cursor-pointer truncate">

                                </div>
                            </div>
                            <div class="px-10 sm:px-0">
                                <div class="h-[110px]">
                                    <canvas class="utilization-chart -ml-1 mt-8 -mb-7"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-4 xl:col-span-3 px-0 lg:px-6 xl:px-0 2xl:px-6">
                            <div class="flex items-center flex-wrap lg:flex-nowrap gap-3">
                                {{--                                <div class="sm:w-full lg:w-auto text-lg font-medium truncate mr-auto">Social Media</div>--}}
                                {{--                                <a href="" class="flex items-center text-primary">--}}
                                {{--                                    <div class="truncate 2xl:mr-auto">View Details</div>--}}
                                {{--                                    <i data-lucide="arrow-right" class="w-4 h-4 ml-3"></i>--}}
                                {{--                                </a>--}}
                            </div>
                            <div class="flex items-center justify-center mt-10">
                                <div class="text-right">
                                    <div
                                        class="text-3xl font-medium">{{$resource['Plan_Resource']['total_allocations']}}</div>
                                    <div class="truncate mt-1 text-slate-500">Allocations including NAs</div>
                                </div>
                                <div
                                    class="w-px h-16 border border-r border-dashed border-slate-300 mx-4 xl:mx-6"></div>
                                <div>
                                    <div class="text-3xl font-medium">135</div>
                                    <div class="truncate mt-1 text-slate-500">Total Something</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-4 xl:col-span-3 px-0 lg:px-6 xl:px-0 2xl:px-6">
                            <div class="flex items-center flex-wrap lg:flex-nowrap gap-3">
                                <div class="sm:w-full lg:w-auto text-lg font-medium truncate mr-auto">Something Here
                                </div>
                                <div
                                    class="py-1 px-2.5 rounded-full text-xs bg-slate-300/50 text-slate-600 cursor-pointer truncate">
                                    666 things
                                </div>
                            </div>
                            <div class="px-10 sm:px-0">
                                <div class="h-[110px]">
                                    <canvas class="simple-line-chart-4 -ml-1 mt-8 -mb-7"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="report-box-4 w-full h-full grid grid-cols-12 gap-6 xl:absolute -mt-8 xl:mt-0 pb-6 xl:pb-0 top-0 right-0 z-30 xl:z-auto">
                <div class="col-span-12 xl:col-span-3 xl:col-start-10 xl:pb-16 z-30">
                    <div class="h-full flex flex-col">
                        <div class="box p-5 mt-6 bg-primary intro-x">
                            <div class="flex flex-wrap gap-3">
                                <div class="mr-auto">
                                    <div
                                        class="text-white text-opacity-70 flex items-center leading-3">
                                        interesting box <i data-lucide="alert-circle" class="tooltip w-4 h-4 ml-1.5"
                                                           title="cool info"></i></div>
                                    <div class="text-white relative text-2xl font-medium leading-5 mt-3.5">
                                        {{--                                        <span class="absolute text-xl top-0 left-0 -mt-1.5"></span>--}}
                                        much things
                                    </div>
                                </div>
                                <a class="flex items-center justify-center w-12 h-12 rounded-full bg-white bg-opacity-20 hover:bg-opacity-30 text-white"
                                   href=""> <i data-lucide="plus" class="w-6 h-6"></i> </a>
                            </div>
                        </div>
                        <div class="report-box-4__content xl:min-h-0 intro-x">
                            <div class="max-h-full xl:overflow-y-auto box mt-5">
                                <div class="xl:sticky top-0 px-5 pt-5 pb-6">
                                    <div class="flex items-center">
                                        <div class="text-lg font-medium truncate mr-5">Parameters</div>
                                        {{--                                        <a href="" class="ml-auto flex items-center text-primary"> <i--}}
                                        {{--                                                data-lucide="refresh-ccw" class="w-4 h-4 mr-3"></i> Refresh </a>--}}
                                    </div>
                                    {{--                                    <ul class=" nav nav-pills border border-slate-300 border-dashed rounded-md mx-auto p-1 mt-5 "--}}
                                    {{--                                        role="tablist">--}}
                                    {{--                                        <li id="weekly-report-tab" class="nav-item flex-1" role="presentation">--}}
                                    {{--                                            <button class="nav-link w-full py-1.5 px-2 active" data-tw-toggle="pill"--}}
                                    {{--                                                    data-tw-target="#weekly-report" type="button" role="tab"--}}
                                    {{--                                                    aria-controls="weekly-report" aria-selected="true"> Weekly--}}
                                    {{--                                            </button>--}}
                                    {{--                                        </li>--}}
                                    {{--                                        <li id="monthly-report-tab" class="nav-item flex-1" role="presentation">--}}
                                    {{--                                            <button class="nav-link w-full py-1.5 px-2" data-tw-toggle="pill"--}}
                                    {{--                                                    data-tw-target="#monthly-report" type="button" role="tab"--}}
                                    {{--                                                    aria-selected="false"> Monthly--}}
                                    {{--                                            </button>--}}
                                    {{--                                        </li>--}}
                                    {{--                                    </ul>--}}
                                </div>
                                <div class="tab-content px-5 pb-5">
                                    <div class="tab-pane active grid grid-cols-10 gap-y-6" id="weekly-report"
                                         role="tabpanel" aria-labelledby="weekly-report-tab">
                                        <div class="col-span-12 sm:col-span-6 md:col-span-4 xl:col-span-12">
                                            <div class="text-slate-500">Max Travel</div>
                                            <div class="mt-1.5 flex items-center">
                                                <div class="text-lg">{{$resource['Resources']['max_travel']}}</div>
                                                <div
                                                    class="text-danger flex text-xs font-medium tooltip cursor-pointer ml-2"
                                                    title="2% Lower than last month"> 2% <i data-lucide="chevron-down"
                                                                                            class="w-4 h-4 ml-0.5"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-span-12 sm:col-span-6 md:col-span-4 xl:col-span-12">
                                            <div class="text-slate-500">In-shift travel to first job</div>
                                            <div class="mt-1.5 flex items-center">
                                                <div class="text-lg">{{$resource['Resources']['travel_to']}}</div>
                                                <div
                                                    class="text-success flex text-xs font-medium tooltip cursor-pointer ml-2"
                                                    title="0.1% Lower than last month"> 49% <i data-lucide="chevron-up"
                                                                                               class="w-4 h-4 ml-0.5"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-span-12 sm:col-span-6 md:col-span-4 xl:col-span-12">
                                            <div class="text-slate-500">In-shift travel to home base</div>
                                            <div class="mt-1.5 flex items-center">
                                                <div class="text-lg">{{$resource['Resources']['max_travel']}}</div>
                                                <div
                                                    class="text-success flex text-xs font-medium tooltip cursor-pointer ml-2"
                                                    title="49% Higher than last month"> 36% <i data-lucide="chevron-up"
                                                                                               class="w-4 h-4 ml-0.5"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-span-12 sm:col-span-6 md:col-span-4 xl:col-span-12">
                                            <div class="text-slate-500">Time Zone</div>
                                            <div class="mt-1.5 flex items-center">
                                                <div class="text-lg">{{$resource['Resources']['time_zone']}}</div>
                                            </div>
                                        </div>
                                        <div class="col-span-12 sm:col-span-6 md:col-span-4 xl:col-span-12">
                                            <div class="text-slate-500">Cost Per Hour</div>
                                            <div class="mt-1.5 flex items-center">
                                                <div
                                                    class="text-lg">{{$resource['Resources']['cost_ph'] ?? $resource['Resource_Type']['cost_ph']}}</div>
                                            </div>
                                        </div>
                                        <div class="col-span-12 sm:col-span-6 md:col-span-4 xl:col-span-12">
                                            <div class="text-slate-500">Cost Per KM</div>
                                            <div class="mt-1.5 flex items-center">
                                                <div
                                                    class="text-lg">{{$resource['Resources']['cost_km'] ?? $resource['Resource_Type']['cost_km']}}</div>
                                                <div
                                                    class="text-danger flex text-xs font-medium tooltip cursor-pointer ml-2"
                                                    title="2% Lower than last month"> 23% <i data-lucide="chevron-down"
                                                                                             class="w-4 h-4 ml-0.5"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-span-12 sm:col-span-6 md:col-span-4 xl:col-span-12">
                                            <div class="text-slate-500">Out Region Multiplier</div>
                                            <div class="mt-1.5 flex items-center">
                                                <div
                                                    class="text-lg">{{$resource['Resources']['out_of_region_multiplier'] ?? $resource['Resource_Type']['out_of_region_multiplier']}}</div>
                                                <div
                                                    class="text-danger flex text-xs font-medium tooltip cursor-pointer ml-2"
                                                    title="2% Lower than last month"> 23% <i data-lucide="chevron-down"
                                                                                             class="w-4 h-4 ml-0.5"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="report-box-3 report-box-3--content grid grid-cols-12 gap-6 xl:-mt-5 2xl:-mt-8 -mb-10 z-40 2xl:z-10">
            <div class="col-span-12 2xl:col-span-9">
                <div class="grid grid-cols-12 gap-6">
                    <!-- BEGIN: Official Store -->
                    <div class="col-span-12 xl:col-span-8 mt-6">

                        <div class="intro-y block sm:flex items-center h-10">
                            <h2 class="text-lg font-medium truncate mr-5">
                                Shifts ({{count($shifts)}})
                            </h2>
                            <div class="sm:ml-auto mt-3 sm:mt-0 relative text-slate-500">
                                {{--                                <i data-lucide="map-pin"--}}
                                {{--                                   class="w-4 h-4 z-10 absolute my-auto inset-y-0 ml-3 left-0"></i>--}}
                                {{--                                <input type="text" class="form-control sm:w-56 box pl-10" placeholder="Filter by city">--}}
                            </div>
                        </div>
                        <div class="col-span-12 mt-6">
                            {{--                            <div class="intro-y block sm:flex items-center h-10">--}}
                            {{--                                <h2 class="text-lg font-medium truncate mr-5">--}}
                            {{--                                    Weekly Top Products--}}
                            {{--                                </h2>--}}
                            {{--                                <div class="flex items-center sm:ml-auto mt-3 sm:mt-0">--}}
                            {{--                                    <button class="btn box flex items-center text-slate-600"><i--}}
                            {{--                                            data-lucide="file-text" class="hidden sm:block w-4 h-4 mr-2"></i> Export to--}}
                            {{--                                        Excel--}}
                            {{--                                    </button>--}}
                            {{--                                    <button class="ml-3 btn box flex items-center text-slate-600"><i--}}
                            {{--                                            data-lucide="file-text" class="hidden sm:block w-4 h-4 mr-2"></i> Export to PDF--}}
                            {{--                                    </button>--}}
                            {{--                                </div>--}}
                            {{--                            </div>--}}
                            <div class="intro-y overflow-auto lg:overflow-visible mt-8 sm:mt-0">
                                @if($shifts)
                                    <div class="intro-y box p-5 mt-5">
                                        <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                                            {{--                                            <form id="tabulator-html-filter-form" class="xl:flex sm:mr-auto" >--}}
                                            {{--                                                <div class="sm:flex items-center sm:mr-4">--}}
                                            {{--                                                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Field</label>--}}
                                            {{--                                                    <select id="tabulator-html-filter-field" class="form-select w-full sm:w-32 2xl:w-full mt-2 sm:mt-0 sm:w-auto">--}}
                                            {{--                                                        <option value="name">Name</option>--}}
                                            {{--                                                        <option value="category">Category</option>--}}
                                            {{--                                                        <option value="remaining_stock">Remaining Stock</option>--}}
                                            {{--                                                    </select>--}}
                                            {{--                                                </div>--}}
                                            {{--                                                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">--}}
                                            {{--                                                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Type</label>--}}
                                            {{--                                                    <select id="tabulator-html-filter-type" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >--}}
                                            {{--                                                        <option value="like" selected>like</option>--}}
                                            {{--                                                        <option value="=">=</option>--}}
                                            {{--                                                        <option value="<">&lt;</option>--}}
                                            {{--                                                        <option value="<=">&lt;=</option>--}}
                                            {{--                                                        <option value=">">></option>--}}
                                            {{--                                                        <option value=">=">>=</option>--}}
                                            {{--                                                        <option value="!=">!=</option>--}}
                                            {{--                                                    </select>--}}
                                            {{--                                                </div>--}}
                                            {{--                                                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">--}}
                                            {{--                                                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Value</label>--}}
                                            {{--                                                    <input id="tabulator-html-filter-value" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0" placeholder="Search...">--}}
                                            {{--                                                </div>--}}
                                            {{--                                                <div class="mt-2 xl:mt-0">--}}
                                            {{--                                                    <button id="tabulator-html-filter-go" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>--}}
                                            {{--                                                    <button id="tabulator-html-filter-reset" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>--}}
                                            {{--                                                </div>--}}
                                            {{--                                            </form>--}}
                                            <div class="flex mt-5 sm:mt-0">
                                                <button id="tabulator-print"
                                                        class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2"><i
                                                        data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                                                </button>
                                                <div class="dropdown w-1/2 sm:w-auto">
                                                    <button
                                                        class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto"
                                                        aria-expanded="false" data-tw-toggle="dropdown"><i
                                                            data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i
                                                            data-lucide="chevron-down"
                                                            class="w-4 h-4 ml-auto sm:ml-2"></i></button>
                                                    <div class="dropdown-menu w-40">
                                                        <ul class="dropdown-content">
                                                            <li>
                                                                <a id="tabulator-export-csv" href="javascript:;"
                                                                   class="dropdown-item"> <i data-lucide="file-text"
                                                                                             class="w-4 h-4 mr-2"></i>
                                                                    Export CSV </a>
                                                            </li>
                                                            <li>
                                                                <a id="tabulator-export-json" href="javascript:;"
                                                                   class="dropdown-item"> <i data-lucide="file-text"
                                                                                             class="w-4 h-4 mr-2"></i>
                                                                    Export JSON </a>
                                                            </li>
                                                            <li>
                                                                <a id="tabulator-export-xlsx" href="javascript:;"
                                                                   class="dropdown-item"> <i data-lucide="file-text"
                                                                                             class="w-4 h-4 mr-2"></i>
                                                                    Export XLSX </a>
                                                            </li>
                                                            <li>
                                                                <a id="tabulator-export-html" href="javascript:;"
                                                                   class="dropdown-item"> <i data-lucide="file-text"
                                                                                             class="w-4 h-4 mr-2"></i>
                                                                    Export HTML </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="overflow-x-auto scrollbar-hidden">
                                            <div id="tabulator" class="mt-5 table-report table-report--tabulator"></div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                        </div>

                    </div>
                    <!-- END: Official Store -->
                    <!-- BEGIN: Weekly Best Sellers -->
                    <div class="col-span-12 xl:col-span-4 mt-6">
                        <div class="intro-y flex items-center h-10">
                            <h2 class="text-lg font-medium truncate mr-5">
                                Skills ({{count($resource['Resource_Skill'])}})
                            </h2>
                        </div>
                        <div class="mt-5">
                            @foreach($resource['Resource_Skill'] as $skill)
                                <div class="intro-y">
                                    <div class="box px-4 py-4 mb-3 flex items-center zoom-in">
                                        <div class="w-10 h-10 flex-none image-fit rounded-md overflow-hidden">
                                            <img alt="Midone - HTML Admin Template" src="/images/profile-10.jpg">
                                        </div>
                                        <div class="ml-4 mr-auto">
                                            <div class="font-medium">{{$skill['skill_id']}}</div>
                                            <div class="text-slate-500 text-xs mt-0.5">@if($skill['proficiency']>1)
                                                    high proficiency
                                                @else
                                                    low proficiency
                                                @endif</div>
                                        </div>
                                        <div
                                            class="py-1 px-2 rounded-full text-xs @if($skill['proficiency']>1)bg-success @else bg-danger @endif text-white cursor-pointer font-medium">
                                            {{$skill['proficiency']}}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <!-- END: Weekly Best Sellers -->
                    <!-- BEGIN: Weekly Top Products -->

                    <!-- END: Weekly Top Products -->
                </div>
            </div>
            <div class="col-span-12 2xl:col-span-3 relative z-10">
                <div class="2xl:border-l pb-10 intro-y">
                    <div class="2xl:pl-6 grid grid-cols-12 gap-x-6 2xl:gap-x-0 gap-y-6">
                        <!-- BEGIN: Recent Activities -->
                        <div class="col-span-12 md:col-span-6 2xl:col-span-12 mt-3 2xl:mt-6">
                            <div class="intro-x flex items-center h-10">
                                <h2 class="text-lg font-medium truncate mr-5">
                                    Recent Events ({{count($events)}})
                                </h2>

                            </div>
                            <div
                                class="mt-5 relative before:block before:absolute before:w-px before:h-[85%] before:bg-slate-200 before:dark:bg-darkmode-400 before:ml-5 before:mt-5">
                                @if(count($events))
                                    @foreach($events as $event)
                                        <div class="intro-x relative flex items-center mb-3">
                                            <div
                                                class="before:block before:absolute before:w-20 before:h-px before:bg-slate-200 before:dark:bg-darkmode-400 before:mt-5 before:ml-5">
                                                <div class="w-10 h-10 flex-none image-fit rounded-full overflow-hidden">
                                                    <img alt="Midone - HTML Admin Template" src="/images/profile-5.jpg">
                                                </div>
                                            </div>
                                            <div class="box px-5 py-3 ml-4 flex-1 zoom-in">
                                                <div class="flex items-center">
                                                    <div class="font-medium">Leonardo DiCaprio</div>
                                                    <div class="text-xs text-slate-500 ml-auto">07:00 PM</div>
                                                </div>
                                                <div class="text-slate-500 mt-1">Has joined the team</div>
                                            </div>
                                        </div>
                                    @endforeach
                                    {{--                                <div class="intro-x text-slate-500 text-xs text-center my-4">12 November</div>--}}
                                @endif
                            </div>
                        </div>
                        <!-- END: Recent Activities -->

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>

        document.addEventListener('livewire:load', function () {

            if ($(".utilization-chart").length) {
                let ctx = $(".utilization-chart")[0].getContext("2d");
                let myChart = new Chart(ctx, {
                    type: "line",
                    data: {
                        labels: [
                            @foreach($utilization['dates'] as $date)
                                '{{$date['date']}}',
                            @endforeach
                        ],
                        datasets: [
                            {
                                label: "Utilization %",
                                data: [
                                    @foreach($utilization['utilization'] as $util)
                                        {{$util['utilization']}},
                                    @endforeach
                                ],
                                borderWidth: 2,
                                borderColor: colors.primary(0.8),
                                backgroundColor: "transparent",
                                pointBorderColor: "transparent",
                                tension: 0.4,
                            },
                            {
                                label: "Avg Travel Time (min)",
                                data: [
                                    @foreach($utilization['travel'] as $travel)
                                        {{$travel['travel']}},
                                    @endforeach
                                ],
                                borderWidth: 2,
                                borderDash: [2, 2],
                                borderColor: $("html").hasClass("dark")
                                    ? colors.darkmode["100"]()
                                    : colors.slate["400"](),
                                backgroundColor: "transparent",
                                pointBorderColor: "transparent",
                                tension: 0.4,
                            },
                        ],
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false,
                            },
                        },
                        scales: {
                            x: {
                                ticks: {
                                    display: false,
                                },
                                grid: {
                                    display: false,
                                    drawBorder: false,
                                },
                            },
                            y: {
                                ticks: {
                                    display: false,
                                },
                                grid: {
                                    display: false,
                                    drawBorder: false,
                                },
                            },
                        },
                    },
                });
            }

            if ($(".report-maps").length) {
                function initMap(el) {
                    var iconBase = {
                        url: $("html").hasClass("dark")
                            ? "/images/map-marker-dark.svg"
                            : "/images/map-marker.svg",
                    };
                    var lightStyle = [
                        {
                            elementType: "geometry",
                            stylers: [
                                {
                                    color: "#f5f5f5",
                                },
                            ],
                        },
                        {
                            elementType: "labels.icon",
                            stylers: [
                                {
                                    visibility: "off",
                                },
                            ],
                        },
                        {
                            elementType: "labels.text.fill",
                            stylers: [
                                {
                                    color: "#616161",
                                },
                            ],
                        },
                        {
                            elementType: "labels.text.stroke",
                            stylers: [
                                {
                                    color: "#f5f5f5",
                                },
                            ],
                        },
                        {
                            featureType: "administrative.land_parcel",
                            elementType: "labels",
                            stylers: [
                                {
                                    visibility: "off",
                                },
                            ],
                        },
                        {
                            featureType: "administrative.land_parcel",
                            elementType: "labels.text.fill",
                            stylers: [
                                {
                                    color: "#bdbdbd",
                                },
                            ],
                        },
                        {
                            featureType: "poi",
                            elementType: "geometry",
                            stylers: [
                                {
                                    color: "#eeeeee",
                                },
                            ],
                        },
                        {
                            featureType: "poi",
                            elementType: "labels.text",
                            stylers: [
                                {
                                    visibility: "off",
                                },
                            ],
                        },
                        {
                            featureType: "poi",
                            elementType: "labels.text.fill",
                            stylers: [
                                {
                                    color: "#757575",
                                },
                            ],
                        },
                        {
                            featureType: "poi.park",
                            elementType: "geometry",
                            stylers: [
                                {
                                    color: "#e5e5e5",
                                },
                            ],
                        },
                        {
                            featureType: "poi.park",
                            elementType: "geometry.fill",
                            stylers: [
                                {
                                    visibility: "off",
                                },
                            ],
                        },
                        {
                            featureType: "poi.park",
                            elementType: "labels.text.fill",
                            stylers: [
                                {
                                    color: "#9e9e9e",
                                },
                            ],
                        },
                        {
                            featureType: "road",
                            elementType: "geometry",
                            stylers: [
                                {
                                    color: "#ffffff",
                                },
                            ],
                        },
                        {
                            featureType: "road.arterial",
                            elementType: "labels",
                            stylers: [
                                {
                                    visibility: "off",
                                },
                            ],
                        },
                        {
                            featureType: "road.arterial",
                            elementType: "labels.text.fill",
                            stylers: [
                                {
                                    color: "#757575",
                                },
                            ],
                        },
                        {
                            featureType: "road.highway",
                            elementType: "geometry",
                            stylers: [
                                {
                                    color: "#dadada",
                                },
                            ],
                        },
                        {
                            featureType: "road.highway",
                            elementType: "labels",
                            stylers: [
                                {
                                    visibility: "off",
                                },
                            ],
                        },
                        {
                            featureType: "road.highway",
                            elementType: "labels.text.fill",
                            stylers: [
                                {
                                    color: "#616161",
                                },
                            ],
                        },
                        {
                            featureType: "road.local",
                            stylers: [
                                {
                                    visibility: "off",
                                },
                            ],
                        },
                        {
                            featureType: "road.local",
                            elementType: "labels",
                            stylers: [
                                {
                                    visibility: "off",
                                },
                            ],
                        },
                        {
                            featureType: "road.local",
                            elementType: "labels.text.fill",
                            stylers: [
                                {
                                    color: "#9e9e9e",
                                },
                            ],
                        },
                        {
                            featureType: "transit.line",
                            elementType: "geometry",
                            stylers: [
                                {
                                    color: "#e5e5e5",
                                },
                            ],
                        },
                        {
                            featureType: "transit.line",
                            elementType: "geometry.fill",
                            stylers: [
                                {
                                    visibility: "off",
                                },
                            ],
                        },
                        {
                            featureType: "transit.station",
                            elementType: "geometry",
                            stylers: [
                                {
                                    color: "#eeeeee",
                                },
                            ],
                        },
                        {
                            featureType: "transit.station",
                            elementType: "geometry.fill",
                            stylers: [
                                {
                                    visibility: "off",
                                },
                            ],
                        },
                        {
                            featureType: "water",
                            elementType: "geometry",
                            stylers: [
                                {
                                    color: "#c9c9c9",
                                },
                            ],
                        },
                        {
                            featureType: "water",
                            elementType: "geometry.fill",
                            stylers: [
                                {
                                    color: "#e0e3e8",
                                },
                            ],
                        },
                        {
                            featureType: "water",
                            elementType: "labels.text.fill",
                            stylers: [
                                {
                                    color: "#9e9e9e",
                                },
                            ],
                        },
                    ];
                    var darkStyle = [
                        {
                            elementType: "geometry",
                            stylers: [
                                {
                                    color: "#242f3e",
                                },
                            ],
                        },
                        {
                            elementType: "labels.text.fill",
                            stylers: [
                                {
                                    color: "#746855",
                                },
                            ],
                        },
                        {
                            elementType: "labels.text.stroke",
                            stylers: [
                                {
                                    color: "#242f3e",
                                },
                            ],
                        },
                        {
                            featureType: "administrative.land_parcel",
                            elementType: "labels",
                            stylers: [
                                {
                                    visibility: "off",
                                },
                            ],
                        },
                        {
                            featureType: "administrative.land_parcel",
                            elementType: "labels.text.fill",
                            stylers: [
                                {
                                    color: "#bdbdbd",
                                },
                            ],
                        },
                        {
                            featureType: "administrative.locality",
                            elementType: "labels.text.fill",
                            stylers: [
                                {
                                    color: "#d59563",
                                },
                            ],
                        },
                        {
                            featureType: "poi",
                            elementType: "geometry",
                            stylers: [
                                {
                                    color: "#eeeeee",
                                },
                            ],
                        },
                        {
                            featureType: "poi",
                            elementType: "labels.text",
                            stylers: [
                                {
                                    visibility: "off",
                                },
                            ],
                        },
                        {
                            featureType: "poi",
                            elementType: "labels.text.fill",
                            stylers: [
                                {
                                    color: "#d59563",
                                },
                            ],
                        },
                        {
                            featureType: "poi.park",
                            elementType: "geometry",
                            stylers: [
                                {
                                    color: "#263c3f",
                                },
                            ],
                        },
                        {
                            featureType: "poi.park",
                            elementType: "geometry.fill",
                            stylers: [
                                {
                                    visibility: "off",
                                },
                            ],
                        },
                        {
                            featureType: "poi.park",
                            elementType: "labels.text.fill",
                            stylers: [
                                {
                                    color: "#6b9a76",
                                },
                            ],
                        },
                        {
                            featureType: "road",
                            elementType: "geometry",
                            stylers: [
                                {
                                    color: "#38414e",
                                },
                            ],
                        },
                        {
                            featureType: "road",
                            elementType: "geometry.stroke",
                            stylers: [
                                {
                                    color: "#212a37",
                                },
                            ],
                        },
                        {
                            featureType: "road",
                            elementType: "labels.text.fill",
                            stylers: [
                                {
                                    color: "#9ca5b3",
                                },
                            ],
                        },
                        {
                            featureType: "road.arterial",
                            elementType: "labels",
                            stylers: [
                                {
                                    visibility: "off",
                                },
                            ],
                        },
                        {
                            featureType: "road.arterial",
                            elementType: "labels.text.fill",
                            stylers: [
                                {
                                    color: "#757575",
                                },
                            ],
                        },
                        {
                            featureType: "road.highway",
                            elementType: "geometry",
                            stylers: [
                                {
                                    color: "#746855",
                                },
                            ],
                        },
                        {
                            featureType: "road.highway",
                            elementType: "geometry.stroke",
                            stylers: [
                                {
                                    color: "#1f2835",
                                },
                            ],
                        },
                        {
                            featureType: "road.highway",
                            elementType: "labels",
                            stylers: [
                                {
                                    visibility: "off",
                                },
                            ],
                        },
                        {
                            featureType: "road.highway",
                            elementType: "labels.text.fill",
                            stylers: [
                                {
                                    color: "#f3d19c",
                                },
                            ],
                        },
                        {
                            featureType: "road.local",
                            stylers: [
                                {
                                    visibility: "off",
                                },
                            ],
                        },
                        {
                            featureType: "road.local",
                            elementType: "labels",
                            stylers: [
                                {
                                    visibility: "off",
                                },
                            ],
                        },
                        {
                            featureType: "transit",
                            elementType: "geometry",
                            stylers: [
                                {
                                    color: "#2f3948",
                                },
                            ],
                        },
                        {
                            featureType: "transit.line",
                            elementType: "geometry",
                            stylers: [
                                {
                                    color: "#e5e5e5",
                                },
                            ],
                        },
                        {
                            featureType: "transit.line",
                            elementType: "geometry.fill",
                            stylers: [
                                {
                                    visibility: "off",
                                },
                            ],
                        },
                        {
                            featureType: "transit.station",
                            elementType: "geometry",
                            stylers: [
                                {
                                    color: "#eeeeee",
                                },
                            ],
                        },
                        {
                            featureType: "transit.station",
                            elementType: "geometry.fill",
                            stylers: [
                                {
                                    visibility: "off",
                                },
                            ],
                        },
                        {
                            featureType: "transit.station",
                            elementType: "labels.text.fill",
                            stylers: [
                                {
                                    color: "#d59563",
                                },
                            ],
                        },
                        {
                            featureType: "water",
                            elementType: "geometry",
                            stylers: [
                                {
                                    color: "#17263c",
                                },
                            ],
                        },
                        {
                            featureType: "water",
                            elementType: "geometry.fill",
                            stylers: [
                                {
                                    color: "#171f29",
                                },
                            ],
                        },
                        {
                            featureType: "water",
                            elementType: "labels.text.fill",
                            stylers: [
                                {
                                    color: "#515c6d",
                                },
                            ],
                        },
                        {
                            featureType: "water",
                            elementType: "labels.text.stroke",
                            stylers: [
                                {
                                    color: "#17263c",
                                },
                            ],
                        },
                    ];
                    var lat = $(el).data("center").split(",")[0];
                    var long = $(el).data("center").split(",")[1];
                    var map = new google.maps.Map(el, {
                        center: new google.maps.LatLng(lat, long),
                        zoom: 10,
                        styles: $("html").hasClass("dark") ? darkStyle : lightStyle,
                        zoomControl: true,
                        zoomControlOptions: {
                            position: google.maps.ControlPosition.LEFT_BOTTOM,
                        },
                        streetViewControl: false,
                    });
                    const myLatLng = {lat: {!! $locations[0]['latitude'] !!}, lng: {!! $locations[0]['longitude'] !!}};
                    const marker = new google.maps.Marker({
                        position: myLatLng,
                        map,
                        title: "Home Location",
                    });

                    const infowindow = new google.maps.InfoWindow({
                        content: 'Home Location',
                    });

                    marker.addListener("click", () => {
                        infowindow.open({
                            anchor: marker,
                            map,
                            ariaLabel: "Uluru",
                        });
                    });
                    // var mcOptions = {
                    //                 styles: [
                    //                     {
                    //                         width: 55,
                    //                         height: 46,
                    //                         textColor: "white",
                    //                         url: $("html").hasClass("dark")
                    //                             ? "/images/map-marker-region-dark.svg"
                    //                             : "/images/map-marker-region.svg",
                    //                         anchor: [0, 0],
                    //                     },
                    //                 ],
                    //             };

                    // new MarkerClusterer(map, [myLatLng], mcOptions);

                    var infoWindow = new google.maps.InfoWindow({
                        minWidth: 400,
                        maxWidth: 400,
                    });

                    // axios
                    //     .get($(el).data("sources"))
                    //     .then(function ({data}) {
                    //         var markersArray = data.map(function (markerElem, i) {
                    //             var point = new google.maps.LatLng(
                    //                 parseFloat(markerElem.latitude),
                    //                 parseFloat(markerElem.longitude)
                    //             );
                    //             var infowincontent =
                    //                 '<div class="font-medium">' +
                    //                 markerElem.name +
                    //                 '</div><div class="mt-1 text-gray-600">Latitude: ' +
                    //                 markerElem.latitude +
                    //                 ", Longitude: " +
                    //                 markerElem.longitude +
                    //                 "</div>";
                    //             var marker = new google.maps.Marker({
                    //                 map: map,
                    //                 position: point,
                    //                 icon: iconBase,
                    //             });
                    //
                    //             google.maps.event.addListener(
                    //                 marker,
                    //                 "click",
                    //                 function (evt) {
                    //                     infoWindow.setContent(infowincontent);
                    //                     google.maps.event.addListener(
                    //                         infoWindow,
                    //                         "domready",
                    //                         function () {
                    //                             $(".arrow_box")
                    //                                 .closest(".gm-style-iw-d")
                    //                                 .removeAttr("style");
                    //                             $(".arrow_box")
                    //                                 .closest(".gm-style-iw-d")
                    //                                 .attr("style", "overflow:visible");
                    //                             $(".arrow_box")
                    //                                 .closest(".gm-style-iw-d")
                    //                                 .parent()
                    //                                 .removeAttr("style");
                    //                         }
                    //                     );
                    //
                    //                     infoWindow.open(map, marker);
                    //                 }
                    //             );
                    //             return marker;
                    //         });
                    //         var mcOptions = {
                    //             styles: [
                    //                 {
                    //                     width: 55,
                    //                     height: 46,
                    //                     textColor: "white",
                    //                     url: $("html").hasClass("dark")
                    //                         ? "/images/map-marker-region-dark.svg"
                    //                         : "/images/map-marker-region.svg",
                    //                     anchor: [0, 0],
                    //                 },
                    //             ],
                    //         };
                    //         new MarkerClusterer(map, markersArray, mcOptions);
                    //     })
                    //     .catch(function (err) {
                    //         console.log(err);
                    //     });
                }

                $(".report-maps").each(function (key, el) {
                    google.maps.event.addDomListener(window, "load", initMap(el));
                });
            }
            // var tabledata = [
            //     {
            //
            //
            //         shift_date: "19/02/1984",
            //         shift_id: "test",
            //         shift_times: "red",
            //         shift_duration: 1,
            //         status: "male",
            //
            //     },
            // ];
            var tabledata = {!! $shifts !!};


            if ($("#tabulator").length) {
                // Setup Tabulator
                let table = new Tabulator("#tabulator", {
                    initialSort: [
                        {column: "shift_date", dir: "asc"}, //sort by this first
                    ],
                    // ajaxURL: "https://dummy-data.left4code.com",
                    data: tabledata,
                    //ajaxFiltering: true,
                    //ajaxSorting: true,
                    // printAsHtml: true,
                    // printStyled: true,
                    pagination: "local",
                    paginationSize: 10,
                    paginationSizeSelector: [10, 20, 30, 40],
                    layout: "fitColumns",
                    responsiveLayout: "collapse",
                    placeholder: "No matching records found",
                    columns: [
                        {
                            formatter: "responsiveCollapse",
                            width: 40,
                            minWidth: 30,
                            hozAlign: "center",
                            resizable: false,
                            headerSort: false,
                        },

                        // For HTML table
                        {
                            title: "Shift Date",
                            minWidth: 170,
                            responsive: 0,
                            field: "shift_date",
                            vertAlign: "middle",
                            print: false,
                            download: false,
                            formatter(cell, formatterParams) {
                                return `<div>
                            <div class="font-medium whitespace-nowrap">${
                                    cell.getData().shift_date
                                }</div>
                            <div class="text-slate-500 text-xs whitespace-nowrap">${
                                    cell.getData().id
                                }</div>
                        </div>`;
                            },
                        },
                        {
                            title: "Start/End Time",
                            minWidth: 80,
                            field: "shift_times",
                            hozAlign: "center",
                            vertAlign: "middle",
                            print: false,
                            download: false,

                        },
                        {
                            title: "Duration",
                            minWidth: 30,
                            field: "shift_duration",
                            hozAlign: "center",
                            vertAlign: "middle",
                            print: false,
                            download: false,
                        },
                        {
                            title: "Manual Scheduling",
                            minWidth: 50,
                            field: "status",
                            hozAlign: "center",
                            vertAlign: "middle",
                            print: false,
                            download: false,
                            formatter(cell, formatterParams) {
                                return `<div class="flex items-center lg:justify-center">
<div class="form-check form-switch">
<input id="checkbox-switch-7" class="form-check-input" type="checkbox" ` + cell.getData().manual_scheduling_isset + `>
 </div>
                        </div>`;
                            },
                        },


                        // For print format
                        // {
                        //     title: "PRODUCT NAME",
                        //     field: "name",
                        //     visible: false,
                        //     print: true,
                        //     download: true,
                        // },
                        // {
                        //     title: "CATEGORY",
                        //     field: "category",
                        //     visible: false,
                        //     print: true,
                        //     download: true,
                        // },
                        // {
                        //     title: "REMAINING STOCK",
                        //     field: "remaining_stock",
                        //     visible: false,
                        //     print: true,
                        //     download: true,
                        // },
                        // {
                        //     title: "STATUS",
                        //     field: "status",
                        //     visible: false,
                        //     print: true,
                        //     download: true,
                        //     formatterPrint(cell) {
                        //         return cell.getValue() ? "Active" : "Inactive";
                        //     },
                        // },
                        // {
                        //     title: "IMAGE 1",
                        //     field: "images",
                        //     visible: false,
                        //     print: true,
                        //     download: true,
                        //     formatterPrint(cell) {
                        //         return cell.getValue()[0];
                        //     },
                        // },
                        // {
                        //     title: "IMAGE 2",
                        //     field: "images",
                        //     visible: false,
                        //     print: true,
                        //     download: true,
                        //     formatterPrint(cell) {
                        //         return cell.getValue()[1];
                        //     },
                        // },
                        // {
                        //     title: "IMAGE 3",
                        //     field: "images",
                        //     visible: false,
                        //     print: true,
                        //     download: true,
                        //     formatterPrint(cell) {
                        //         return cell.getValue()[2];
                        //     },
                        // },
                    ],
                    renderComplete() {
                        // createIcons({
                        //     icons,
                        //     "stroke-width": 1.5,
                        //     nameAttr: "data-lucide",
                        // });
                    },
                });

                // Redraw table onresize
                window.addEventListener("resize", () => {
                    table.redraw();
                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });
                });

                // Filter function
                function filterHTMLForm() {
                    let field = $("#tabulator-html-filter-field").val();
                    let type = $("#tabulator-html-filter-type").val();
                    let value = $("#tabulator-html-filter-value").val();
                    table.setFilter(field, type, value);
                }

                // On submit filter form
                // $("#tabulator-html-filter-form")[0].addEventListener(
                //     "keypress",
                //     function (event) {
                //         let keycode = event.keyCode ? event.keyCode : event.which;
                //         if (keycode == "13") {
                //             event.preventDefault();
                //             filterHTMLForm();
                //         }
                //     }
                // );

                // On click go button
                // $("#tabulator-html-filter-go").on("click", function (event) {
                //     filterHTMLForm();
                // });

                // On reset filter form
                // $("#tabulator-html-filter-reset").on("click", function (event) {
                //     $("#tabulator-html-filter-field").val("name");
                //     $("#tabulator-html-filter-type").val("like");
                //     $("#tabulator-html-filter-value").val("");
                //     filterHTMLForm();
                // });

                // Export
                $("#tabulator-export-csv").on("click", function (event) {
                    table.download("csv", "data.csv");
                });

                $("#tabulator-export-json").on("click", function (event) {
                    table.download("json", "data.json");
                });

                $("#tabulator-export-xlsx").on("click", function (event) {
                    window.XLSX = xlsx;
                    table.download("xlsx", "data.xlsx", {
                        sheetName: "Products",
                    });
                });

                $("#tabulator-export-html").on("click", function (event) {
                    table.download("html", "data.html", {
                        style: true,
                    });
                });

                // Print
                $("#tabulator-print").on("click", function (event) {
                    table.print();
                });
            }

        });


    </script>
</div>
