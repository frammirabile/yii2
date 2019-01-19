<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2018 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\validators\PasswordValidator;

/**
 * Rest user model
 *
 * @property-read int $id
 * @property string $username
 * @property-read string $reset_password
 * @property-write bool $active
 * @property-read bool $isActive
 * @property-read int $created_at
 * @property-read int $updated_at
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class ActiveUser extends ActiveRecord implements IdentityInterface
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
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'active' => true]);
    }

    /**
     * {@inheritdoc}
     * @param TokenInterface $token
     */
    public static function findIdentityByAccessToken($token, $type = null): ?IdentityInterface
    {
        return static::findOne(['id' => $token->getUserId(), 'active' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findByUsername(string $username): ?IdentityInterface
    {
        return static::findOne(['username' => $username, 'active' => true]);
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
        return $this->primaryKey;
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
        $this->setAttribute('password', \Yii::$app->security->generatePasswordHash($password));
        $this->reset_password = \Yii::$app->security->generateRandomInteger();
    }

    /**
     * {@inheritdoc}
     */
    public function validatePassword(string $password): bool
    {
        return \Yii::$app->security->validatePassword($password, $this->getAttribute('password'));
    }

    /**
     * {@inheritdoc}
     */
    public function getResetPassword(): string
    {
        return $this->reset_password;
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
     * {@inheritdoc}
     * @throws NotSupportedException
     */
    public function getAuthKey(): string
    {
        throw new NotSupportedException;
    }

    /**
     * {@inheritdoc}
     * @throws NotSupportedException
     */
    public function validateAuthKey($authKey): bool
    {
        throw new NotSupportedException;
    }
}