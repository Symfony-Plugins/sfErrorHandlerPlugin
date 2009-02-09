<?php
/*
 * This file is part of the sfErrorHandler plugin
 * (c) 2008 Lee Bolding <lee@php.uk.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfErrorHandler is the class that replaces the standard PHP error handler.
 * whenever an error is caught by the error handler, it is rethrown as an
 * sfLegacyErrorException - which extends sfException.
 *
 * @package    sfErrorHandlerPlugin
 * @see        sfLegacyErrorException
 * @author     Lee Bolding <lee@php.uk.com>
 * @version    SVN: $Id$
 */

class sfErrorHandler
{
  private static $instance = null;
  protected static $exception = null;
  private static $filtered_errors = array(E_WARNING, E_NOTICE, E_STRICT);

  private function __construct() {
    set_error_handler(array(__CLASS__, 'error_handler'));
  }

  public function destruct()
  {
    // restore error handler stack 
    restore_error_handler();
  }

  // factory method to return only instance of class
  public static function getInstance()
  {
    if (!self::$instance) {
      self::$instance = new sfErrorHandler();
    }
        
    return self::$instance;
  }

  public static function error_handler($code, $message, $file, $line, $context = null)
  {
    // instantiate a LegacyErrorException ...
    $le = new sfLegacyErrorException($code, $message, $file, $line, $context);
    // now throw the exception
    throw $le;
  }
  
  
  // can't seem to throw an exception here due to always receiving an error :
  // Exception thrown without a stack frame in Unknown on line 0

  public static function fatal_error_handler($buffer)
  {
    $error = error_get_last();
    $output = NULL;
    
    // this should never happen, but if $error isn't an array, return false
    if (!is_array($error)) return false;
    
    // we can't specify a bitmask for error logging to ob_start, so we have
    // to manually filter... (we don't want anything that the error_handler can handle)
    if (in_array($error['type'], self::$filtered_errors) || $error['type'] >= E_USER_ERROR) return false;
    
    if (!sfConfig::get('sf_debug'))
    {
      $files = array();

      // this is a reverse cascade = the bottom-most has precedence
      $files[] = sfConfig::get('sf_plugins_dir').'/sfErrorHandlerPlugin/errors/error500.php';
      $files[] = sfConfig::get('sf_config_dir').'/error_500.php';
      $files[] = sfConfig::get('sf_web_dir').'/errors/error500.php';
      
      // check for app/project specific error page, can only do this if we have a context
      if (sfConfig::get('sf_app_config_dir'))
      {
        $files[] = sfConfig::get('sf_app_config_dir').'/error_500.php';
      }

      foreach ($files as $file)
      {
        if (is_readable($file))
        {
          $output = file_get_contents($file);
        }
      }

    } else {
      foreach ($error as $info => $string)
      // at the moment, pretty basic, but better than nothing, eh?
        $output .= "{$info} : {$string}\n";
        //self::setFatalException(new Exception($error['message'], $error['type']));
        //throw new Exception($error['message'], $error['type']);
    }
    
    return $output;
  }
  
    /**
   * Returns true if we've thrown an exception
   *
   * This is a hack needed because PHP does not allow to throw exceptions after throwing class has been destroyed
   *
   * @return boolean
   */
  static public function hasFatalException()
  {
    return !is_null(self::$exception);
  }

  /**
   * Gets the exception if one was thrown 
   *
   * This is a hack needed because PHP does not allow to throw exceptions after throwing class has been destroyed
   *
   * @return Exception
   */
  static public function getFatalException()
  {
    return self::$exception;
  }

  /**
   * Sets an exception thrown by the fatal_error_handler
   *
   * This is a hack needed because PHP does not allow to throw exceptions after the throwing class has been destroyed
   *
   * @param Exception $e The exception thrown by fatal_error_handler
   */
  static public function setFatalException(Exception $e)
  {
    if (is_null(self::$exception))
    {
      self::$exception = $e;
    }
  }
}

?>
