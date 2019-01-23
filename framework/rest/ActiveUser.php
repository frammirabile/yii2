<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2019 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

use yii\behaviors\TimestampBehavior;

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
 * @property IdentityInterface $identity
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class ActiveUser extends ActiveRecord implements UserInterface
{
    /**
     * @var string the id attribute
     */
    protected static $idAttribute = 'id';

    /**
     * @var string the username attribute
     */
    protected static $usernameAttribute = 'username';

    /**
     * @var IdentityInterface
     */
    private $_identity;

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
    public static function findById(int $id): ?UserInterface
    {
        return ($user = self::findOne([static::$idAttribute => $id])) !== null && $user->getIsActive() ? $user : null;
    }

    /**
     * {@inheritdoc}
     */
    public static function findByUsername(string $username): ?UserInterface
    {
        return ($user = self::findOne([static::$usernameAttribute => $username])) !== null && $user->getIsActive() ? $user : null;
    }

    /**
     * {@inheritdoc}
     */
    public static function findByAccessToken(TokenInterface $token, $type = null): ?UserInterface
    {
        return ($user = static::findOne([static::$idAttribute => $token->getUserId()])) !== null && $user->getIsActive() ? $user : null;
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [TimestampBehavior::class];
    }

    /**
     * {@inheritdoc}
     *
     * @tbd ottimizzare regole username, password e identity
     */
    public function rules(): array
    {
        return [
            [[static::$usernameAttribute, 'password', 'identity'], 'required'],
            [[static::$usernameAttribute], 'string'],
            ['password', 'string', 'when' => function() {
                return $this->isAttributeChanged('password');
            }],
            ['active', 'default', 'value' => false],
            ['active', 'boolean']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios(): array
    {
        return [
            self::SCENARIO_CREATE => [static::$usernameAttribute, 'password'],
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
        return ['id' => static::$idAttribute, 'username' => static::$usernameAttribute, 'identity'];
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): int
    {
        return $this->{static::$idAttribute};
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername(): string
    {
        return $this->hasAttribute(static::$usernameAttribute) ? $this->{static::$usernameAttribute} : $this->getIdentity()->getEmail();
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
     * {@inheritdoc}
     */
    public function getIsActive(): bool
    {
        return $this->active;
    }

    /**
     * {@inheritdoc}
     */
    public function setIsActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @return IdentityInterface|null
     */
    public function getIdentity(): ?IdentityInterface
    {
        if ($this->identity_id === null)
            return null;

        /** @var IdentityInterface $identityClass */
        $identityClass = \Yii::$app->user->identityClass;

        return $identityClass::findById($this->identity_id);
    }

    /**
     * {@inheritdoc}
     */
    public function setIdentity(IdentityInterface $identity): void
    {
        $this->_identity = $identity;
    }

    /**
     * @return TokenInterface|null
     */
    public function getToken(): ?TokenInterface
    {
        /** @var TokenInterface $tokenClass */
        $tokenClass = \Yii::$app->user->tokenClass;

        return $tokenClass::findByUserId($this->getId());
    }
}