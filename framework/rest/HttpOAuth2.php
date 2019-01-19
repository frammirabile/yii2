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
                throw new BadRequestHttpException(\Yii::t('api', 'Invalid request'), 1);

            switch ($request['grant_type']) {
                case 'password':
                    if (!isset($request['username'], $request['password']))
                        throw new BadRequestHttpException(\Yii::t('api', 'Invalid request'), 2);

                    if (!\Yii::$app->user->authenticate($request['username'], $request['password']))
                        throw new BadRequestHttpException('Invalid grant', 1);

                    if (!isset(\Yii::$app->user->token) || !\Yii::$app->user->token->isValid()) {
                        if (!\Yii::$app->user->refreshToken())
                            throw new ServerErrorHttpException(\Yii::t('api', 'Token cannot be created'));

                        \Yii::$app->getResponse()->setStatusCode(201);
                    }

                    break;
                case 'refresh_token':
                    if (!isset($request['refresh_token']))
                        throw new BadRequestHttpException(\Yii::t('api', 'Invalid request'), 3);

                    if (!\Yii::$app->user->authenticateByRefreshToken($request['refresh_token']))
                        throw new BadRequestHttpException('Invalid grant', 2);

                    if (!\Yii::$app->user->refreshToken())
                        throw new ServerErrorHttpException(\Yii::t('api', 'Token cannot be refreshed'));

                    \Yii::$app->getResponse()->setStatusCode(201);

                    break;
                default:
                    throw new BadRequestHttpException(\Yii::t('api', 'Unsupported grant type'));
            }

            \Yii::$app->getResponse()->getHeaders()
                ->add('Cache-Control', 'no-store')
                ->add('Pragma', 'no-cache');

            return \Yii::$app->user->getToken();
        };
    }
}