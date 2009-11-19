<?php
/*
 * This file is part of the sfErrorHandler plugin
 * (c) 2008-2009 PHP (UK) Ltd <http://php.uk.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfHardenedRenderingFilter is a replacement for the sfRenderingFilter
 * The sfHardenedRenderingFilter dynamically replaces the sfRenderingFilter for an app
 * when the sfErrorHandler is one of the enabled modules
 *
 * @package    sfErrorHandlerPlugin
 * @author     Lee Bolding <lee@php.uk.com>
 * @version    SVN: $Id$
 */

class sfHardenedRenderingFilter extends sfFilter
{
  /**
   * Log filter activity
   *
   * @param string  $message
   * @param int     $level
   */
  public function log($message, $level = sfLogger::DEBUG)
  {
    sfContext::getInstance()->getLogger()->log('{sfHardenedRenderingFilter} ' . $message, $level);
  }
  
  /**
   * Executes this filter.
   *
   * @param sfFilterChain $filterChain The filter chain.
   *
   * @throws <b>sfInitializeException</b> If an error occurs during view initialization
   * @throws <b>sfViewException</b>       If an error occurs while executing the view
   */
  public function execute($filterChain)
  {
    if ($this->isFirstCall())
    {
      ob_start(array('sfErrorHandler', 'fatal_error_handler'));
      
      // if we're in a prod environment we want E_ALL, but not to fail on E_NOTICE, E_WARNING or E_STRICT
      if (!sfConfig::get('sf_debug'))
      {
        set_error_handler(array('sfErrorHandler', 'error_handler'), sfConfig::get('sf_error_reporting', E_ALL & ~E_NOTICE & ~E_WARNING));
        //$this->log('set error_reporting to : ' . sfConfig::get('sf_error_reporting', E_ALL & ~E_NOTICE));
      } else {
        // get from config or default to E_ALL without E_NOTICE (those E_NOTICEs can get annoying...)
        set_error_handler(array('sfErrorHandler', 'error_handle'), sfConfig::get('sf_error_reporting', E_ALL & ~E_NOTICE));
        //$this->log('set error_reporting to : ' . sfConfig::get('sf_error_reporting', E_ALL & ~E_NOTICE));
      }
    }
    
    try {
      // execute next filter
      $filterChain->execute();
      
    } catch (sfLegacyErrorException $e) {
      ob_clean(); // don't care what's in the buffer, we've got all we need
      throw sfException::createFromException($e);
    } catch (sfStopException $e) {
      // sfStopExceptions are thrown on sfAction::forward() and need to be rethrown to be properly handled
      throw $e;
    } catch (sfSecurityException $e) {
	    // sfSecurityExceptions are thrown by sfGuard and the security system, and need to be rethrown to be properly handled
      throw $e;
    } catch (Exception $e) {
      ob_clean(); // don't care what's in the buffer, we've got all we need
      throw sfException::createFromException($e);
    }
    
    ob_end_clean(); // no need for output buffering from here...
    
    if (sfConfig::get('sf_logging_enabled'))
    {
      $this->context->getEventDispatcher()->notify(new sfEvent($this, 'application.log', array('Render to the client')));
    }

    // hack to rethrow sfForm and|or sfFormField __toString() exceptions (see sfForm and sfFormField)
    if (sfForm::hasToStringException())
    {
      throw sfForm::getToStringException();
    }
    else if (sfFormField::hasToStringException())
    {
      throw sfFormField::getToStringException();
    }
    else if (sfErrorHandler::hasFatalException())
    {
      throw sfException::createFromException(sfErrorHandler::getFatalException());
    }
    
    try {
      // get response object
      $response = $this->context->getResponse();
    
      // send headers + content
      $response->send();
    } catch (sfLegacyErrorException $e) {
      // why does this always get thrown???
      //throw sfException::createFromException($e);
    }
  }
  
}