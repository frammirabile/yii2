<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2018 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

use yii\base\InvalidConfigException;
use yii\filters\auth\AuthMethod;

/**
 * OAuth2 authentication
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class HttpOAuth2 extends AuthMethod
{
    /**
     * @var string the HTTP authentication realm
     */
    public $realm = 'api';

    /**
     * {@inheritdoc}
     * @param User $user
     * @throws InvalidConfigException
     */
    public function authenticate($user, $request, $response): ?IdentityInterface
    {
        list($id, $secret) = $request->getAuthCredentials();

        if ((\Yii::$app->request->client = Client::findOne(['id' => $id, 'secret' => $secret])) === null)
            return null;

        $request = \Yii::$app->getRequest()->getBodyParams();

        if (!isset($request['grant_type']))
            return null;

        switch ($request['grant_type']) {
            case 'password':
                if (!isset($request['username'], $request['password']))
                    return null;

                $identity = $user->authenticate($request['username'], $request['password']);

                break;
            case 'refresh_token':
                if (!isset($request['refresh_token']))
                    return null;

                $identity = $user->authenticateByRefreshToken($request['refresh_token']);

                break;
            default:
                return null;
        }

        if ($identity === null)
            $this->handleFailure($response);

        return $identity;
    }

    /**
     * @param Response $response
     * @return void
     */
    public function challenge($response): void
    {
        $response->getHeaders()->set('WWW-Authenticate', "Basic realm=\"{$this->realm}\"");
    }
}