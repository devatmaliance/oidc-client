<?php

namespace atmaliance\oidc_client\models\dto;


use yii\base\Model;

class UserEntityDTO extends Model
{
    public string $sub = '';
    public string $email_verified = '';
    public string $name = '';
    public string $preferred_username = '';
    public string $locale = '';
    public string $given_name = '';
    public string $middle_name = '';
    public string $family_name = '';
    public string $email = '';
    public string $phone_number = '';

    public function rules(): array
    {
        return [
            [[
                'phone_number',
                'sub',
                'email_verified',
                'name',
                'preferred_username',
                'locale',
                'given_name',
                'middle_name',
                'family_name',
                'email',
            ], 'safe']
        ];
    }
}
