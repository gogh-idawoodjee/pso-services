<?php

namespace App\Http\Requests\Api\V2;

use App\Classes\V2\PSOObjectRegistry;
use Illuminate\Validation\Rule;

class DeleteObjectRequest extends BaseFormRequest
{
    protected bool $groupErrors = false;

    public function rules(): array
    {
        // Keep this method free of method calls and assignments beyond this literal:
        // Scramble drops every docblock below if it can't statically read the rules.
        // The objectPkN rules are placeholders for documentation; withValidator()
        // makes as many of them required as the selected object type has keys.
        $commonRules = $this->commonRules();

        $additionalRules = [
            /**
             * The type of object to delete, by label or entity name, e.g. "Activity", "Resource", "Shift".
             *
             * @var string
             *
             * @example "Activity"
             */
            'data.objectType' => [
                'required',
                'string',
                Rule::in(
                    array_merge(
                        collect(PSOObjectRegistry::all())->pluck('label')->toArray(),
                        collect(PSOObjectRegistry::all())->pluck('entity')->toArray()
                    )
                ),
            ],

            /**
             * First primary key of the object. Required; the number of keys (objectPk1 to objectPk4)
             * depends on the object type, e.g. an Activity needs only objectPk1 (the activity ID).
             *
             * @var string
             *
             * @example "ACT-001"
             */
            'data.objectPk1' => ['nullable'],

            /**
             * Second primary key of the object, for object types with a composite key.
             *
             * @var string
             *
             * @example "RES-001"
             */
            'data.objectPk2' => ['nullable'],

            /**
             * Third primary key of the object, for object types with a composite key.
             *
             * @var string
             *
             * @example "2025-05-05T08:00:00"
             */
            'data.objectPk3' => ['nullable'],

            /**
             * Fourth primary key of the object, for object types with a composite key.
             *
             * @var string
             *
             * @example "2025-05-05T16:00:00"
             */
            'data.objectPk4' => ['nullable'],

            /**
             * Reference datetime for this write, used as "now" by PSO instead of
             * the actual current time when supplied. Defaults to now when omitted.
             *
             * @var string|null
             *
             * @example "2025-04-30T14:30:00"
             */
            'data.inputDatetime' => ['nullable', 'date'],
        ];

        return array_merge($commonRules, $additionalRules);
    }

    /**
     * Dynamically add objectPkX rules based on objectType, if provided.
     *
     * @return array<string, array<int, string>>
     */
    private function requiredObjectKeyRules(): array
    {
        $rules = [];

        $objectTypeLabel = data_get($this->input('data'), 'objectType');

        if ($objectTypeLabel) {
            $key = collect(PSOObjectRegistry::all())
                ->filter(static fn($entry) =>
                    strtolower($entry['label']) === strtolower($objectTypeLabel)
                    || strtolower($entry['entity']) === strtolower($objectTypeLabel)
                )
                ->keys()
                ->first();

            if ($key) {
                $registry = PSOObjectRegistry::get($key);
                $attributes = $registry['attributes'] ?? [];

                foreach ($attributes as $index => $attribute) {
                    $pkIndex = $index + 1;
                    $pkField = "data.objectPk{$pkIndex}";
                    $rules[$pkField] = ['required']; // Add type rules if needed
                }
            }
        }

        return $rules;
    }

    public function setGroupErrors(bool $shouldGroupErrors): static
    {
        $this->groupErrors = $shouldGroupErrors;
        return $this;
    }

    public function withValidator($validator): void
    {
        $validator->addRules($this->requiredObjectKeyRules());

        $validator->after(function ($validator) {
            $data = $this->get('data', []);
            $rawObjectType = $data['objectType'] ?? null;

            if (!$rawObjectType) {
                return;
            }

            // Use the new resolveKey helper to get the registry key
            $key = PSOObjectRegistry::resolveKey($rawObjectType);

            if (!$key) {
                $validator->errors()->add('data.objectType', "Unknown object type label '{$rawObjectType}'");
                return;
            }

            $registry = PSOObjectRegistry::get($key);
            $expectedLabel = $registry['label'] ?? null;

            // Normalize the label in the request data
            $this->merge([
                'data' => array_merge($data, [
                    'objectType' => $expectedLabel,
                ]),
            ]);

            $providedLabel = $data['label'] ?? null;

            if (
                $providedLabel !== null &&
                $expectedLabel !== null &&
                strtolower($providedLabel) !== strtolower($expectedLabel)
            ) {
                $validator->errors()->add(
                    'data.label',
                    "The label '{$providedLabel}' does not match the expected label '{$expectedLabel}' for object type '{$rawObjectType}'."
                );
            }

            $attributes = $registry['attributes'] ?? [];
            $friendlyLabel = $expectedLabel ?? $rawObjectType;
            $attributeErrors = [];

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
        });
    }

    protected function matchesExpectedType(mixed $value, string|null $expectedType): bool
    {
        if ($expectedType === null) {
            return true;
        }

        return match ($expectedType) {
            'string' => is_string($value),
            'int', 'integer' => filter_var($value, FILTER_VALIDATE_INT) !== false,
            'boolean', 'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null,
            default => true,
        };
    }
}
