<?php

namespace App\Rules;

use app\Services\Rota\PSOAuthService;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

class PSOCanAuth implements Rule, DataAwareRule
{
    protected $data = [];
    private PSOAuthService $auth;

    /**
     * Set the data under validation.
     *
     * @param array $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        //
        $this->auth = new PSOAuthService($this->data['username'], $this->data['password'], $this->data['account_id'], $value);
        return $this->auth->isAuthenticated();

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Unable to Authenticate. ' . $this->auth->getErrors()[0];
    }
}
