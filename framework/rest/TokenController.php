<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2018 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

use yii\filters\auth\HttpBasicAuth;
use yii\web\{BadRequestHttpException, ServerErrorHttpException};

/**
 * Rest token controller
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class TokenController extends ActiveController
{
    /**
     * {@inheritdoc}
     */
    public $serializer = [
        'class' => Serializer::class,
        'variablizeKeys' => false,
        'keysReplacement' => null
    ];

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return ['options' => 'yii\rest\OptionsAction'];
    }

    /**
     * {@inheritdoc}
     */
    protected function verbs(): array
    {
        return ['create' => ['POST']];
    }

    /**
     * {@inheritdoc}
     */
    protected function authMethods(): array
    {
       return ['class' => HttpBasicAuth::class];

        #tbd auth per autenticazione del client
    }

    /**
     * @return TokenInterface
     * @throws BadRequestHttpException
     * @throws ServerErrorHttpException
     */
    public function actionCreate(): TokenInterface
    {
        $request = \Yii::$app->request->bodyParams;

        if (!isset($request['grant_type']))
            throw new BadRequestHttpException(\Yii::t('api', 'Invalid request'), 1); #tbd sistemare traduzioni

        switch ($request['grant_type']) {
            case 'password':
                if (!isset($request['username'], $request['password']))
                    throw new BadRequestHttpException(\Yii::t('api', 'Invalid request'), 2);

                if (!\Yii::$app->user->authenticate($request['username'], $request['password']))
                    throw new BadRequestHttpException('Invalid grant', 1);

                if (!isset(\Yii::$app->user->token) || !\Yii::$app->user->token->isValid()) {
                    if (!\Yii::$app->user->refreshToken())
                        throw new ServerErrorHttpException(\Yii::t('api', 'Token cannot be created'));

                    \Yii::$app->response->setStatusCode(201);
                }

                break;
            case 'refresh_token':
                if (!isset($request['refresh_token']))
                    throw new BadRequestHttpException(\Yii::t('api', 'Invalid request'), 3);

                if (!\Yii::$app->user->authenticateByRefreshToken($request['refresh_token']))
                    throw new BadRequestHttpException('Invalid grant', 2);

                if (!\Yii::$app->user->refreshToken())
                    throw new ServerErrorHttpException(\Yii::t('api', 'Token cannot be refreshed'));

                \Yii::$app->response->setStatusCode(201);

                break;
            default:
                throw new BadRequestHttpException(\Yii::t('api', 'Unsupported grant type'));
        }

        \Yii::$app->response->headers
            ->add('Cache-Control', 'no-store')
            ->add('Pragma', 'no-cache');

        return \Yii::$app->user->token;
    }
}