<?php

namespace Zheltikov\Pipeline;

use Throwable;

use function Zheltikov\Invariant\invariant;

/**
 * Class Pipeline
 * @package Zheltikov\Pipeline
 */
class Pipeline
{
    /**
     * @var mixed
     */
    private $reason = null;

    /**
     * @var mixed
     */
    private $value = null;

    /**
     * @var bool
     */
    private $is_resolved = false;

    /**
     * @var bool
     */
    private $is_rejected = false;

    /**
     * Pipeline constructor.
     * @param callable $executor
     */
    public function __construct(callable $executor)
    {
        /**
         * @param mixed $value
         */
        $resolve = function ($value = null) {
            $this->value = $value;
            $this->is_resolved = true;
        };

        /**
         * @param mixed $reason
         */
        $reject = function ($reason = null) {
            $this->reason = $reason;
            $this->is_rejected = true;
        };

        $executor($resolve, $reject);
    }

    /**
     * @param callable $onRejected
     * @return $this
     */
    public function catch(callable $onRejected): self
    {
        return $this->then(null, $onRejected);
    }

    /**
     * @param callable|null $onFulfilled
     * @param callable|null $onRejected
     * @return $this
     */
    public function then(?callable $onFulfilled, ?callable $onRejected = null): self
    {
        if ($this->is_rejected == true) {
            if ($onRejected !== null) {
                $onRejected($this->reason);
                $this->is_rejected = false;
            }

            return $this;
        }

        if ($onFulfilled !== null) {
            try {
                $this->value = $onFulfilled($this->value);
                $this->is_resolved = true;
                $this->is_rejected = false;
            } catch (Throwable $reason) {
                $this->reason = $reason;
                $this->is_rejected = true;
                $this->is_resolved = false;

                if ($onRejected !== null) {
                    $onRejected($this->reason);
                    $this->is_rejected = false;

                    return $this;
                }
            }
        }

        if ($onRejected !== null) {
            if ($this->is_rejected == true) {
                $onRejected($this->reason);
                $this->is_rejected = false;

                return $this;
            }
        }

        return $this;
    }

    /**
     * @param callable $onFinally
     * @return $this
     */
    public function finally(callable $onFinally): self
    {
        return $this->then($onFinally, $onFinally);
    }

    /**
     * @return bool
     */
    public function isResolved(): bool
    {
        return $this->is_resolved;
    }

    /**
     * @return bool
     */
    public function isRejected(): bool
    {
        return $this->is_rejected;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function getReason()
    {
        return $this->reason;
    }

    // -------------------------------------------------------------------------

    /**
     * @param iterable $iterable
     * @return static
     * @throws \Zheltikov\Exceptions\InvariantException
     */
    public static function all(iterable $iterable): self
    {
        $value = [];

        /** @var static $pipeline */
        foreach ($iterable as $pipeline) {
            invariant(
                $pipeline instanceof static,
                'Iterable item must be of type %s',
                static::class
            );

            if ($pipeline->isResolved()) {
                $value[] = $pipeline->getValue();
                continue;
            }

            if ($pipeline->isRejected()) {
                return static::reject($pipeline->getReason());
            }
        }

        return static::resolve($value);
    }

    /**
     * @param iterable $iterable
     * @return static
     * @throws \Zheltikov\Exceptions\InvariantException
     */
    public static function allSettled(iterable $iterable): self
    {
        $value = [];

        /** @var static $pipeline */
        foreach ($iterable as $pipeline) {
            invariant(
                $pipeline instanceof static,
                'Iterable item must be of type %s',
                static::class
            );

            if ($pipeline->isResolved()) {
                $value[] = [
                    'status' => 'resolved',
                    'value' => $pipeline->getValue(),
                ];
                continue;
            }

            if ($pipeline->isRejected()) {
                $value[] = [
                    'status' => 'rejected',
                    'reason' => $pipeline->getReason(),
                ];
            }
        }

        return static::resolve($value);
    }

    /**
     * @param iterable $iterable
     * @return static
     * @throws \Zheltikov\Exceptions\InvariantException
     */
    public static function any(iterable $iterable): self
    {
        $reason = [];

        /** @var static $pipeline */
        foreach ($iterable as $pipeline) {
            invariant(
                $pipeline instanceof static,
                'Iterable item must be of type %s',
                static::class
            );

            if ($pipeline->isResolved()) {
                return static::resolve($pipeline->getValue());
            }

            if ($pipeline->isRejected()) {
                $reason[] = $pipeline->getReason();
            }
        }

        return static::reject($reason);
    }

    /**
     * @param mixed $reason
     * @return static
     */
    public static function reject($reason): self
    {
        return new static(
            function (callable $resolve, callable $reject) use ($reason) {
                $reject($reason);
            }
        );
    }

    /**
     * @param mixed $value
     * @return static
     */
    public static function resolve($value): self
    {
        return new static(
            function (callable $resolve) use ($value) {
                $resolve($value);
            }
        );
    }
}
