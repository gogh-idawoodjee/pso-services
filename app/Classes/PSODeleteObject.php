<?php

namespace App\Classes;


use Illuminate\Support\Arr;

class PSODeleteObject
{

    private array $additional_pk;
    private array $delete_object;


    public function __construct($object_type_id, $object_pk_name1, $object_pk1, $object_pk_name2 = null, $object_pk2 = null, $object_pk_name3 = null, $object_pk3 = null, $object_pk_name4 = null, $object_pk4 = null)
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
            $this->setDeleteData($object_type_id, $object_pk_name1, $object_pk1, $object_pk_name2, $object_pk2, $object_pk_name3, $object_pk3, $object_pk_name4, $object_pk4);

//        $this->addPKs($delete_data);

    }

    private function setDeleteData($object_type_id, $object_pk_name1, $object_pk1, $object_pk_name2, $object_pk2, $object_pk_name3, $object_pk3, $object_pk_name4, $object_pk4)
    {
        $data =
            [
                'object_type_id' => $object_type_id,
                'object_pk_name1' => $object_pk_name1,
                'object_pk1' => $object_pk1
            ];

        foreach ($this->additional_pk as $pk) {
            if (${$pk['name']}) {
                $data = Arr::add($data, $pk['name'], ${$pk['pk']});
            }

            return $data;
        }

//    private function addPKs($delete_data)
//    {
//        foreach ($this->additional_pk as $pk) {
//            if (Arr::has($delete_data, $pk['name'])) {
//                $this->delete_object = Arr::add($this->delete_object, $pk['name'], $delete_data[$pk['pk']]);
//            }
//        }
    }

    public function toJson()
    {
        return $this->delete_object;
    }

}
