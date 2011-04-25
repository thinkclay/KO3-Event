# prggmr

lightweight, intuitive event-processing library for PHP 5.3+ applications

## Introduction

prggmr implements a fast event-processing engine for use with developing
event driven applications in PHP 5.3+. It's incredibly simple, is driven by
a robust engine that allows for event chaining, halting, states, namespaces,
asynchronous execution and robust subscriptions.

## Features
* Asynchronous event bubbling
* Robust event subscription
* Stateful events
* Chaining events
* Event Namespaces
* Benchmarking tools
* High performance oriented
* High test coverage
* Needs no configuration

## HelloWorld Example

prggmr uses [anonymous functions](http://www.php.net/Closures) as it's callback mechanism, the alternative method of array('function') and array('class', 'method') callbacks
are not supported.

    include 'prggmr.php';

    // all callbacks are allways passed the current event scope object as the first parameter
    subscribe('helloworld', function($event){
        echo 'Hello World';
    });

    // Bubbling the helloworld event outputting "HelloWorld"
    bubble('helloworld');

Simple no?

## Whats so great about events?

The HelloWorld example can easily be written as a function and achieve the same results.

    function helloworld() {
        echo "HelloWorld";
    }

    helloworld();

This really defeats the purpose of events, and simply put events allow code to react and interact with itself with infinite possibilities.

### So why use events

Here is a real world example.

I recently developed a program which sync's a users Google account into a local database, halfway through development new requirements came in and new information
was required to be synced. The task was easy, I simple refactored production ready code, tested and published. The problem is I had to modify code
which was already in a stable state, possibly introducing new bugs in the code. If I had written the system using events I could have added a new subscriber to the sync
event and introduced the new information without modifing any of the existing codebase ... saving time and headaches.

## Benchmark

This benchmark was conducted on

    Ubuntu 10.01
    AMD Athlon II X3 435
    6GB Memory

The benchmark tested subscribing to 1,250 events with 5 subscribers per event, this number was chosen as a high estimate of the
typical number of events subscribed and bubbled within a large application, with all benchmarks this is only a reflection of possible performance and it
will vary dependent on system specifications.



As the results show

## Limitations & Issues

* Timeout and Interval methods are not realistically possible in PHP ... yet.
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

* Unfortunately Timeout and Internal methods do not currently have a reliable method of implementation without halting execution in some manner wether that is sleeping, looping or line counting.
* Regarding the stacktrace, the current method in planning is to attach the event to a stacktrace on each fire which would allow a backwards rebuild.

## About the Author

prggmr is created and maintained by Nickolas Whiting, a developer by day at [X Studios](http://www.xstudiosinc.com), and a [engineer by night](http://github.com/nwhitingx).

## License

prggmr is released under the Apache 2 license.