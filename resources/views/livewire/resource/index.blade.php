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
            <div class="card-title">Get Resources</div>
        </div>
        <div class="card-body  m-t-20">
            <div class="col-md-12">
                <form role="form" wire:submit.prevent="getResources">
                    <div class="row column-seperation">
                        <div class="col-lg-12">


                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group form-group-default required">
                                        <label>Username</label>
                                        <input type="text" class="form-control" name="username"
                                               wire:model="usage_data.username">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group form-group-default required">
                                        <label>Password</label>
                                        <input type="password" class="form-control" name="password"
                                               wire:model="usage_data.password">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group form-group-default required">
                                        <label>Account ID</label>
                                        <input type="text" class="form-control" name="account_id"
                                               wire:model="usage_data.account_id">
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group form-group-default required">
                                        <label>Base URL</label>
                                        <input type="url" class="form-control" name="base_url"
                                               wire:model="usage_data.base_url">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group form-group-default required">
                                        <label>Dataset ID</label>
                                        <input type="text" class="form-control" required name="dataset_id"
                                               wire:model="usage_data.dataset_id">
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
                            Get Resources
                        </button>
                    </div>
                </form>
                <div class="row">
                    @error('usage_data.account_id')
                    <div class="alert alert-warning" role="alert">
                        <button aria-label="" class="close" data-dismiss="alert"></button>
                        <strong>Please check: </strong>{{$message}}
                    </div>
                    @enderror

                    @error('usage_data.token')
                    <div class="alert alert-warning" role="alert">
                        <button aria-label="" class="close" data-dismiss="alert"></button>
                        <strong>Please check: </strong>{{$message}}
                    </div>
                    @enderror


                    @error('usage_data.base_url')
                    <div class="alert alert-warning" role="alert">
                        <button aria-label="" class="close" data-dismiss="alert"></button>
                        <strong>Please check: </strong>{{$message}}
                    </div>
                    @enderror
                    @if($invalid_auth)
                        <div class="alert alert-warning" role="alert">
                            <button aria-label="" class="close" data-dismiss="alert"></button>
                            <strong>Please check: </strong>Invalid Credentials
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    @if($usage_response)
        <div class="card card-default m-t-20">
            <div class="card-body">
                @if($this->http_status==200)
                    <div class="alert alert-success" role="alert">
                        <button aria-label="" class="close" data-dismiss="alert"></button>
                        <strong>Successful Get - </strong> See Data Below
                    </div>
                @endif

                @if($this->http_status==404)
                    <div class="alert alert-warning" role="alert">
                        <button aria-label="" class="close" data-dismiss="alert"></button>
                        <strong>Problem - </strong>
                        See Below.
                        <pre><code class="language-json">{{$usage_response}}</code></pre>
                    </div>
                @endif

            </div>
        </div>
    @endif
    @if($resources)

    @endif
</div>
