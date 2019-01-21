<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2019 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

use yii\filters\auth\HttpBasicAuth;
use yii\web\UnauthorizedHttpException;

/**
 * Client authentication
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class HttpClientAuth extends HttpBasicAuth
{
    /**
     * Authenticates the current client
     *
     * @param User $user
     * @param Request $request
     * @param Response $response
     * @return bool|null
     * @throws UnauthorizedHttpException
     */
    public function authenticate($user, $request, $response): ?bool
    {
        list($id, $secret) = $request->getAuthCredentials();

        if (($client = Client::findOne(['id' => $id, 'secret' => $secret])) === null) {
            $this->handleFailure($response);

            return null;
        }

        \Yii::$app->request->client = $client;

        return true;
    }
}