<?php

namespace App\Http\Requests\Api\V2;

use App\Classes\PSOObjectRegistry;
use Illuminate\Validation\Rule;

class DeleteObjectRequest extends BaseFormRequest
{
    protected bool $groupErrors = false;

    public function rules(): array
    {
        $commonRules = $this->commonRules();

        $rules = [
            'data.objectType' => [
                'required',
                'string',
                Rule::in(array_values(PSOObjectRegistry::forSelect())),
            ],
        ];

        // Dynamically add objectPkX rules based on objectType, if provided
        $objectTypeLabel = data_get($this->input('data'), 'objectType');

        if ($objectTypeLabel) {
            $key = collect(PSOObjectRegistry::all())
                ->filter(fn($entry) => strtolower($entry['label']) === strtolower($objectTypeLabel))
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

        return array_merge($commonRules, $rules);
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
            $label = $data['objectType'] ?? null;

            if (!$label) {
                return;
            }

            // 🔁 Map label back to registry key
            $key = collect(PSOObjectRegistry::all())
                ->filter(fn($entry) => strtolower($entry['label']) === strtolower($label))
                ->keys()
                ->first();

            if (!$key) {
                $validator->errors()->add('data.objectType', "Unknown object type label '{$label}'");
                return;
            }

            $registry = PSOObjectRegistry::get($key);

            if (!$registry) {
                $validator->errors()->add('data.objectType', 'Invalid object type provided.');
                return;
            }

            $expectedLabel = $registry['label'] ?? null;
            $providedLabel = $data['label'] ?? null;

            if (
                $providedLabel !== null &&
                $expectedLabel !== null &&
                strtolower($providedLabel) !== strtolower($expectedLabel)
            ) {
                $validator->errors()->add(
                    'data.label',
                    "The label '{$providedLabel}' does not match the expected label '{$expectedLabel}' for object type '{$label}'."
                );
            }

            $attributes = $registry['attributes'] ?? [];
            $friendlyLabel = $expectedLabel ?? $label;
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
