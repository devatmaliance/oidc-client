<?php

declare(strict_types=1);

namespace atmaliance\oidc_client\modules;

use yii\base\Module;

class AuthOpenidConnectModule extends Module
{
    public ?string $controllerNamespace = 'atmaliance\oidc_client\controllers';
}