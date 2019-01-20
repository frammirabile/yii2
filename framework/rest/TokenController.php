<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2018 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

use yii\base\InvalidConfigException;
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
       return ['class' => HttpOAuth2::class];
    }

    /**
     * @return TokenInterface
     * @throws InvalidConfigException
     * @throws ServerErrorHttpException
     */
    public function actionCreate(): TokenInterface
    {
        $token = \Yii::$app->user->getToken();

        if ($token !== null & !$token->isValid())
            try {
                /** @var ActiveRecordInterface $token */
                $token->delete();
                $token = new \Yii::$app->user->tokenClass;
                $token->save();

                \Yii::$app->getResponse()->setStatusCode(201);
            } catch (\Throwable $e) {
                \Yii::error($e->getMessage(), __METHOD__);
                throw new ServerErrorHttpException('Token cannot be created');
            }
        elseif (\Yii::$app->getRequest()->getBodyParam('grant_type') == 'refresh_token')
            throw new ServerErrorHttpException('Token cannot be refreshed');

        \Yii::$app->getResponse()->getHeaders()
            ->add('Cache-Control', 'no-store')
            ->add('Pragma', 'no-cache');

        return $token;
    }
}