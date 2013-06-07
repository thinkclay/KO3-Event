# Event Module for Kohana 3.x

Lightweight, intuitive event-processing library for PHP 5.3+ applications built using the [prggmr](https://github.com/nwhitingx/prggmr) event framework by [Nickolas Whiting](http://github.com/nwhitingx)

## Introduction

The Kohana Event Module implements a fast event-processing engine for use with developing
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

## Installation and Getting Started

Download or clone the event module from github and install to your module path

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

## License

This module is released under an [MIT opensource license](http://opensource.org/licenses/MIT)

----
## The MIT License (MIT)

### Copyright (c) 2013 Clay McIlrath

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
