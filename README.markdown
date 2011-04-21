# prggmr

prggmr is a event library currently written in PHP, it is nothing more and nothing less.

## Introduction

prggmr is currently being developed as a method of writing applications using an event driven process.

## Limitations & Issues

* Timeout and Interval methods are not realistically possible in PHP.
* Stacks are not maintained within events resulting in untraceable stacks.

This can be demonstrated with the following code.

    include 'prggmr.php';

    \prggmr\Engine::initialize();
    \prggmr\Engine::debug(true);

    subscribe('event1', function(){
            bubble('event2');
    });

    subscribe('event2', function(){
        bubble('event3');
    });

    subscribe('event3', function(){
        throw new \Exception("I'm an error");
    });

    bubble('event1');

### Results

    Message [Event (event1) Subscriber "dnpsuvxz" execution failed due to exception "LogicException" with message "Event (event2) Subscriber "fimnpsvx" execution failed due to exception "LogicException" with message "Event (event3) Subscriber "dfhjmpqs" execution failed due to exception "Exception" with message "I'm an error"""]
    File [prggmr.php]
    Line [703]
    Trace Route
    {#0} prggmr.php(1688): Unknown::prggmr\bubble (event1)
    {#1} prggmr.php(1644): prggmr\Engine::bubble (event1, Array(), Array())

### Expected

    Message [Event (event1) Subscriber "dnpsuvxz" execution failed due to exception "LogicException" with message "Event (event2) Subscriber "fimnpsvx" execution failed due to exception "LogicException" with message "Event (event3) Subscriber "dfhjmpqs" execution failed due to exception "Exception" with message "I'm an error"""]
    File [~/prggmr.php]
    Line [703]
    Trace Route
    {#0} prggmr.php(1688): Unknown::prggmr\bubble (event1)
    {#1} prggmr.php(1644): prggmr\Engine::bubble (event1, Array(), Array())
    {#2} prggmr.php(1644): prggmr\Engine::bubble (event2, Array(), Array())
    {#3} prggmr.php(1644): prggmr\Engine::bubble (event3, Array(), Array())

### Solutions?

* Unfortunately Timeout and Internal methods do not currently have a reliable method of implementation without halting execution
* The current method in planning is to manually construct a stacktrace on each event fire which would allow a backwards rebuild on exceptions.

## Features
* Asynchronous event bubbling
* Robust event subscription
* Stateful events
* Chaining events
* Event Namespaces
* Benchmarking tools
* High performance oriented
* High test coverage

## About the Author

prggmr is created and maintained by Nickolas Whiting, a developer by day at [X Studios](http://www.xstudiosinc.com), and a [engineer by night](http://github.com/nwhitingx).

## License

prggmr is released under the Apache 2 license.