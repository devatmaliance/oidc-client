<?php

namespace atmaliance\oidc_client\models;

use atmaliance\oidc_client\models\dto\UserEntityDTO;
use common\models\Outbox;
use DateTimeImmutable;
use sizeg\jwt\Jwt;
use yii\authclient\OAuthToken;
use yii\authclient\OpenIdConnect;
use yii\web\HttpException;

/**
 *
 * @property-read string $stateKeyPrefix
 */
class KeycloakClient extends OpenIdConnect
{
    const NAME = 'keycloak';
    public string $realm = '';


    public function init()
    {
        $this->issuerUrl = "$this->apiBaseUrl/realms/$this->realm";
        $this->setStateStorage(new CookieStateStorage);
        parent::init();
    }

    protected function getStateKeyPrefix(): string
    {
        return sprintf("%s-%s-", self::NAME, 'prefix');
    }

    public function isExpiredToken(string $accessToken): bool
    {
        $accessTokenObj = (new Jwt())->getParser()->parse($accessToken);
        return $accessTokenObj->isExpired();
    }

    /**
     * @throws HttpException
     */
    public function getUserEntityDTO(string $accessToken): UserEntityDTO
    {
        $attributes = $this->loadJws($accessToken);
        $dto = new UserEntityDTO;
        $dto->load($attributes, '');
        return $dto;
    }


    /**
     * Saves tokens as persistent state.
     * @param OAuthToken|null $token auth token to be saved.
     * @return $this the object itself.
     */
    protected function saveAccessToken($token): KeycloakClient
    {
        if (!$token) return $this;

        $tokenParams = $token->params;
        $accessToken = $tokenParams['id_token'] ?? null;
        $refreshToken = $tokenParams['refresh_token'] ?? null;

        $storage = $this->getStateStorage();

        $storage->set(CookieBearerAuth::ACCESS_TOKEN_COOKIE, $accessToken);
        $storage->set(CookieBearerAuth::REFRESH_TOKEN_COOKIE, $refreshToken);

        return $this;
    }
}