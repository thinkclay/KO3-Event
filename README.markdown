# prggmr

prggmr is a event library currently written in PHP, it is nothing more and nothing less.

## Introduction

prggmr is currently being developed as a method of writing applications using an event driven process.

## Limitations & Issues

* Asynchornous events are currently and most likely will stay impossible within the realm PHP.
* Timeout and Interval methods are not realistically possible in PHP.
* Stacks are not maintained within events resulting in untraceable stacks.

This can be demonstrated with the following code.

    namespace prggmr;
    include 'prggmr.php';
    
    Engine::initialize();
    Engine::debug(true);
    
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


## Features

* Robust event subscription
* Stateful events
* Chaining events
* Event Namespaces
* Benchmarking tools
* High performance oriented
* High test coverage
* Debugging & Performance tools

## About the Author

prggmr is created and maintained by Nickolas Whiting, a developer by day at [X Studios](http://www.xstudiosinc.com), and a [engineer by night](http://github.com/nwhitingx).

## License

prggmr is released under the Apache 2 license.