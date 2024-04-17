<?php

namespace atmaliance\oidc_client\models;

use Yii;
use yii\authclient\clients\Yandex;
use yii\authclient\OAuthToken;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\IdentityInterface;
use yii\web\Response;
use yii\filters\auth\AuthMethod;

/**
 * CookieBearerAuth is an action filter that supports the authentication through Access Tokens and Refresh Tokens stored in cookies.
 *
 * You can use CookieBearerAuth by attaching it as a behavior to a controller, like the following:
 *
 * ```php
 * public function behaviors(): array
 * {
 *     return [
 *         'cookieAuth' => [
 *             'class' => \app\components\CookieBearerAuth::class,
 *         ],
 *     ];
 * }
 * ```
 */
class CookieBearerAuth extends AuthMethod
{
    const ACCESS_TOKEN_COOKIE = 'accessToken';
    const REFRESH_TOKEN_COOKIE = 'refreshToken';

    /**
     * Authenticates the user based on `accessToken` and `refreshToken` cookies.
     * This method attempts to authenticate a user using the Access Token and Refresh Token provided in cookies.
     * @param $user
     * @param $request
     * @param $response
     * @return null|IdentityInterface the authenticated user model or null if authentication fails.
     */
    public function authenticate($user, $request, $response): ?IdentityInterface
    {
        $accessToken = $request->cookies->getValue(self::ACCESS_TOKEN_COOKIE);
        $refreshToken = $request->cookies->getValue(self::REFRESH_TOKEN_COOKIE);

        if (!$accessToken || !$refreshToken) {
            $response->statusCode = 401;
            return null;
        }

        /** @var KeycloakClient $client */
        $client = Yii::$app->authClientCollection->getClient(KeycloakClient::NAME);
        $isExpiredAccessToken = $client->isExpiredToken($accessToken);

        if ($isExpiredAccessToken && !$client->isExpiredToken($refreshToken)) {
            $oauthTokenForRefresh = new OAuthToken;
            $oauthTokenForRefresh->setParam('refresh_token', $refreshToken);

            $oauthToken = $client->refreshAccessToken($oauthTokenForRefresh);
            $accessToken = $oauthToken->token;
            return $user->loginByAccessToken($accessToken, get_class($this));
        }


        return !$isExpiredAccessToken
            ? $user->loginByAccessToken($accessToken, get_class($this))
            : null;
    }

    /**
     * Handles unauthorized access attempt.
     * @param $response
     * @return Response the response to be sent.
     * @throws ForbiddenHttpException
     */
    public function handleFailure($response): Response
    {
        $user = Yii::$app->user;
        if ($user !== false && $user->isGuest) {
            return $user->loginRequired();
        } else {
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }
    }
}
