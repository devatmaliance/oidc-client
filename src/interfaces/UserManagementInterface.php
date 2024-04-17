<?php

namespace atmaliance\oidc_client\interfaces;

use atmaliance\oidc_client\models\dto\UserEntityDTO;
use yii\web\IdentityInterface;

interface UserManagementInterface
{
    public function findUserByAttributes(UserEntityDTO $entityDTO): IdentityInterface;
    public function logout(string $uuid): void;
}