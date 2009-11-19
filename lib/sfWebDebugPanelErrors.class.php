<?php
/*
 * This file is part of the sfErrorHandler plugin
 * (c) 2008-2009 PHP (UK) Ltd <http://php.uk.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebDebugPanelErrors
 *
 * @package    sfErrorHandlerPlugin
 * @author     Martin Schnabel <mcnilz@gmail.com>
 * @version    SVN: $Id$
 */
class sfWebDebugPanelErrors extends sfWebDebugPanel
{
  public function getTitle()
  {
    $errors = sfErrorHandler::getErrors();
    if (is_array($errors))
    {
      $count = count($errors);
      if ($count === 1) return '1 error';

      return count($errors) . ' errors';
    } else {
      return 'No errors';
    }
  }

  public function getPanelTitle()
  {
    return 'PHP Errors';
  }

  public function getPanelContent()
  {
    $errors = sfErrorHandler::getErrors();
    if ($errors)
    {
      $html = '';
      $html .= '<ul>';
      foreach ($errors as $error)
        $html .= sprintf("<li><b>%s:%s code %s</b><br/>%s</li>",
          htmlentities($error['file']), $error['line'], $error['code'], htmlentities($error['message']));

      $html .= '</ul>';

      return $html;
    }

    return null;
  }
}

?>