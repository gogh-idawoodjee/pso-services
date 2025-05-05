<?php

namespace App\Http\Requests\Api\V2;

use App\Classes\PSOObjectRegistry;
use Illuminate\Validation\Rule;

class DeleteObjectRequest extends BaseFormRequest
{
    protected bool $groupErrors = false; // 👈 set to true for grouped under data.attributes, false for field-by-field

    public function rules(): array
    {
        $commonRules = $this->commonRules();

        $additionalRules = [
            /**
             * The type of object to delete.
             * Must be one of:
             * "activity_sla", "activity_skill", "shift", "activity", "resource",
             * "location", "unavailability", "location_region", "schedule_event",
             * "resource_region", "resource_region_availability".
             * @var string
             * @example "activity"
             */
            'data.objectType' => [
                'required',
                'string',
                Rule::in(array_keys(PSOObjectRegistry::all())),
            ],
            // No pk fields yet — added dynamically in withValidator()
        ];

        return array_merge($commonRules, $additionalRules);
    }


    public function setGroupErrors(bool $shouldGroupErrors): static
    {
        $this->groupErrors = $shouldGroupErrors;
        return $this;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $data = $this->get('data', []);

            $objectType = $data['objectType'] ?? null;

            if ($objectType) {
                $registry = PSOObjectRegistry::get($objectType);

                if (!$registry) {
                    $validator->errors()->add('data.objectType', 'Invalid object type provided.');
                    return;
                }

                $attributes = $registry['attributes'] ?? [];
                $friendlyLabel = $registry['label'] ?? $objectType;

                $attributeErrors = []; // Collect errors if needed

                foreach ($attributes as $index => $attribute) {
                    $pkIndex = $index + 1;
                    $pkField = "objectPk{$pkIndex}";
                    $attributeName = $attribute['name'] ?? "Attribute {$pkIndex}";

                    if (!array_key_exists($pkField, $data)) {
                        $message = "The field {$pkField} ({$attributeName}) is required for {$friendlyLabel}.";

                        if ($this->groupErrors) {
                            $attributeErrors[] = $message;
                        } else {
                            $validator->errors()->add("data.{$pkField}", $message);
                        }

                        continue;
                    }

                    $value = $data[$pkField];

                    if (!$this->matchesExpectedType($value, $attribute['type'] ?? null)) {
                        $message = "The field {$pkField} ({$attributeName}) must be of type {$attribute['type']} for {$friendlyLabel}.";

                        if ($this->groupErrors) {
                            $attributeErrors[] = $message;
                        } else {
                            $validator->errors()->add("data.{$pkField}", $message);
                        }
                    }
                }

                if ($this->groupErrors && !empty($attributeErrors)) {
                    $validator->errors()->add('data.attributes', $attributeErrors);
                }
            }
        });
    }



    /**
     * Match value against expected type.
     */
    protected function matchesExpectedType(mixed $value, string|null $expectedType): bool
    {
        if ($expectedType === null) {
            return true; // No type specified, assume OK
        }

        return match ($expectedType) {
            'string' => is_string($value),
            'int', 'integer' => filter_var($value, FILTER_VALIDATE_INT) !== false,
            'boolean', 'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null,
            default => true,
        };
    }


}
