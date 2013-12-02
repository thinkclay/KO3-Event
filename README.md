# Event Module for Kohana 3.x
Lightweight, intuitive event-processing library for PHP 5.4+ ([see the 5.3 branch for older compatability](https://github.com/thinkclay/KO3-Event/tree/php-5.3)) applications running on Kohana 3.x (tested on 3.2 and 3.3)

The Kohana Event Module implements a fast event-processing engine for use with developing event driven applications in PHP 5.3+. It's incredibly simple, driven by a robust engine that allows for event chaining, halting, states, asynchronous execution and robust subscriptions.

## Features
* Asynchronous event firing
* Robust event subscription
* Stateful events
* Chaining events
* High performance oriented
* High test coverage
* ZERO configuration
* Priority based subscription

### Writing Code

	// For a test, drop this into any method in any controller or class
	// This attaches your callback to a custom event which can be a string, function, etc
	Event::instance()->listen(
		'EVENT_ECHO_TEST',

		function ($event) {
			echo '<strong>Event Fired!!!</strong><br />';
			var_dump($event);
		}
	);

	// Then drop this into another function to create the hook for the event listener
    Event::instance()->fire('EVENT_ECHO_TEST');


## Limitations & Issues

* Timeout and Interval methods are not realistically possible in PHP ... although written as an extension this would be possible.
* Stacks are not maintained within events resulting in untraceable stacks.
* Since this is based on the prggmr framework, it inherits the same limitations and issues, though we plan to extend and resolve those over time.


----
This module is released under an [MIT opensource license](http://opensource.org/licenses/MIT)
