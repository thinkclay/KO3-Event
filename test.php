<?php
require 'lib/prggmr.php';

subscribe('exception', function(){
    fire('exception_2');
});

subscribe('exception_2', function(){
    fire('exception_3');
});

subscribe('exception_3', function(){
    throw new Exception('I have no trace ...');
});

fire('exception');
