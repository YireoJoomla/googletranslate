<?php
/**
 * Joomla! component GoogleTranslate
 *
 * @author    Yireo
 * @package   GoogleTranslate
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link      http://www.yireo.com/
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the base controller
require_once JPATH_COMPONENT . '/controller.php';
$controller = JControllerLegacy::getInstance('googletranslate');

// Perform the Request task
$controller->execute(JFactory::getApplication()->input->getCmd('task'));
$controller->redirect();
