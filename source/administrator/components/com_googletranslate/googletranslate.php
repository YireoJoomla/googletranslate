<?php
/**
 * Joomla! component GoogleTranslate
 *
 * @author Yireo
 * @package GoogleTranslate
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com/
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Require the base controller
require_once (JPATH_COMPONENT.'/controller.php');
$controller	= new GoogleTranslateController( );

// Perform the Request task
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();

