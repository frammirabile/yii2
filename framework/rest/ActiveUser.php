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
    public static function findByUsername(string $username): ?UserInterface
    {
        return self::findOne(['username' => $username, 'isActive' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findByAccessToken(TokenInterface $token, $type = null): ?UserInterface
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
     *
     * @tbd ottimizzare regole username e password
     */
    public function rules(): array
    {
        return [
            [['username', 'password', 'identity_id'], 'required'],
            [['username'], 'string'],
            ['password', 'string', 'when' => function() {
                return $this->isAttributeChanged('password');
            }],
            ['identity_id', 'integer'],
            ['active', 'default', 'value' => false],
            ['active', 'boolean'],
            ['identity_id', 'unique']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios(): array
    {
        return [
            self::SCENARIO_CREATE => ['username', 'password', 'identity_id'],
            self::SCENARIO_UPDATE => ['password', 'active']
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
        return ['id', 'username'];
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername(): string
    {
        return $this->hasAttribute('username') ? $this->username : $this->identity->getEmail();
    }

    /**
     * @param string $password
     * @return void
     * @throws \Exception
     */
    public function setPassword(string $password): void
    {
        $this->password = \Yii::$app->getSecurity()->generatePasswordHash($password);
    }

    /**
     * {@inheritdoc}
     */
    public function validatePassword(string $password): bool
    {
        return \Yii::$app->security->validatePassword($password, $this->password);
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
    public function validatePasswordResetCode(string $passordResetCode): bool
    {
        return \Yii::$app->security->validatePassword($passordResetCode, $this->password_reset_code);
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