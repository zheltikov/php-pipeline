<?php

namespace Zheltikov\Pipeline;

/**
 * @param callable $executor
 * @return \Zheltikov\Pipeline\Pipeline
 */
function pipeline(callable $executor): Pipeline
{
    return new Pipeline($executor);
}

/**
 * @param iterable $iterable
 * @return \Zheltikov\Pipeline\Pipeline
 * @throws \Zheltikov\Exceptions\InvariantException
 */
function all(iterable $iterable): Pipeline
{
    return Pipeline::all($iterable);
}

/**
 * @param iterable $iterable
 * @return \Zheltikov\Pipeline\Pipeline
 * @throws \Zheltikov\Exceptions\InvariantException
 */
function allSettled(iterable $iterable): Pipeline
{
    return Pipeline::allSettled($iterable);
}

/**
 * @param iterable $iterable
 * @return \Zheltikov\Pipeline\Pipeline
 * @throws \Zheltikov\Exceptions\InvariantException
 */
function any(iterable $iterable): Pipeline
{
    return Pipeline::any($iterable);
}

/**
 * @param $reason
 * @return \Zheltikov\Pipeline\Pipeline
 */
function reject($reason): Pipeline
{
    return Pipeline::reject($reason);
}

/**
 * @param $value
 * @return \Zheltikov\Pipeline\Pipeline
 */
function resolve($value): Pipeline
{
    return Pipeline::resolve($value);
}
