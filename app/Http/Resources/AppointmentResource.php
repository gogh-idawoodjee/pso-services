<?php

namespace App\Http\Resources;

use App\Enums\AppointmentRequestStatus;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateInterval;
use DateMalformedIntervalStringException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

class AppointmentResource extends JsonResource
{
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'meta' => $this->getMetaData(),
            'summary' => [
                'environment' => $this->getEnvironmentData(),
                'appointmentRequest' => $this->getAppointmentRequestData(),
                'offers' => $this->getOfferData(),
            ]
        ];
    }

    private function getMetaData(): array
    {
        return [
            'logId' => $this->id,
            'status' => AppointmentRequestStatus::from($this->status)->label(),
            'createdAt' => $this->created_at->toDateTimeString(),
            'updatedAt' => $this->updated_at->toDateTimeString(),
            'updateDelay' => $this->formatDelayFromCreatedAt($this->updated_at, 'after creation'),
        ];
    }

    private function getEnvironmentData(): array
    {
        return [
            'baseUrl' => $this->base_url,
            'datasetId' => $this->dataset_id,
        ];
    }

    private function getAppointmentRequestData(): array
    {
        return [
            'id' => $this->appointment_request_id,
            'activityId' => $this->activity_id,
            'appointmentTemplateId' => $this->appointment_template_id,
            'appointmentTemplateDuration' => $this->formatInterval($this->appointment_template_duration),
            'appointmentTemplateDateTime' => $this->appointment_template_datetime->toDateTimeString(),
            'slotUsageRule' => $this->slot_usage_rule_id,
        ];
    }

    private function getOfferData(): array
    {
        $expiryKey = $this->offer_expiry_datetime->isFuture() ? 'expiresAt' : 'expiredAt';
        $expiryValue = $this->formatDateTimeWithHumanDiff($this->offer_expiry_datetime);
        $statusEnum = AppointmentRequestStatus::from($this->status);
        $acceptedOrDeclined = $statusEnum->isAcceptedOrDeclined();

        $acceptDeclineKeyPrefix = match (true) {
            $statusEnum === AppointmentRequestStatus::ACCEPTED => 'accepted',
            $statusEnum === AppointmentRequestStatus::DECLINED => 'declined',
            default => '',
        };

        $baseOffersArray = [
            'offersReturned' => $this->total_offers_returned,
            'validOffers' => $this->total_valid_offers_returned,
            'invalidOffers' => $this->total_invalid_offers_returned,
            'percentValid' => $this->total_offers_returned > 0
                ? number_format(($this->total_valid_offers_returned / $this->total_offers_returned) * 100, 2) . '%'
                : 'N/A',
            'appointedCheck' => $this->getAppointedCheck(),
            $expiryKey => $expiryValue,
        ];

        $responseArray = [
            'status' => $statusEnum->label(),
            'inputReferenceId' => $this->accept_decline_input_reference_id,
        ];


        if ($statusEnum === AppointmentRequestStatus::ACCEPTED) {
            $responseArray['acceptedOfferId'] = $this->accepted_offer_id;

            $responseArray[$acceptDeclineKeyPrefix . 'DateTime'] = $this->formatDateTimeWithHumanDiff($this->accept_decline_datetime);

            // Add acceptedDelay — how long after createdAt
            $responseArray['acceptedDelay'] = $this->formatDelayFromCreatedAt($this->accept_decline_datetime, 'after appointment check');
        } else {
            $responseArray[$acceptDeclineKeyPrefix . 'DateTime'] = $this->formatDateTimeWithHumanDiff($this->accept_decline_datetime);
            $responseArray['declinedDelay'] = $this->formatDelayFromCreatedAt($this->accept_decline_datetime, 'after appointment check');
        }

        if ($acceptedOrDeclined) {
            return array_merge($baseOffersArray, ['response' => $responseArray]);
        }
        return $baseOffersArray;
    }

    private function getAppointedCheck(): array
    {
        $checkResult = $this->appointed_check_result ? "available" : "unavailable"; // todo parameterize or enum this


        return [
            'status' => $this->appointed_check_complete ? 'COMPLETED' : 'NOT COMPLETED',
            'offerId' => $this->appointed_check_complete ? $this->appointed_check_offer_id : 'N/A',
            'checkResult' => $this->appointed_check_complete ? $checkResult : 'N/A',
            'inputReferenceId' => $this->appointed_check_complete ? $this->appointed_check_input_reference_id : 'N/A',
            'checkDateTime' => $this->appointed_check_complete
                ? $this->formatDateTimeWithHumanDiff($this->appointed_check_datetime)
                : 'N/A',
            'checkDelay' => $this->formatDelayFromCreatedAt($this->appointed_check_datetime, 'after appointment check'),
        ];
    }


    private function formatDateTimeWithHumanDiff(Carbon|null $dateTime): string
    {
        if ($dateTime) {
            return $dateTime->toDateTimeString() . ' - ' . $dateTime->diffForHumans();
        }
        return 'N/A';
    }

    private function formatInterval(string $isoInterval): string
    {
        try {
            return (new DateInterval($isoInterval))->format('%d days');
        } catch (DateMalformedIntervalStringException $e) {
            return 'Invalid duration';
        }
    }

    private function formatDelayFromCreatedAt(?Carbon $dateTime, string $suffix): string
    {
        return ($this->created_at && $dateTime)
            ? $this->created_at->diffForHumans($dateTime, ['parts' => 2, 'short' => true, 'syntax' => CarbonInterface::DIFF_ABSOLUTE]) . " $suffix"
            : 'N/A';
    }

}
