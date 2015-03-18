<?php
/**
 * This file is part of the Imbo package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

use Imbo\Auth\AccessControl\Adapter\AdapterInterface,
    Imbo\Auth\AccessControl\Adapter\AbstractAdapter,
    Imbo\Auth\AccessControl\UserQuery,
    Imbo\Auth\AccessControl\GroupQuery;

/**
 * Use a custom user lookup implementation
 */
class StaticAccessControl extends AbstractAdapter implements AdapterInterface {
    public function hasAccess($publicKey, $resource, $user = null) {
        return $publicKey === 'public';
    }

    public function getPrivateKey($publicKey) {
        return 'private';
    }

    public function getUsers(UserQuery $query = null) {
        return ['public'];
    }

    public function getGroups(GroupQuery $query = null) {
        return [];
    }

    public function getGroup($groupName) {
        return false;
    }

    public function userExists($publicKey) {
        return $publicKey === 'public';
    }

    public function publicKeyExists($publicKey) {
        return $publicKey === 'public';
    }

    public function getAccessListForPublicKey($publicKey) {
        return [];
    }
}

return [
    'accessControl' => new StaticAccessControl(),
];
