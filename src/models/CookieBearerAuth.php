<?php
namespace atmaliance\oidc_client\models;

use yii\web\IdentityInterface;
use yii\web\Response;
use yii\filters\auth\AuthMethod;

/**
 * CookieBearerAuth is an action filter that supports the authentication through Access Tokens and Refresh Tokens stored in cookies.
 *
 * You can use CookieBearerAuth by attaching it as a behavior to a controller, like the following:
 *
 * ```php
 * public function behaviors()
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
    public string $accessTokenCookie = 'accessToken';
    public string $refreshTokenCookie = 'refreshToken';

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
        $accessToken = $request->cookies->getValue($this->accessTokenCookie);
        $refreshToken = $request->cookies->getValue($this->refreshTokenCookie);

        if ($accessToken !== null && $refreshToken !== null) {
            $identity = $user->loginByAccessToken($accessToken, get_class($this));
            if ($identity !== null) {
                return $identity;
            }

            // Optionally, you can add logic here to handle the case where the Access Token has expired
            // and you want to attempt to refresh it using the Refresh Token.
            // For now, if authentication fails, we throw an UnauthorizedHttpException
            $response->statusCode = 401;
            $response->data = [
                'message' => 'Your request was made with invalid credentials.',
            ];
        }

        return null;
    }

    /**
     * Handles unauthorized access attempt.
     * @param $response
     * @return Response the response to be sent.
     */
    public function handleFailure($response): Response
    {
        $response->data = [
            'message' => 'You are requesting with an invalid credential.',
        ];
        $response->statusCode = 401;
        return $response;
    }
}
