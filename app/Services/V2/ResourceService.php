<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\Classes\V2\EntityBuilders\ActivityBuilder;
use App\Classes\V2\EntityBuilders\ActivityStatusBuilder;
use App\Classes\V2\EntityBuilders\ResourceEventBuilder;
use App\Classes\V2\EntityBuilders\ShiftBuilder;
use App\Enums\ActivityClass;
use App\Enums\ActivityStatus;
use App\Enums\PsoEndpointSegment;
use App\Enums\ShiftEntity;
use App\Enums\UnavailabilityEntity;
use App\Helpers\Stubs\RamTimePattern;
use App\Helpers\Stubs\RamUnavailability;
use App\Models\PsoEnvironment;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use SensitiveParameter;

class ResourceService extends BaseService
{

    public function __construct(#[SensitiveParameter] string|null $sessionToken = null, $data)
    {
        parent::__construct($sessionToken, $data);
    }

    public function createEvent(): JsonResponse|null
    {
        try {


            $payload =
                ResourceEventBuilder::make(data_get($this->data, 'resourceId'), data_get($this->data, 'eventType'))
                    ->eventDateTime(data_get($this->data, 'eventDateTime'))
                    ->latitude(data_get($this->data, 'lat'))
                    ->longitude(data_get($this->data, 'long'))
                    ->build();

            return $this->sendOrSimulate(
                ['Schedule_Event' => $payload],
                data_get($this->data, 'environment'),
                $this->sessionToken
            );
        } catch (Exception $e) {
            $this->LogError($e, __METHOD__, __CLASS__);
            return $this->error('An unexpected error occurred', 500);
        }
    }

    public function updateShift(): JsonResponse|null
    {
        try {

            $payload = ShiftBuilder::make()
                ->shiftId(data_get($this->data, 'data.shiftId'))
                ->shiftType(data_get($this->data, 'data.shiftType'))
                ->startDateTime(data_get($this->data, 'data.startDateTime'))
                ->endDateTime(data_get($this->data, 'data.endDateTime'))
                ->arpObject(data_get($this->data, 'data.isArpObject'))
                ->description(data_get($this->data, 'data.description'))
                ->manualSchedulingOnly(data_get($this->data, 'data.isManualSchedulingOnly'))
                ->rotaId(data_get($this->data, 'data.rotaId'))
                ->resourceId(data_get($this->data, 'data.resourceId'))
                ->build();

            $entity = data_get($this->data, 'data.isArpObject') ? ShiftEntity::RAMROTAITEM->value : ShiftEntity::SHIFT->value;

            return $this->sendOrSimulate(
                [$entity => $payload],
                data_get($this->data, 'environment'),
                $this->sessionToken,
                true, // sends rota update
                'Updated Rota After Shift Update'
            );


        } catch (Exception $e) {
            $this->LogError($e, __METHOD__, __CLASS__);
            return $this->error('An unexpected error occurred', 500);
        }

    }

    public function createUnavailability(): JsonResponse|null
    {
        try {

            // starting with just schedule unavail which is just a private activity

            if (data_get($this->data, 'data.isArpObject')) {
                // to ARP first

                $timePatternId = Str::uuid()->getHex();

                $timepattern = RamTimePattern::make(
                    data_get($this->data, 'data.resourceId'),
                    $timePatternId,
                    data_get($this->data, 'data.categoryId'),
                );
                $unavailability = RamUnavailability::make(
                    $timePatternId,
                    data_get($this->data, 'data.baseDateTime'),
                    data_get($this->data, 'data.duration'),
                );

                return $this->sendOrSimulateBuilder()
                    ->payload(['Ram_Time_Pattern' => $timepattern, 'RAM_Unavailability' => $unavailability])
                    ->environment(data_get($this->data, 'environment'))
                    ->token($this->sessionToken)
                    ->includeInputReference('send unavailability to ARP')
                    ->requiresRotaUpdate(true)
                    ->send();

            } else {
                // straight to DSE
                $activityId = Uuid::uuid4()->toString();
                data_set($this->data, 'data.activityId', $activityId);


                $payload = ActivityBuilder::make($this->data)
                    ->withActivityClass(ActivityClass::PRIVATE)
                    ->withActivityStatusBuilder(
                        ActivityStatusBuilder::make($activityId, ActivityStatus::COMMITTED)
                            ->resourceId(data_get($this->data, 'data.resourceId'))
                            ->fixed(true)
                            ->dateTimeFixed(data_get($this->data, 'data.baseDateTime'))
                            ->duration(data_get($this->data, 'data.duration'))
                    )
                    ->build();

                return $this->sendOrSimulateBuilder()
                    ->payload($payload)
                    ->environment(data_get($this->data, 'environment'))
                    ->token($this->sessionToken)
                    ->includeInputReference('Created Unavailability')
                    ->send();
            }


        } catch (Exception $e) {
            $this->LogError($e, __METHOD__, __CLASS__);
            return $this->error('An unexpected error occurred', 500);
        }
    }

    public function getResource(array $data): JsonResponse
    {
        $datasetId = data_get($data, 'environment.datasetId');
        $resourceId = data_get($data, 'data.resourceId');
        return $this->ok($this->getResourceFromPSO($datasetId, $resourceId, data_get($data, 'environment'),$this->sessionToken,PsoEndpointSegment::DATA));;
    }
}
