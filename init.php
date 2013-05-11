<?php defined('SYSPATH') or die('No direct script access.');

Route::set('language', 'language/change/<lang>', array(
        'lang' => '[a-z]{2}'
))->defaults(array(
    'controller' => 'language',
    'action'     => 'change',
));
