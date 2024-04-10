<?php

declare(strict_types=1);

namespace atmaliance\yii2_keycloak\controllers;

use Yii;
use yii\authclient\AuthAction;
use yii\filters\AccessControl;
use yii\web\Controller;
use atmaliance\oidc_client\models\KeycloakClient;

class AuthController extends Controller
{
    public function actions(): array
    {
        return [
            'auth' => [
                'class' => AuthAction::class,
                'successCallback' => [$this, 'onAuthSuccess'],
            ],
        ];
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                ],
            ],
        ];
    }

    public function onAuthSuccess(KeycloakClient $client)
    {
        $attributes = $client->getUserAttributes();
        dd($attributes);
    }
}