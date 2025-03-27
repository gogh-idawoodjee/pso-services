<div>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 lg:col-span-6">
            <div class="intro-y box p-5">

                <select wire:model="selectedEnvironment" class="form-select"
                        wire:change="updateDatasets">
                    <option value="" selected>Choose Environment</option>
                    @foreach($environments as $environment)
                        <option value="{{ $environment->id }}">{{ $environment->name }}</option>
                    @endforeach
                </select>

                @if (!is_null($selectedEnvironment) && !is_null($datasets) && $selectedEnvironment != "")
                    <div class="form-group row">
                        <label for="city" class="col-md-4 col-form-label text-md-right">Dataset</label>

                        <div class="col-md-6">
                            <select class="form-select" wire:model="selectedDataset">
                                <option value="" selected>Choose Dataset ID</option>
                                @foreach($datasets as $dataset)
                                    <option value="{{ $dataset->dataset_id }}">{{ $dataset->dataset_id }} </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif
                <div class="text-right mt-5">
                    <button wire:click="getSchedule"
                            type="button" class="btn btn-primary"
                            @if(is_null($selectedDataset))disabled @endif>
                        Get Schedule Output
                    </button>
                </div>


                <pre><code class="language-json">{{json_encode($scheduleOutput,JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)}}</code></pre>

            </div>

        </div>
    </div>
    <script>

        document.addEventListener('livewire:load', function () {

            import helper from "./helper";
            import hljs from "highlight.js";
            import jsBeautify from "js-beautify";

            (function () {
                "use strict";

                // Highlight code
                $(".source-preview").each(function () {
                    let source = $(this).find("code").html();

                    // First replace
                    let replace = helper.replaceAll(source, "HTMLOpenTag", "<");
                    replace = helper.replaceAll(replace, "HTMLCloseTag", ">");

                    // Save for copy code function
                    let originalSource = $(
                        '<textarea class="absolute w-0 h-0 p-0"></textarea>'
                    ).val(replace);
                    $(this).append(originalSource);

                    // Beautify code
                    if ($(this).find("code").hasClass("javascript")) {
                        replace = jsBeautify(replace);
                    } else {
                        replace = jsBeautify.html(replace);
                    }

                    // Format for highlight.js
                    replace = helper.replaceAll(replace, "<", "&lt;");
                    replace = helper.replaceAll(replace, ">", "&gt;");
                    $(this).find("code").html(replace);
                });

                hljs.highlightAll();
            })();



        })

    </script>
</div>
