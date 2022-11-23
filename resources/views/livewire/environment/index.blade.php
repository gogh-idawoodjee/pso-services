<div>
    <div class="card card-default m-t-20">
        <div class="card-header  separator">
            <div class="card-title">Add Environment
            </div>
        </div>
        <div class="card-body  m-t-20">
            <div class="col-md-12">
                <form role="form" wire:submit.prevent="addEnvironment">
                    <div class="row column-seperation">
                        <div class="col-lg-12">

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group form-group-default required">
                                        <label>Environment Name</label>
                                        <input type="text" class="form-control" name="name" required
                                               wire:model="env_data.name">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group form-group-default required">
                                        <label>Username</label>
                                        <input type="text" class="form-control" name="username" required
                                               wire:model="env_data.username">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group form-group-default required">
                                        <label>Password</label>
                                        <input type="password" class="form-control" name="password"
                                               wire:model="env_data.password">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group form-group-default required">
                                        <label>Base URL</label>
                                        <input type="url" class="form-control" name="base_url"
                                               wire:model="env_data.base_url">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group form-group-default required">
                                        <label>Default Dataset ID</label>
                                        <input type="text" class="form-control" required name="dataset_id"
                                               wire:model="env_data.dataset_id">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group form-group-default">
                                        <label>Rota ID</label>
                                        <input type="text" class="form-control" name="rota_id"
                                               wire:model="env_data.rota_id"
                                               placeholder="defaults to Dataset ID value">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group form-group-default required">
                                        <label>Account ID</label>
                                        <input type="text" class="form-control" name="account_id" required
                                               wire:model="env_data.account_id">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="pull-right">
                        <button aria-label="" type="button" class="btn btn-default btn-cons">Clear
                        </button>
                        <button aria-label="" type="submit" class="btn btn-primary btn-cons">
                            Add Environment
                        </button>
                    </div>
                </form>
                <div class="row">
                    @error('env_data.account_id')
                    <div class="alert alert-warning" role="alert">
                        <button aria-label="" class="close" data-dismiss="alert"></button>
                        {{--                        <strong>Please check: </strong>{{$message}}--}}
                    </div>
                    @enderror


                </div>
            </div>
        </div>

    </div>
    @if(count($environments))
        <div class="card card-default m-t-30">
            <div class="card-header ">
                <div class="card-title">Environments
                </div>
                <div class="pull-right">
                    <div class="col-xs-12">
                        <input type="text" id="search-table" class="form-control pull-right" placeholder="Search">
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="card-body">
                <table class="table table-hover demo-table-search table-responsive-block" id="tableWithSearch">
                    <thead>
                        <tr>
                            <th>Name / Account</th>
                            <th>Datasets</th>
                            <th>base_url</th>
                            <th>username</th>
                            <th>Last Update</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($environments as $env)
                            <tr>
                                <td class="v-align-middle semi-bold">
                                    <p>{{$env->name}}</p>
                                    <small>{{$env->account_id}}</small>
                                </td>
                                <td class="v-align-middle">


                                    @if($env->defaultdataset()->count())

                                        <a href="#" class="btn btn-tag">{{$env->defaultdataset->dataset_id}}</a>
                                    @endif
                                    @if($env->datasets()->count() > 1)
                                        and {{$env->datasets()->count()}} more
                                    @endif

                                    {{--                                    <a href="#" class="btn btn-tag">China</a><a href="#" class="btn btn-tag">Africa</a>--}}
                                </td>
                                <td class="v-align-middle">
                                    <p>{{$env->base_url}}</p>
                                </td>
                                <td class="v-align-middle">
                                    <p>{{$env->username}}</p>
                                </td>
                                <td class="v-align-middle">
                                    <p>{{$env->updated_at}}</p>
                                </td>
                                <td class="v-align-middle">
                                    <a href="/environment/{{$env->id}}"><i class="pg-icon">pencil</i></a>
                                </td>
                                <td class="v-align-middle">
                                    <a href="" wire:click.prevent="deleteEnvironment('{{ $env->id }}')"><i
                                            class="pg-icon">bin</i></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

