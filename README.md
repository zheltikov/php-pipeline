# php-pipeline

A library providing utility functions for dealing with pipeline operations.

## Installation

To install this library use Composer:

```shell
$ composer require zheltikov/php-pipeline
```

## Usage

Below is a simple example that explains how to use this library at its basic level:

```php
<?php

require_once(__DIR__ . '/../vendor/autoload.php');

use function Zheltikov\Pipeline\pipeline;

$work = pipeline(function ($resolve, $reject) {
        // do some interesting work...
        // for illustration purposes, just randomize the result
        rand(0, 1)
            ? $resolve(123) // pass the result value of the work
            : $reject(new \Exception('Some error'));
    })
    // on each step of the pipeline, do something with the result of the
    // previous step and pass it forward
    ->then(fn($x) => $x ** 2) 
    ->then(fn($x) => $x + 1)
    ->then(fn($x) => $x / 123)
    ->then(fn($x) => sqrt($x))
    ->catch(function ($reason) {
        // do something when an error occurs in the pipeline
        // for example, you can log it, for later analysis
        \Logger::log($reason);
    })
    ->finally(function () {
        // do something regardless if the work succeeded of failed
        // for example you can close some file or connection
        fclose($some_file);
        mysqli_close($connection);
    });

// You are given some methods to gather information about the pipeline
if ($work->isResolved()) {
    // The pipeline succeeded
    $result = $work->getValue();
}

if ($work->isRejected()) {
    // The pipeline failed
    $reason = $work->getReason();
}

// We can even add pipeline steps afterwards...!
$work->then(fn($x) => $x * $x)
    // ...
    ->catch('var_dump')
    ->finally(function () { /* ... */ });

```

As you can see, the interface provided by this library is similar to the one of
the [`Promise`](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Promise) object in
JavaScript, but unfortunately, it doesn't provide asynchronous support... Guess why! :)

You can always hunt for more powerful ways of using this library in its source code.
