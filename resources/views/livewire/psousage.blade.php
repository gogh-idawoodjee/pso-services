<div>
    <div class="card card-default m-t-20">
        <div class="card-header  separator">
            <div class="card-title">Get PSO Usage Data</div>
        </div>
        <div class="card-body  m-t-20">
            <div class="col-md-12">
                <form role="form" wire:submit.prevent="getUsage">
                    <div class="row column-seperation">
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-check form-check-inline switch disabled">
                                        <input type="checkbox" id="send_to_pso" disabled>
                                        <label for="send_to_pso">Send to PSO</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group form-group-default required">
                                        <label>Username</label>
                                        <input type="text" class="form-control" name="username"
                                               wire:model="usage_data.username">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group form-group-default required">
                                        <label>Password</label>
                                        <input type="password" class="form-control" name="password"
                                               wire:model="usage_data.password">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group form-group-default required">
                                        <label>Account ID</label>
                                        <input type="text" class="form-control" name="account_id"
                                               wire:model="usage_data.account_id">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group form-group-default required">
                                        <label>Base URL</label>
                                        <input type="url" class="form-control" name="base_url"
                                               wire:model="usage_data.base_url">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group form-group-default required">
                                        <label>Dataset ID</label>
                                        <input type="text" class="form-control" required name="dataset_id"
                                               wire:model="usage_data.dataset_id">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div
                                        class="form-group form-group-default form-group-default-date input-group">
                                        <div class="form-input-group">
                                            <label class="">Min Date</label>
                                            <input type="date" class="form-control"
                                                   name="usage_data.mindate"
                                                   wire:model="usage_data.mindate">
                                        </div>
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <i class="pg-icon">calendar</i></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div
                                        class="form-group form-group-default form-group-default-date input-group">
                                        <div class="form-input-group">
                                            <label class="">Max Date</label>
                                            <input type="date" class="form-control"
                                                   name="datetime"
                                                   wire:model="usage_data.maxdate">
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
                            Get Usage

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
                    <div class="alert alert-info" role="alert">
                        <button aria-label="" class="close" data-dismiss="alert"></button>
                        <strong>Successful Get - </strong> See Data Below
                    </div>
                @endif

                @if($this->http_status==404)
                    <div class="alert alert-warning" role="alert">
                        <button aria-label="" class="close" data-dismiss="alert"></button>
                        <strong>Problem - </strong>
                        Dataset does not exist. If available, try one of the datasets listed below
                    </div>
                @endif
                <pre><code class="language-json">{{$usage_response}}</code></pre>
            </div>
        </div>
    @endif
</div>
