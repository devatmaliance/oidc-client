<?php

declare(strict_types=1);

namespace atmaliance\oidc_client\controllers;

use atmaliance\oidc_client\interfaces\UserManagementInterface;
use atmaliance\oidc_client\models\CookieBearerAuth;
use atmaliance\oidc_client\models\dto\UserEntityDTO;
use common\exceptions\ModelNotFoundException;
use Yii;
use yii\authclient\AuthAction;
use yii\authclient\OAuthToken;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\web\Controller;
use atmaliance\oidc_client\models\KeycloakClient;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use function Symfony\Component\Translation\t;

class AuthController extends Controller
{
    private UserManagementInterface $userManagement;
    private string $clientCollection;

    /**
     * UserManagementInterface DI components. Path: config/main.php -> container[singleton] = class
     */
    public function __construct($id, $module, UserManagementInterface $userManagement, $config = [])
    {
        $this->clientCollection = 'authClientCollection';
        $this->userManagement = $userManagement;
        parent::__construct($id, $module, $config);
    }

    public function actions(): array
    {
        return [
            'index' => [
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
            ]
        ];
    }

    public function actionLogout(string $authclient): Response
    {
        $collection = Yii::$app->get($this->clientCollection);
        if (!$collection->hasClient($authclient)) {
            throw new NotFoundHttpException("Unknown auth client '{$authclient}'");
        }
        $client = $collection->getClient($authclient);

        $storage = $client->getStateStorage();
        $accessToken = $storage->get(CookieBearerAuth::ACCESS_TOKEN_COOKIE);
        if (!$accessToken) {
            throw new Exception('Not found access token');
        }

        $userEntityDTO = $client->getUserEntityDTO($accessToken);
        $storage->remove(CookieBearerAuth::REFRESH_TOKEN_COOKIE);
        $storage->remove(CookieBearerAuth::ACCESS_TOKEN_COOKIE);

        $this->userManagement->logout($userEntityDTO->sub);
        Yii::$app->user->logout();
        return $this->goHome();
    }

    public function onAuthSuccess(KeycloakClient $client)
    {
        $attributes = $client->getUserAttributes();
        $userEntityDTO = new UserEntityDTO();
        $userEntityDTO->load($attributes, '');

        $user = $this->userManagement->getIdentity($userEntityDTO);
        if ($this->userManagement->beforeLogin($userEntityDTO, $user)) {
            $client->setAccessToken($client->getAccessToken());
            Yii::$app->user->login($user);
        }
    }
}