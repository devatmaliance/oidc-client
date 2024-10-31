<?php

namespace atmaliance\oidc_client\interfaces;

use atmaliance\oidc_client\models\dto\UserEntityDTO;
use yii\web\IdentityInterface;

interface UserManagementInterface
{
    public function beforeLogin(UserEntityDTO $entityDTO): bool;
    public function findUserByAttributes(UserEntityDTO $entityDTO): IdentityInterface;
    public function logout(string $uuid): void;
    public function create(UserEntityDTO $userDTO): IdentityInterface;
}