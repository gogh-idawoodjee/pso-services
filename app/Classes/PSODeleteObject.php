<?php /** @noinspection UnknownInspectionInspection */

namespace App\Classes;


use Illuminate\Support\Arr;

class PSODeleteObject
{

    private array $additional_pk;
    private array $delete_object;


    public function __construct(
        $object_type_id,
        $object_pk_name1, $object_pk1,
        $object_pk_name2 = null, $object_pk2 = null,
        $object_pk_name3 = null, $object_pk3 = null,
        $object_pk_name4 = null, $object_pk4 = null,
        $is_rota_object = false
    )
    {
        $this->additional_pk = [
            'pk2' => [
                'name' => 'object_pk_name2',
                'pk' => 'object_pk2'
            ],
            'pk3' => [
                'name' => 'object_pk_name3',
                'pk' => 'object_pk3'
            ],
            'pk4' => [
                'name' => 'object_pk_name4',
                'pk' => 'object_pk4'
            ],
        ];

        $this->delete_object =
            $this->setDeleteData($object_type_id,
                $object_pk_name1, $object_pk1,
                $object_pk_name2, $object_pk2,
                $object_pk_name3, $object_pk3,
                $object_pk_name4, $object_pk4,
                $is_rota_object
            );

    }

    /** @noinspection PhpUnusedParameterInspection */
    private function setDeleteData($object_type_id, $object_pk_name1, $object_pk1, $object_pk_name2, $object_pk2, $object_pk_name3, $object_pk3, $object_pk_name4, $object_pk4, $is_rota_object)
    {
        $data =
            compact('object_type_id', 'object_pk_name1', 'object_pk1');

        foreach ($this->additional_pk as $pk) {
            if (${$pk['name']}) {
                $data = Arr::add($data, $pk['name'], ${$pk['name']});
                $data = Arr::add($data, $pk['pk'], ${$pk['pk']});
            }
        }

        if ($is_rota_object) {
            $data = Arr::add($data, 'delete_row', true);
        }
        return $data;

    }

    public function toJson()
    {
        return $this->delete_object;
    }

}
