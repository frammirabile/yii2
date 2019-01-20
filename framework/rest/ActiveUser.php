<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2018 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;

/**
 * Rest user model
 *
 * @property-read int $id
 * @property string $username
 * @property-write string $password
 * @property-read string $password_reset_code tbd da valorizzare all'invio della mail in formato hash
 * @property-read int $identity_id
 * @property-write bool $active
 * @property-read bool $isActive
 * @property-read int $created_at
 * @property-read int $updated_at
 *
 * @property-read IdentityInterface $identity
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class ActiveUser extends ActiveRecord implements UserInterface
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public static function findByUsername(string $username): ?IdentityInterface
    {
        return self::findOne(['username' => $username, 'isActive' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findByAccessToken(TokenInterface $token, $type = null): ?IdentityInterface
    {
        return static::findOne(['id' => $token->getUserId(), 'isActive' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [TimestampBehavior::class]);
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['username', 'password', 'email'], 'required'],
            'password' => ['password', 'string', 'when' => function() {
                return $this->isAttributeChanged('password');
            }],
            'email' => ['email', 'string'],
            ['email', 'email'],
            ['active', 'default', 'value' => false],
            ['active', 'boolean'],
            ['username', 'unique'],
            ['email', 'unique']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios(): array
    {
        return [
            self::SCENARIO_CREATE => ['username', 'password', 'email'],
            self::SCENARIO_UPDATE => ['']
        ];
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert))
            return false;

        if ($this->isAttributeChanged('password'))
            $this->setPassword($this->password);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function fields(): array
    {
        return ['id', 'username', 'email'];
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): string
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername(): string
    {
        return $this->hasAttribute('username') ? $this->username : $this->email;
    }

    /**
     * @param string $password
     * @return void
     * @throws \Exception
     */
    public function setPassword(string $password): void
    {
        $this->setAttribute('password', \Yii::$app->getSecurity()->generatePasswordHash($password));
        $this->password_reset_code = \Yii::$app->getSecurity()->generateRandomInteger();
    }

    /**
     * {@inheritdoc}
     */
    public function validatePassword(string $password): bool
    {
        return \Yii::$app->getSecurity()->validatePassword($password, $this->password);
    }

    /**
     * {@inheritdoc}
     */
    public function getPasswordResetCode(): string
    {
        return $this->password_reset_code;
    }

    /**
     * {@inheritdoc}
     */
    public function validatePasswordResetCode(string $password): bool
    {
        return \Yii::$app->getSecurity()->validatePassword($password, $this->password);
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->active;
    }

    /**
     * @return ActiveQuery
     */
    public function getIdentity(): ActiveQuery
    {
        return $this->hasOne(\Yii::$app->user->identityClass, ['id' => 'identity_id']);
    }
}