<?php
/**
 * @link https://github.com/frammirabile/yii2
 * @copyright Copyright (c) 2018 Francesco Ammirabile <frammirabile@gmail.com>
 */

namespace yii\rest;

use Ramsey\Uuid\Uuid;
use yii\behaviors\{ExpirableBehavior, TimestampBehavior};
use yii\db\ActiveQuery;
use yii\helpers\{Json, StringHelper, Url};

/**
 * Token model
 *
 * @property string $id
 * @property int $user_id
 * @property string $secret
 * @property string $refresh
 * @property int $created_at
 * @property int $expires_at
 *
 * @property-read ActiveUser $user
 *
 * @author Francesco Ammirabile <frammirabile@gmail.com>
 * @since 1.0
 */
class Token extends ActiveRecord implements TokenInterface
{
    /**
     * @var int|string
     */
    protected $expiration;

    /**
     * {@inheritdoc}
     */
    public static function findByIdentity(IdentityInterface $identity): ?TokenInterface
    {
        return ($token = static::findOne(['user_id' => $identity->getId()])) !== null && $token->isValid() ? $token : null;
    }

    /**
     * {@inheritdoc}
     */
    public static function findByKey(string $key): ?TokenInterface
    {
        try {
            if (count($token = explode('.', $key)) == 3
                && ($json = Json::decode(base64_decode($token[1]), false)) !== null
                && !empty($json->jti) && ($token = static::findOne(['id' => StringHelper::uuid2Binary($json->jti)])) !== null
                && $json == \Firebase\JWT\JWT::decode($key, $token->secret, ['HS256']))
                return $token;
        } catch (\Exception $e) {
            \Yii::error($e->getMessage(), __METHOD__);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public static function findByRefresh(string $refresh): ?TokenInterface
    {
        return ($token = static::findOne(['refresh' => $refresh])) !== null && $token->isValid() ? $token : null;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function __toString(): string
    {
        return \Firebase\JWT\JWT::encode($this->claims(), $this->secret);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false
            ],
            [
                'class' => ExpirableBehavior::class,
                'expiration' => $this->expiration
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            ['user_id', 'required'],
            [['id', 'secret', 'refresh'], 'string'],
            [['user_id', 'created_at', 'expires_at'], 'integer'],
            ['id', 'unique'],
            ['user_id', 'unique'],
            ['secret', 'unique'],
            ['refresh', 'unique'],
            ['user_id', 'exist', 'targetRelation' => 'user']
        ];
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert) || !$insert)
            return false;

        $this->id = str_pad(mb_convert_encoding(StringHelper::uuid2Binary(Uuid::uuid4()->toString()), 'UTF-8'), 16);
        $this->secret = \Yii::$app->security->generateRandomString();
        $this->refresh = \Yii::$app->security->generateRandomString();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function fields(): array
    {
        return [
            'access_token' => function() { return $this->__toString(); },
            'token_type' => function() { return 'bearer'; },
            'expires_in' => function() { return $this->expires_at - $this->created_at; },
            'refresh_token'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpiresAt(): ?int
    {
        return $this->expires_at;
    }

    /**
     * @return ActiveQuery
     */
    public function getUser(): ActiveQuery
    {
        return $this->hasOne(ActiveUser::class, ['id' => 'user_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(): bool
    {
        return $this->expires_at === null || $this->expires_at > time();
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function claims(): array
    {
        return [
            'jti' => StringHelper::binaryToUuid($this->id),
            'iss' => Url::domain(),
            'sub' => $this->user_id,
            'iat' => $this->created_at,
            'exp' => $this->expires_at
        ];
    }
}