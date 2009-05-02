<?php
/*
 * This file is part of the sfErrorHandler plugin
 * (c) 2008-2009 Lee Bolding <lee@php.uk.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * bitcalc is a handy PHP script for determining which error levels are included
 * for a particular error mask, and what it's integer value is 
 *
 * @package    sfErrorHandlerPlugin
 * @author     Lee Bolding <lee@php.uk.com>
 * @version    SVN: $Id$
 */

$raw_expression = "E_ALL ^E_NOTICE";
$expression = E_ALL ^E_NOTICE;


$errors = array(
    1  =>  'E_ERROR', 
    2  =>  'E_WARNING', 
    4  =>  'E_PARSE', 
    8  =>  'E_NOTICE', 
    16 =>  'E_CORE_ERROR',
    32 =>  'E_CORE_WARNING',
    64 =>  'E_COMPILE_ERRR',
    128 => 'E_COMPILE_WARNING',
    256 => 'E_USER_ERROR',
    512 => 'E_USER_WARNING',
    1024 => 'E_USER_NOTICE',
    2048 => 'E_STRICT',
    4096 => 'E_RECOVERABLE_ERROR',
    8192 => 'E_DEPRECATED',
    16384 => 'E_USER_DEPRECATED',
    30719 => 'E_ALL'
);

echo '<pre>';
    echo "<br>LOGGING LEVEL <i>$raw_expression</i> ($expression) :";
    foreach ($errors as $k=>$v) {
        if ($expression & $k) echo "<li>$v($k)</li>";                          // edited to show bit value
    }
    
echo '</pre>';

?>
