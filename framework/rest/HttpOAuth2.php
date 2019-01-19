<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2018 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

use yii\filters\auth\HttpBasicAuth;

/**
 * OAuth2 authentication
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class HttpOAuth2 extends HttpBasicAuth
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function init(): void
    {
        $this->auth = function(string $id, string $secret): ?bool {
            if ((\Yii::$app->request->client = Client::findOne(['id' => $id, 'secret' => $secret])) === null)
                return null;

            $request = \Yii::$app->getRequest()->getBodyParams();

            if (!isset($request['grant_type']))
                return null;

            switch ($request['grant_type']) {
                case 'password':
                    if (!isset($request['username'], $request['password']))
                        return null;

                    return !\Yii::$app->user->authenticate($request['username'], $request['password']);

                    break;
                case 'refresh_token':
                    if (!isset($request['refresh_token']))
                        return null;

                    return !\Yii::$app->user->authenticateByRefreshToken($request['refresh_token']);

                    break;
                default:
                    return null;
            }
        };
    }
}