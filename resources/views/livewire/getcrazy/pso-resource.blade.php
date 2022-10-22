<div>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 lg:col-span-6">


            @if($resources)
                <table class="table table-report">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap"></th>
                            <th class="whitespace-nowrap">Resource</th>
                            <th class="whitespace-nowrap">Time Zone</th>
                            <th class="whitespace-nowrap">Allocations Including NAs</th>
                            <th class="text-center whitespace-nowrap">Shifts Generated</th>
                            <th class="text-center whitespace-nowrap"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($resources as $resource)
                            <tr class="intro-x">
                                <td class="w-40">
                                    <div class="flex">
                                        <div class="w-10 h-10 image-fit zoom-in">
                                            <img alt="Midone - HTML Admin Template" class="tooltip rounded-full"
                                                 src="/images/preview-4.jpg" title="Uploaded at 8 August 2022">
                                        </div>
                                        <div class="w-10 h-10 image-fit zoom-in -ml-5">
                                            <img alt="Midone - HTML Admin Template" class="tooltip rounded-full"
                                                 src="/images/preview-5.jpg" title="Uploaded at 8 August 2022">
                                        </div>
                                        <div class="w-10 h-10 image-fit zoom-in -ml-5">
                                            <img alt="Midone - HTML Admin Template" class="tooltip rounded-full"
                                                 src="/images/preview-10.jpg" title="Uploaded at 8 August 2022">
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href=""
                                       class="font-medium whitespace-nowrap">{{$resource['first_name'].' '.$resource['surname']}}</a>
                                    <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">
                                        ID: {{$resource['id']}}
                                    </div>
                                </td>
                                <td>
                                    {{$resource['time_zone']}}

                                </td>
                                <td class="text-center items-center">

                                    {{$resource['route']['total_allocations']}}

                                </td>

                                <td class="w-40">
                                    @if($resource['shiftcount'] > 0)
                                        <div
                                            class="flex items-center justify-center text-success"> {{$resource['shiftcount']}}

                                        </div>
                                        <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">
                                            through
                                            : {{Carbon\Carbon::parse($resource['shift_max'])->toFormattedDateString()}}
                                        </div>
                                    @endif
                                </td>
                                <td class="table-report__action w-56">
                                    <div class="flex justify-center items-center">
                                        <a class="flex items-center mr-3" href="/getcrazy/resource/{{$resource['id']}}">
                                            <i
                                                data-lucide="edit" class="w-4 h-4 mr-1"></i> Details </a>

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                no resources, the dataset may be expired or no dudes have shifts generated
            @endif

        </div>

    </div>

</div>
