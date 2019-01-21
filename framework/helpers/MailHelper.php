<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2019 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\helpers;

use yii\base\InvalidConfigException;
use yii\validators\EmailValidator;
use yii\web\ServerErrorHttpException;

/**
 * Mail helper
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 *
 * @tbd sistemare:
 *  - rimuovere e usare frammirabile/yii2-notification
 *  - convertire automaticamente i valori in mail: float -> asCurrency, date -> asDate
 */
class MailHelper
{
    /**
     * @param string $view
     * @param string $subject
     * @param string $from
     * @param string $to
     * @param array $params
     * @param null|string $error
     * @return void
     * @throws InvalidConfigException
     * @throws ServerErrorHttpException
     */
    public static function sendHtmlMail(string $view, string $subject, string $from, string $to, array $params = [], ?string $error = null): void
    {
        if (strpos($from, '@') === false)
            $from .= '@'.Url::domain();

        foreach ([$from, $to] as $email)
            if (!(new EmailValidator)->validate($email, $error))
                throw new InvalidConfigException($error);

        if (!\Yii::$app->mailer
            ->compose($view, $params)
            ->setFrom($from)
            ->setTo($to)
            ->setSubject($subject)
            ->send())
            throw new ServerErrorHttpException($error ?: \Yii::t('api', '{view} mail not sent to <{to}>', ['view' => Inflector::titleize($view, true), 'to' => $to]));
    }
}