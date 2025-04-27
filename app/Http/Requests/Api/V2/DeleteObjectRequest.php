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
            'data.object_type' => [
                'required',
                'string',
                Rule::in(array_keys(PSOObjectRegistry::all())), // 👈 validate against valid object types
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
            $data = $this->input('data', []);

            $objectType = $data['object_type'] ?? null;

            if ($objectType) {
                $registry = PSOObjectRegistry::get($objectType);

                if (!$registry) {
                    $validator->errors()->add('data.object_type', 'Invalid object type provided.');
                    return;
                }

                $attributes = $registry['attributes'] ?? [];
                $friendlyLabel = $registry['label'] ?? $objectType;

                $attributeErrors = []; // Collect errors if needed

                foreach ($attributes as $index => $attribute) {
                    $pkIndex = $index + 1;
                    $pkField = "object_pk{$pkIndex}";
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
