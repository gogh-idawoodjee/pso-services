<?php

use App\Http\Requests\Api\V2\DeleteObjectRequest;
use Illuminate\Validation\Validator;

function deleteObjectValidator(array $data): Validator
{
    $request = DeleteObjectRequest::create('/api/v2/delete', 'DELETE', [
        'environment' => ['sendToPso' => false, 'datasetId' => 'dataset_123'],
        'data' => $data,
    ]);

    $validator = validator($request->all(), $request->rules());
    $request->withValidator($validator);

    return $validator;
}

it('requires the primary keys of the selected object type', function (): void {
    $validator = deleteObjectValidator(['objectType' => 'Activity']);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->keys())->toContain('data.objectPk1');
});

it('rejects an empty primary key', function (): void {
    $validator = deleteObjectValidator(['objectType' => 'Activity', 'objectPk1' => '']);

    expect($validator->errors()->keys())->toContain('data.objectPk1');
});

it('does not require primary keys the object type does not have', function (): void {
    $validator = deleteObjectValidator(['objectType' => 'Activity', 'objectPk1' => 'ACT-001']);

    expect($validator->errors()->keys())
        ->not->toContain('data.objectPk1')
        ->not->toContain('data.objectPk2');
});
