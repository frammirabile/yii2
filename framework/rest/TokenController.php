<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2019 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

use yii\base\{InvalidConfigException, InvalidValueException};
use yii\db\ActiveRecordInterface;
use yii\web\ServerErrorHttpException;

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
        'keysReplacements' => null
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
       return ['class' => HttpOAuth2::class];
    }

    /**
     * @return TokenInterface
     * @throws InvalidConfigException
     * @throws ServerErrorHttpException
     */
    public function actionCreate(): TokenInterface
    {
        /** @var TokenInterface|ActiveRecordInterface $token */
        $token = \Yii::$app->user->getToken();

        if ($token !== null && $token->getIsValid() && \Yii::$app->getRequest()->getBodyParam('grant_type') == 'refresh_token')
            throw new ServerErrorHttpException(\Yii::t('yii', 'Token cannot be refreshed'));

        try {
            if ($token === null || !$token->getIsValid() && $token->delete()) {
                $token = new \Yii::$app->user->tokenClass;
                $token->save();

                \Yii::$app->getResponse()->setStatusCode(201);
            }

            if (!$token->getIsValid())
                throw new InvalidValueException('Invalid token');
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            throw new ServerErrorHttpException(\Yii::t('yii', 'Token cannot be created'));
        }

        \Yii::$app->getResponse()->getHeaders()->add('Cache-Control', 'no-store')->add('Pragma', 'no-cache');

        return $token;
    }
}