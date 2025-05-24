<?php

namespace App\Services\V2;

use App\Classes\V2\BaseService;
use App\Models\V2\PSOCommitLog;
use Ramsey\Uuid\Uuid;

class CommitService extends BaseService
{

    public function commitActivity()
    {

        // $data is expected to be teh full SDS payload from PSO

        // create the log
        $inputReference = Uuid::uuid4()->toString();
        $commitLog = new PSOCommitLog();
        $commitLog->create([
            'id' => Uuid::uuid4()->toString(),
            'input_reference' => $inputReference,
        ]);



    }


}
