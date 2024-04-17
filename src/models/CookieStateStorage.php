<?php

namespace atmaliance\oidc_client\models;

use yii\authclient\StateStorageInterface;
use Yii;
use yii\web\Cookie;

class CookieStateStorage implements StateStorageInterface
{
    /**
     * Sets a state variable.
     *
     * @param string $key the key of the state variable.
     * @param mixed $value the value of the state variable.
     */
    public function set($key, $value)
    {
        $cookie = new Cookie([
            'name' => $key,
            'value' => $value,
            'expire' => time() + 86400 * 30, // 30 days
            'httpOnly' => true, 
        ]);
        Yii::$app->response->cookies->add($cookie);
    }

    /**
     * Gets a state variable.
     *
     * @param string $key the key of the state variable.
     * @return mixed the value of the state variable, null if the variable does not exist.
     */
    public function get($key)
    {
        $cookies = Yii::$app->request->cookies;
        return $cookies->getValue($key, null);
    }

    /**
     * Removes a state variable.
     *
     * @param string $key the key of the state variable.
     * @return bool whether the variable is successfully removed.
     */
    public function remove($key)
    {
        $cookies = Yii::$app->response->cookies;
        return $cookies->remove($key);
    }
}
