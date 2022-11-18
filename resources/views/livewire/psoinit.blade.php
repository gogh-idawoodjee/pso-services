<div>
    <div class="card card-default m-t-20">
        <div class="card-body">
            <div class="col-md-12">
                <div class="card card-transparent">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs  nav-tabs-fillup" data-init-reponsive-tabs="dropdownfx">
                        <li class="nav-item">
                            <a href="#" class="active" data-toggle="tab" data-target="#slide1"><span>Initial Load</span></a>
                        </li>
                        <li class="nav-item">
                            <a href="#" data-toggle="tab" data-target="#slide2"><span>Rota to DSE</span></a>
                        </li>

                    </ul>
                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div class="tab-pane slide-left active m-t-10" id="slide1">

                            <form class="" role="form">
                                <div class="row column-seperation">
                                    <div class="col-lg-12">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-check form-check-inline switch">
                                                    <input type="checkbox" id="send_to_pso" wire:model="send_to_pso">
                                                    <label for="send_to_pso">Send to PSO</label>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group form-group-default required">
                                                    <label>Dataset ID</label>
                                                    <input type="text" class="form-control" required name="dataset_id">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group form-group-default required ">
                                                    <label>Rota ID</label>
                                                    <input type="text" class="form-control" name="rota_id"
                                                           placeholder="defaults to Dataset ID value">
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <div class="form-group form-group-default">
                                                    <label>DSE Window</label>
                                                    <input type="number" data-v-min="1" data-v-max="14" min="1" max="14"
                                                           value="7" name="dse_duration"
                                                           class="autonumeric form-control" required>
                                                    {{--                                                    <span class="help">e.g. "1 - 14"</span>--}}
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group form-group-default">
                                                    <label>Appointment Window </label>
                                                    <input type="number" data-v-max="200" name="appointment_window"
                                                           class="autonumeric form-control">
                                                    {{--                                                    <span class="help">e.g. "1 - 200"</span>--}}
                                                </div>
                                            </div>
                                        </div>
                                        @if($send_to_pso)
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group form-group-default required">
                                                        <label>Username</label>
                                                        <input type="text" class="form-control" name="username">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group form-group-default required">
                                                        <label>Password</label>
                                                        <input type="password" class="form-control" name="password">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group form-group-default required">
                                                        <label>Account ID</label>
                                                        <input type="text" class="form-control" name="account_id">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group form-group-default required">
                                                        <label>Base URL</label>
                                                        <input type="url" class="form-control" name="base_url">
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="row">
                                            <div class="col-md-2">
                                                <div
                                                    class="form-group form-group-default form-group-default-select2 required"
                                                    wire:ignore id="select2">
                                                    <label class="">Process Type</label>
                                                    <select class="full-width" data-placeholder="Select Country"
                                                            data-init-plugin="select2" name="process_type">
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
                                                               value="{{now()}}">
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
                                            class="btn btn-primary btn-cons">@if($send_to_pso)
                                            Initialize
                                        @else
                                            Generate Payload
                                        @endif
                                    </button>
                                </div>
                            </form>

                        </div>
                        <div class="tab-pane slide-left" id="slide2">
                            <div class="row">
                                <div class="col-lg-12">
                                    <h3>“ Nothing is
                                        <span class="semi-bold">impossible</span>, the word itself says 'I'm
                                        <span class="semi-bold">possible</span>'! ”
                                    </h3>
                                    <p>A style represents visual customizations on top of a layout. By editing a style,
                                        you
                                        can use Squarespace's visual interface to customize your...</p>
                                    <br>
                                    <p class="pull-right">
                                        <button aria-label="" type="button" class="btn btn-default btn-cons">White
                                        </button>
                                        <button aria-label="" type="button" class="btn btn-success btn-cons">Success
                                        </button>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
</div>
