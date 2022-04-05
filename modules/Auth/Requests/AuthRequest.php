<?php

declare(strict_types=1);

namespace Auth\Requests;

use App\Models\User;
use Auth\Dtos\AuthDto;
use Auth\Enums\AuthDriver;
use Auth\Exceptions\InvalidEmailException;
use Auth\Exceptions\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class AuthRequest extends FormRequest
{
    /**
     * The user model instance.
     *
     * @var User
     */
    private $user;

    /**
     * Create a new request instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|string',
            'password' => 'required|string',
            'name' => 'required|string',
            'driver' => [new Enum(AuthDriver::class)],
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge(['driver' => $this->route()->parameter('driver')]);
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return void
     */
    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator->errors()->keys());
    }

    /**
     * Handle a passed validation attempt.
     *
     * @return void
     */
    protected function passedValidation()
    {
        $hasEmail = $this->user->where('email', $this->email)->exists();

        if ($hasEmail) {
            throw new InvalidEmailException();
        }
    }

    /**
     * Transform the data to object.
     *
     * @return AuthDto
     */
    public function toDto(): AuthDto
    {
        return new AuthDto(
            $this->email,
            $this->password,
            $this->name,
            $this->driver
        );
    }
}
