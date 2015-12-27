<?php
/*
 * Joomla! Editor Button Plugin - Google Translate
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Google Translate Editor Button Plugin
 */
class PlgButtonGoogleTranslate extends JPlugin
{
	/**
	 * Method to display the button
	 *
	 * @param string $name
	 *
	 * @return object
	 */
	public function onDisplay($name)
	{
		// Add the proper JavaScript to this document
		JHtml::_('jquery.framework');
		$document = JFactory::getDocument();
		$document->addScript(JURI::base() . '../media/com_googletranslate/js/editor-xtd.js');
		$document->addStyleSheet(JURI::base() . '../media/com_googletranslate/css/editor-xtd.css');

		// Detect the language
		$lang = null;

		// Construct the button
		$button = new JObject;
		$button->set('modal', false);
		$button->set('onclick', 'javascript:doGoogleTranslate(\'' . $name . '\', \'' . $lang . '\', this);return false;');
		$button->set('class', 'btn');
		$button->set('text', 'Google Translate');
		$button->set('name', 'copy');
		$button->set('link', '#');

		return $button;
	}
}
