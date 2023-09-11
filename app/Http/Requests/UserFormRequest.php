<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $user = $this->route('user');

        $rules = [
            'firstname' => ['required', 'string'],
            'lastname' => ['required', 'string'],
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user),
            ],
            'role' => ['required', 'in:admin,client'],
            'presentation' => ['nullable', 'string', 'max:200'],
        ];

        if ($this->isSelfUpdate()) {
            $rules['password'] = ['required', 'string', 'min:8', 'regex:/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/'];
        }

        return $rules;
    }

    // Détermine si l'utilisateur met à jour son propre compte
    protected function isSelfUpdate()
    {
        $user = Auth::user();
        return $this->route()->named('admin.users.update') && $this->route('user')->id == $user->id;
    }
}
