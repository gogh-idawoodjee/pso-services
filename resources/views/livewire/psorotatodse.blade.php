<div>
    <div class="card card-default m-t-20">
        <div class="card-header  separator">
            <div class="card-title">Rota To DSE Parameters
            </div>
        </div>
        <div class="card-body  m-t-20">
            <div class="col-md-12">
                <form role="form" wire:submit.prevent="rotaToDSE">
                    <div class="row column-seperation">
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-check form-check-inline switch">
                                        <input type="checkbox" id="send_to_pso"
                                               wire:model="rota_data.send_to_pso">
                                        <label for="send_to_pso">Send to PSO</label>
                                    </div>
                                </div>
                            </div>
                            @if($rota_data['send_to_pso'])
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group form-group-default required">
                                            <label>Username</label>
                                            <input type="text" class="form-control" name="username"
                                                   wire:model="rota_data.username">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group form-group-default required">
                                            <label>Password</label>
                                            <input type="password" class="form-control" name="password"
                                                   wire:model="rota_data.password">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group form-group-default required">
                                            <label>Account ID</label>
                                            <input type="text" class="form-control" name="account_id"
                                                   wire:model="rota_data.account_id">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group form-group-default required">
                                            <label>Base URL</label>
                                            <input type="url" class="form-control" name="base_url"
                                                   wire:model="rota_data.base_url">
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group form-group-default required">
                                        <label>Dataset ID</label>
                                        <input type="text" class="form-control" required name="dataset_id"
                                               wire:model="rota_data.dataset_id">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group form-group-default">
                                        <label>Rota ID</label>
                                        <input type="text" class="form-control" name="rota_id"
                                               wire:model="rota_data.rota_id"
                                               placeholder="defaults to Dataset ID value">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div
                                        class="form-group form-group-default form-group-default-date input-group">
                                        <div class="form-input-group">
                                            <label class="">Input Date</label>
                                            <input type="datetime-local" class="form-control"
                                                   name="datetime"
                                                   wire:model="rota_data.datetime">
                                        </div>
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <i class="pg-icon">calendar</i></span>
                                        </div>
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
                            @if($rota_data['send_to_pso'])
                                Send Rota to DSE
                            @else
                                Generate Payload
                            @endif
                        </button>
                    </div>
                </form>
                <div class="row">
                    @error('rota_data.account_id')
                    <div class="alert alert-warning" role="alert">
                        <button aria-label="" class="close" data-dismiss="alert"></button>
                        <strong>Please check: </strong>{{$message}}
                    </div>
                    @enderror

                    @error('rota_data.token')
                    <div class="alert alert-warning" role="alert">
                        <button aria-label="" class="close" data-dismiss="alert"></button>
                        <strong>Please check: </strong>{{$message}}
                    </div>
                    @enderror


                    @error('rota_data.base_url')
                    <div class="alert alert-warning" role="alert">
                        <button aria-label="" class="close" data-dismiss="alert"></button>
                        <strong>Please check: </strong>{{$message}}
                    </div>
                    @enderror

                    @if($http_status==401)
                        <div class="alert alert-warning" role="alert">
                            <button aria-label="" class="close" data-dismiss="alert"></button>
                            <strong>Problem - </strong>
                            Could not authenticate. Check user, pass or account ID
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
