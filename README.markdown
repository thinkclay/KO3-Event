# prggmr

lightweight, intuitive event-processing library for PHP 5.3+ applications

## Introduction

prggmr implements a fast event-processing engine for use with developing
event driven applications in PHP 5.3+. It's incredibly simple, is driven by
a robust engine that allows for event chaining, halting, states,
asynchronous execution and robust subscriptions.

## Features

* Asynchronous event firing
* Robust event subscription
* Stateful events
* Chaining events
* High performance oriented
* High test coverage
* ZERO configuration
* Priority based subscription

## Installation

prggmr is designed to be a wolf in sheeps clothing, with a very minimalistic easy-to-use api with a robust complex engine backing it, the installation of prggmr currently requires inclusion of a single file and nothing else.

    require 'prggmr/lib/prggmr.php';

### Installing in the include path (Unix)

The recommended method of installation is into your PHP include path allowing you to have a single local copy avaliable system wide. The following steps will install prggmr to your include path.

Note this assumes an include path of /usr/local/lib/php and you have root or sudo access.

If unsure of your include path you can retrieve it by running the command

    php -r "echo get_include_path()\n";

The following will install prggmr to your include path, again replace if yours differs.

    cd /usr/local/lib/php
    git clone git@github.com:nwhitingx/prggmr.git

prggmr can now be included using 

    require 'prggmr/lib/prggmr.php';

## HelloWorld Example

### Code

    subscribe('my_event', function($event){
        echo 'HelloWorld';
    });

    fire('my_event');

### Results

    HelloWorld

## Limitations & Issues

* Timeout and Interval methods are not realistically possible in PHP ... although written as an extension this would be possible.
* Stacks are not maintained within events resulting in untraceable stacks.

## Untraceable stack demostration

    subscribe('exception', function(){
        fire('exception_2');
    });

    subscribe('exception_2', function(){
        fire('exception_3');
    });

    subscribe('exception_3', function(){
        throw new Exception('I have no trace ...');
    });

### Result

    RuntimeException: I have no trace ... in /home/nick/Apps/Prggmr/lib/subscription.php on line 94

    Call Stack:
        0.0002     328852   1. {main}() /home/nick/Apps/Prggmr/test.php:0
        0.0023     504908   2. fire() /home/nick/Apps/Prggmr/test.php:16
        0.0023     504964   3. prggmr\Engine->fire() /home/nick/Apps/Prggmr/lib/api.php:66
        0.0024     505692   4. prggmr\Subscription->fire() /home/nick/Apps/Prggmr/lib/engine.php:240


### Expected

    RuntimeException: I have no trace ... in /home/nick/Apps/Prggmr/lib/subscription.php on line 94

    Call Stack:
        0.0002     328852   1. {main}() /home/nick/Apps/Prggmr/test.php:0
        0.0023     504908   2. fire() /home/nick/Apps/Prggmr/test.php:16
        0.0023     504964   3. prggmr\Engine->fire() /home/nick/Apps/Prggmr/lib/api.php:66
        0.0023     504908   4. fire() /home/nick/Apps/Prggmr/test.php:5
        0.0023     504964   5. prggmr\Engine->fire() /home/nick/Apps/Prggmr/lib/api.php:66
        0.0023     504908   6. fire() /home/nick/Apps/Prggmr/test.php:9
        0.0023     504964   7. prggmr\Engine->fire() /home/nick/Apps/Prggmr/lib/api.php:66


### Solution

The method which is in planning is to attach the event to a stacktrace on each fire which would rebuilt itself in reverse.

## About the Author

prggmr is created and maintained by Nickolas Whiting, a developer by day at [X Studios](http://www.xstudiosinc.com), and a [engineer by night](http://github.com/nwhitingx).

## License

prggmr is released under the Apache 2 license.