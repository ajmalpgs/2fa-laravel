<?php

namespace TwoFA\LaravelMfa\Http\Requests;

use TwoFA\LaravelMfa\Support\MfaTranslator;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

class VerifyMfaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['code' => ['required', 'string', 'max:32']];
    }

    public function attributes(): array
    {
        return [
            'code' => MfaTranslator::get('code'),
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($this->routeIs(config('mfa.route_name_prefix').'setup.verify')) {
            throw new HttpResponseException(
                redirect($this->getRedirectUrl())
                    ->withInput($this->except($this->dontFlash))
                    ->with('otperror', $validator->errors()->first())
            );
        }

        throw new ValidationException(
            $validator,
            redirect($this->getRedirectUrl())
                ->withErrors($validator, $this->errorBag)
                ->with('otperror', $validator->errors()->first())
        );
    }
}
