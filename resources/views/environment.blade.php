@extends('layouts.main')
@section('content')

    <table class="table table-report">
        <thead>
            <tr>
                <th class="whitespace-nowrap"></th>
                <th class="whitespace-nowrap">Name</th>
                <th class="whitespace-nowrap">Base URL</th>
                <th class="whitespace-nowrap">User</th>
                <th class="text-center whitespace-nowrap">STATUS</th>
                <th class="text-center whitespace-nowrap">ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($environments as $environment)
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
                        <a href="" class="font-medium whitespace-nowrap">{{$environment->name}}</a>
                        <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">Account
                            ID: {{$environment->account_id}}
                        </div>
                    </td>
                    <td>
                        <a class="text-slate-500 flex items-center mr-3" href="javascript:;"> <i
                                data-lucide="external-link" class="w-4 h-4 mr-2"></i> {{$environment->base_url}}
                        </a>
                    </td>
                    <td>
                        <a class="text-slate-500 flex items-center mr-3" href="javascript:;"> <i
                                data-lucide="external-link" class="w-4 h-4 mr-2"></i> {{$environment->username}}
                        </a>
                    </td>

                    <td class="w-40">
                        <div class="flex items-center justify-center text-success"><i
                                data-lucide="check-square" class="w-4 h-4 mr-2"></i> Active
                        </div>
                    </td>
                    <td class="table-report__action w-56">
                        <div class="flex justify-center items-center">
                            <a class="flex items-center mr-3" href="javascript:;"> <i
                                    data-lucide="check-square" class="w-4 h-4 mr-1"></i> Edit </a>
                            <a class="flex items-center text-danger" href="javascript:;"
                               data-tw-toggle="modal" data-tw-target="#delete-confirmation-modal"> <i
                                    data-lucide="trash-2" class="w-4 h-4 mr-1"></i> Delete </a>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

@endsection
