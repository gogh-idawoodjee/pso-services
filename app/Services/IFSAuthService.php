<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\HttpClientException;


class IFSAuthService
{
    public string $pso_token;
    private string $pso_full_url;
    private string $account_id;
    private string $username;
    private string $password;


    public function __construct($base_url, $account_id, $username, $password)
    {

        $this->pso_full_url = $base_url . '/IFSSchedulingRESTfulGateway/api/v1/scheduling/session';
        $this->account_id = $account_id;
        $this->username = $username;
        $this->password = $password;

        return $this;
    }

    public function getToken($platform)
    {
        if ($platform == 'fsm') {
            return $this->GetFSMToken();
        }

        if ($platform == 'pso') {
            return $this->GetPSOToken();
        }

        return false;
    }

    private function GetPSOToken()
    {

        try {
            $response = Http::asForm()->post($this->pso_full_url, [
                'accountId' => $this->account_id,
                'username' => $this->username,
                'password' => $this->password,
            ]);
        } catch (Exception $e) {
            dd($e);
        }
//         todo find a cleaner way of doing this
        return $this->pso_token = $response->collect()->get('SessionToken');
    }


    private function GetFSMToken()
    {
//        try {
//            $response = Http::withBasicAuth(config('ifs.fsm.' . $this->fsm_env . '.username'), config('ifs.fsm.' . $this->fsm_env . '.password'))
//                ->post($this->fsm_full_url);
//        } catch (Exception $e) {
//            dd($e);
//        }
//
////        return $response->collect();
//        return $this->fsm_token = $response->collect()->get('value');
    }

    private function isAuthenticated()
    {

        try {
            $token_response = $this->GetPSOToken();
        } catch (HttpClientException $e) {
            $this->handleException($e);
            return false;
        }

        if (!$token_response->has('SessionToken')) {
            $this->handleException($token_response->get('Message'));
            return false;
        }

        $this->pso_token = $token_response->get('SessionToken');
        return true;

    }

    private function handleException($error)
    {
        // $this->run->status = 'error: ' . $error;
        //$this->run->save();
    }
}
