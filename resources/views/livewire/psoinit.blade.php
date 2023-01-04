<div>
    <div class="card card-default m-t-20">
        <div class="card-header  separator">
            <div class="card-title">Select an existing environment
            </div>
        </div>
        <div class="card-body">
            <div
                class="form-group m-t-10" wire:ignore>

                <select wire:model="environment"
                        class="cs-select cs-skin-slide" data-init-plugin="cs-select" id="envs">
                    <option value="" disabled selected>Select Environment</option>
                    @foreach($environments as $env)
                        <option value="{{$env->id}}">{{$env->name}} - {{$env->account_id}}
                            - {{$env->base_url}}</option>
                    @endforeach
                </select>

            </div>
            <div class="form-group m-t-10">
                @if($datasets)
                    <select wire:model="dataset"
                            class="cs-select cs-skin-slide" data-init-plugin="cs-select" id="datasets">
                        <option value="no_dataset" selected>Select Dataset</option>
                        @foreach($datasets as $ds)
                            <option value="{{$ds->id}}">{{$ds->dataset_id}} - {{$ds->rota_id}}</option>
                            {{--                            <option value="" selected>{{$ds->dataset_id}}</option>--}}
                        @endforeach
                    </select>
                @endif
            </div>

        </div>
    </div>
    <div class="card card-default m-t-20">
        <div class="card-header  separator">
            <div class="card-title">Initial Load Parameters
            </div>
        </div>
        <div class="card-body  m-t-20">
            <div class="col-md-12">
                <form role="form" wire:submit.prevent="initPSO">
                    <div class="row column-seperation">
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-check form-check-inline switch">
                                        <input type="checkbox" id="send_to_pso"
                                               wire:model="init_data.send_to_pso">
                                        <label for="send_to_pso">Send to PSO</label>
                                    </div>
                                </div>
                                @if($init_data['send_to_pso'])
                                    <div class="col-lg-4">
                                        <div class="form-check form-check-inline switch">
                                            <input type="checkbox" id="keep_pso_data"
                                                   wire:model="init_data.keep_pso_data">
                                            <label for="keep_pso_data">Keep PSO Data That Has Not Expired</label>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            @if($init_data['send_to_pso'])
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group form-group-default required">
                                            <label>Username</label>
                                            <input type="text" class="form-control" name="username"
                                                   wire:model="init_data.username">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group form-group-default required">
                                            <label>Password</label>
                                            <input type="password" class="form-control" name="password"
                                                   wire:model="init_data.password">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group form-group-default required">
                                            <label>Account ID</label>
                                            <input type="text" class="form-control" name="account_id"
                                                   wire:model="init_data.account_id">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group form-group-default required">
                                            <label>Base URL</label>
                                            <input type="url" class="form-control" name="base_url"
                                                   wire:model="init_data.base_url">
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group form-group-default required">
                                        <label>Dataset ID</label>
                                        <input type="text" class="form-control" required name="dataset_id"
                                               wire:model="init_data.dataset_id">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group form-group-default">
                                        <label>Rota ID</label>
                                        <input type="text" class="form-control" name="rota_id"
                                               wire:model="init_data.rota_id"
                                               placeholder="defaults to Dataset ID value">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group form-group-default">
                                        <label>DSE Window</label>
                                        <input type="number" data-v-min="1" data-v-max="14" min="1" max="14"
                                               value="7" name="dse_duration"
                                               wire:model="init_data.dse_duration"
                                               class="autonumeric form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group form-group-default">
                                        <label>Appointment Window </label>
                                        <input type="number" data-v-max="200" name="appointment_window"
                                               wire:model="init_data.appointment_window"
                                               class="autonumeric form-control">
                                        {{--                                                    <span class="help">e.g. "1 - 200"</span>--}}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2">
                                    <div
                                        class="form-group form-group-default form-group-default-select2 required"
                                        wire:ignore id="select2">
                                        <label class="">Process Type</label>
                                        <select class="full-width" data-placeholder="Select Process type"
                                                data-init-plugin="select2" name="process_type"
                                                wire:model="init_data.process_type">
                                            <option value="APPOINTMENT" selected>Appointment</option>
                                            <option value="DYNAMIC">Dynamic</option>
                                            <option value="REACTIVE">Reactive</option>
                                            <option value="STATIC">Static</option>
                                        </select>
                                    </div>

                                </div>
                                <div class="col-md-4 align-middle">

                                    <div
                                        class="form-group form-group-default form-group-default-date input-group">
                                        <div class="form-input-group">
                                            <label class="">Input Date</label>
                                            <input type="datetime-local" class="form-control"
                                                   name="datetime"
                                                   wire:model="init_data.datetime"
                                            >
                                        </div>
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <i class="pg-icon">calendar</i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group form-group-default">
                                        <label>Description</label>
                                        <input type="text" class="form-control" name="description"
                                               wire:model="init_data.description"
                                               value="Initializing PSO from the Thingy">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="pull-right">
                        <button aria-label="" type="button" class="btn btn-default btn-cons">Clear
                        </button>
                        <button aria-label="" type="submit"
                                class="btn btn-primary btn-cons">
                            @if($init_data['send_to_pso'])
                                Initialize
                            @else
                                Generate Payload
                            @endif
                        </button>
                    </div>
                </form>
                <div class="row">
                    @error('init_data.account_id')
                    <div class="alert alert-warning" role="alert">
                        <button aria-label="" class="close" data-dismiss="alert"></button>
                        <strong>Please check: </strong>{{$message}}
                    </div>
                    @enderror

                    @error('init_data.token')
                    <div class="alert alert-warning" role="alert">
                        <button aria-label="" class="close" data-dismiss="alert"></button>
                        <strong>Please check: </strong>{{$message}}
                    </div>
                    @enderror


                </div>
            </div>
        </div>
    </div>

    @if($http_status)
        <div class="card card-default m-t-20">
            <div class="card-body">

                @if($http_status==200 ||$http_status==202)
                    <div class="alert alert-success" role="alert">
                        <button aria-label="" class="close" data-dismiss="alert"></button>
                        <strong>{{$description}}</strong>
                    </div>
                    <pre><code class="language-json">{{$original_payload}}</code></pre>
                @endif

                @if($http_status==404)
                    <div class="alert alert-warning" role="alert">
                        <button aria-label="" class="close" data-dismiss="alert"></button>
                        <strong>Problem - </strong>
                        Dataset does not exist. If available, try one of the datasets listed below
                    </div>
                @endif

                @if($http_status==401)
                    <div class="alert alert-warning" role="alert">
                        <button aria-label="" class="close" data-dismiss="alert"></button>
                        <strong>Problem - </strong>
                        Could not authenticate. Check user, pass or account ID
                    </div>
                @endif

            </div>
        </div>
    @endif

</div>
