<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * The engine is responsible for ensuring all data passed into the system
 * meets the specifications allowing it to be intrepreted by each module.
 *
 * The engine still supports the main methods of bubbling and subscripting
 * only it now performs almost no logic other than type checking.
 */
class Event extends Kohana_Event
{
}