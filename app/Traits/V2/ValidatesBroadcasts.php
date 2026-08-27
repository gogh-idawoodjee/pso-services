<?php

namespace App\Traits\V2;

use App\Enums\BroadcastAllocationType;
use App\Enums\BroadcastPlanType;
use App\Enums\BroadcastType;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

/**
 * Shared data.broadcasts[] validation, used by both LoadPsoRequest and
 * UpdateRotaRequest to attach Broadcast + Broadcast_Parameter entities to
 * the PSO payload.
 *
 * Beyond the base rules(), each broadcast_type_id has a default set of
 * mandatory parameter names per the IFS Broadcast_Parameter schema (e.g.
 * REST requires mediatype + url). That can't be expressed with required_if
 * because it depends on matching against a list of submitted parameter
 * names rather than a single sibling field, so it's enforced separately
 * via requireBroadcastParameters() in an after() hook.
 */
trait ValidatesBroadcasts
{
    protected function broadcastRules(): array
    {
        return [
            /**
             * Broadcasts to attach to this PSO load, so plans/changes are
             * communicated to external applications (email, file, REST, etc).
             *
             * @var array|null
             */
            'data.broadcasts' => ['nullable', 'array'],

            /**
             * Unique id for this broadcast. Auto-generated if omitted.
             * Supplying the same id across repeated loads lets PSO update an
             * existing broadcast instead of creating a new one each time.
             *
             * @var string|null
             *
             * @example "my-rest-broadcast"
             */
            'data.broadcasts.*.id' => ['nullable', 'string', 'max:32'],

            /**
             * Whether the broadcast is active. Defaults to true.
             *
             * @var bool|null
             *
             * @example true
             */
            'data.broadcasts.*.active' => ['nullable', 'boolean'],

            /**
             * The broadcast type, which determines what Broadcast_Parameters are required.
             *
             * @var string
             *
             * @example "REST"
             */
            'data.broadcasts.*.broadcastTypeId' => ['required_with:data.broadcasts', new Enum(BroadcastType::class)],

            /**
             * The type of plan to be generated for this broadcast.
             *
             * @var string
             *
             * @example "COMPLETE"
             */
            'data.broadcasts.*.planType' => ['required_with:data.broadcasts', new Enum(BroadcastPlanType::class)],

            /**
             * Raw allocation type bitmask component values to combine
             * (1=Dynamic Scheduling, 2=Appointment Booking Engine, 4=Manual
             * Scheduling, 8=Schedule Dispatch Service, 16=Scheduling Travel
             * Analyser). E.g. [1, 4] combines Dynamic Scheduling and Manual
             * Scheduling.
             *
             * @var int[]|null
             *
             * @example [1, 4]
             */
            'data.broadcasts.*.allocationType' => ['nullable', 'array'],
            'data.broadcasts.*.allocationType.*' => [new Enum(BroadcastAllocationType::class)],

            /**
             * Broadcast description.
             *
             * @var string|null
             *
             * @example "Notify third-party system of schedule changes"
             */
            'data.broadcasts.*.description' => ['nullable', 'string', 'max:2000'],

            /**
             * If true, the plan is only broadcast once, the first time it's required.
             *
             * @var bool|null
             *
             * @example false
             */
            'data.broadcasts.*.onceOnly' => ['nullable', 'boolean'],

            /**
             * Only broadcast when the plan quality is greater or equal to this value (0-100).
             *
             * @var float|null
             *
             * @example 80
             */
            'data.broadcasts.*.minimumPlanQuality' => ['nullable', 'numeric', 'between:0,100'],

            /**
             * A broadcast will only be sent every 'x' plans.
             *
             * @var int|null
             *
             * @example 1
             */
            'data.broadcasts.*.minimumStepInterval' => ['nullable', 'integer'],

            /**
             * Date and time the broadcast should expire.
             *
             * @var string|null
             *
             * @example "2026-12-31T23:59:59Z"
             */
            'data.broadcasts.*.expiryDatetime' => ['nullable', 'date'],

            /**
             * Input reference id that must be processed before this broadcast is issued.
             *
             * @var string|null
             *
             * @example "load-123"
             */
            'data.broadcasts.*.inputReferenceId' => ['nullable', 'string', 'max:100'],

            /**
             * Minimum time, in minutes, since the previous broadcast before sending an updated one.
             * Converted to an ISO 8601 duration internally.
             *
             * @var int|null
             *
             * @example 5
             */
            'data.broadcasts.*.maximumFrequency' => ['nullable', 'integer', 'min:0'],

            /**
             * Maximum time, in minutes, to wait before broadcasting, even if plan quality isn't met.
             * Converted to an ISO 8601 duration internally.
             *
             * @var int|null
             *
             * @example 30
             */
            'data.broadcasts.*.maximumWait' => ['nullable', 'integer', 'min:0'],

            /**
             * Allocation rows with a visit_status below this value are excluded from the broadcast.
             *
             * @var int|null
             *
             * @example 2
             */
            'data.broadcasts.*.minimumVisitStatus' => ['nullable', 'integer'],

            /**
             * Activities ending at or before this time are excluded from the broadcast.
             *
             * @var string|null
             *
             * @example "2026-01-01T00:00:00Z"
             */
            'data.broadcasts.*.timeFilterStart' => ['nullable', 'date'],

            /**
             * Activities starting at or after this time are excluded from the broadcast.
             *
             * @var string|null
             *
             * @example "2026-01-02T00:00:00Z"
             */
            'data.broadcasts.*.timeFilterEnd' => ['nullable', 'date'],

            /**
             * Broadcast_Parameter rows for this broadcast. Required parameter
             * names vary by broadcastTypeId (see IFS Broadcast_Parameter docs).
             *
             * @var array
             */
            'data.broadcasts.*.parameters' => ['required_with:data.broadcasts', 'array', 'min:1'],

            /**
             * Parameter name (e.g. "mediatype", "to_address", "file_path").
             *
             * @var string
             *
             * @example "mediatype"
             */
            'data.broadcasts.*.parameters.*.name' => ['required', 'string', 'max:100'],

            /**
             * Parameter value.
             *
             * @var string
             *
             * @example "application/json"
             */
            'data.broadcasts.*.parameters.*.value' => ['required', 'string', 'max:1000'],
        ];
    }

    protected function requireBroadcastParameters(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $broadcasts = (array) $this->input('data.broadcasts', []);

            foreach ($broadcasts as $index => $broadcast) {
                $type = BroadcastType::tryFrom($broadcast['broadcastTypeId'] ?? '');

                if (! $type) {
                    continue;
                }

                $submittedNames = array_column($broadcast['parameters'] ?? [], 'name');

                $requiredNames = array_map(
                    static fn ($param) => $param->value,
                    $type->requiredParameters(),
                );

                if (($broadcast['planType'] ?? null) === 'ADMIN') {
                    $requiredNames = array_merge($requiredNames, ['application_type_id', 'check_in_expired_time']);
                }

                $missing = array_diff($requiredNames, $submittedNames);

                if (! empty($missing)) {
                    $validator->errors()->add(
                        "data.broadcasts.{$index}.parameters",
                        "The following parameters are required for broadcastTypeId \"{$broadcast['broadcastTypeId']}\"".
                        (($broadcast['planType'] ?? null) === 'ADMIN' ? ' with planType "ADMIN"' : '').
                        ': '.implode(', ', $missing).'.',
                    );
                }
            }
        });
    }
}
