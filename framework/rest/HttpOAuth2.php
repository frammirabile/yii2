<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2019 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

use yii\base\InvalidConfigException;

/**
 * OAuth2 authentication
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class HttpOAuth2 extends HttpClientAuth
{
    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function authenticate($user, $request, $response): ?bool
    {
        if (parent::authenticate($user, $request, $response) === null)
            return null;

        $request = \Yii::$app->getRequest()->getBodyParams();

        if (!isset($request['grant_type']))
            return null;

        switch ($request['grant_type']) {
            case 'password':
                if (isset($request['username'], $request['password']))
                    $identity = $user->authenticate($request['username'], $request['password']);

                break;
            case 'refresh_token':
                if (isset($request['refresh_token']))
                    $identity = $user->authenticateByRefreshToken($request['refresh_token']);
        }

        if (isset($identity))
            return true;

        $this->handleFailure($response);

        return false;
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