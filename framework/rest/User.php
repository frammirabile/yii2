<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2018 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

use yii\base\InvalidConfigException;
use yii\helpers\StringHelper;

/**
 * Rest user
 *
 * @property-read IdentityInterface|null $identity
 * @property-read string $username
 * @property-read string $email
 * @property-read bool $isAdmin
 * @property-read TokenInterface|null $token
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class User extends \yii\web\User
{
    /**
     * @var string the model class name
     */
    public $modelClass;

    /**
     * @var string the class name of the [[token]] object
     */
    public $tokenClass;

    /**
     * @var UserInterface
     */
    protected $_user;

    /**
     * @var IdentityInterface|false
     */
    protected $_identity = false;

    /**
     * @var TokenInterface
     */
    protected $_token;

    /**
     * {@inheritdoc}
     *
     * @return void
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->enableSession = false;
        $this->loginUrl = null;

        if (!in_array(IdentityInterface::class, class_implements($this->identityClass)))
            throw new InvalidConfigException(\Yii::t('api', 'Identity class must implement IdentityInterface'));

        if (!in_array(TokenInterface::class, class_implements($this->tokenClass)))
            throw new InvalidConfigException(\Yii::t('api', 'Token class must implement TokenInterface'));
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return StringHelper::basename($this->identityClass).' '.$this->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity($autoRenew = true)
    {
        return $this->_identity;
    }

    /**
     * @param string $username
     * @param string $password
     * @return IdentityInterface
     */
    public function authenticate(string $username, string $password): bool
    {
        /** @var IdentityInterface $identityClass */
        $identityClass = $this->identityClass;

        if (($identity = $identityClass::findByUsername($username)) === null || !$identity->validatePassword($password))
            return false;

        /** @var TokenInterface $tokenClass */
        $tokenClass = $this->tokenClass;
        $this->_token = $tokenClass::findByUser($identity);

        return $this->login($identity);
    }

    /**
     * @param string $token
     * @return IdentityInterface
     */
    public function authenticateByRefreshToken(string $token): bool
    {
        /** @var TokenInterface $tokenClass */
        $tokenClass = $this->tokenClass;

        /** @var IdentityInterface $identityClass */
        $identityClass = $this->identityClass;

        return ($this->_token = $tokenClass::findByRefresh($token)) !== null && ($identity = $identityClass::findIdentityByAccessToken($this->_token)) !== null && $this->login($identity);
    }

    /**
     * {@inheritdoc}
     */
    public function loginByAccessToken($token, $type = null): ?IdentityInterface
    {
        /** @var TokenInterface $tokenClass */
        $tokenClass = $this->tokenClass;

        /** @var IdentityInterface $identityClass */
        $identityClass = $this->identityClass;

        return ($this->_token = $tokenClass::findByKey($token)) !== null && ($identity = $identityClass::findIdentityByAccessToken($this->_token, $type)) !== null && $this->login($identity) ? $identity : null;
    }

    /**
     * @return bool
     */
    public function refreshToken(): bool
    {
        /** @var Token $tokenClass */
        $tokenClass = $this->tokenClass;
        $tokenClass::deleteAll(['user_id' => $this->_identity->getId()]); #tbd usare interfaccia e id in variabile

        try {
            /** @var Token $token */
            $token = new $this->tokenClass(['attributes' => ['user_id' => $this->_identity->getId()]]);
            $token->save();
        } catch (\Throwable $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            return false;
        }

        $this->_token = &$token;
        $token->trigger(ActiveRecord::EVENT_AFTER_REFRESH);

        return true;
    }

    /**
     * Returns the username
     *
     * @return string|null the username
     */
    public function getUsername(): ?string
    {
        return $this->_user->getUsername();
    }

    /**
     * Returns the user email
     *
     * @return string|null the user email
     */
    public function getEmail(): ?string
    {
        return $this->_identity !== null ? $this->_identity->getEmail() : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->_user->getId();
    }

    /**
     * Returns the identity id
     *
     * @return int
     */
    public function getIdentityId(): int
    {
        return $this->_identity->getId() ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getIsGuest(): bool
    {
        return $this->_identity === false;
    }

    /**
     * @return TokenInterface|null
     */
    public function getToken(): ?TokenInterface
    {
        return $this->_token;
    }
}