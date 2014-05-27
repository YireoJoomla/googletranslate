<?php
/*
 * Joomla! Editor Button Plugin - Google Translate
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

/**
 * Google Translate Editor Button Plugin
 */
class plgButtonGoogleTranslate extends JPlugin
{
    /**
     * Method to display the button
     *
     * @param string $name
     */
    public function onDisplay( $name )
    {
        // Load the parameters
        $params = JComponentHelper::getParams('com_googletranslate');

        // Add the proper JavaScript to this document
        $this->jQuery();
        $document = JFactory::getDocument();
        $document->addScript(JURI::base().'../media/com_googletranslate/js/editor-xtd.js');
        $document->addStyleSheet(JURI::base().'../media/com_googletranslate/css/editor-xtd.css');

        // Detect the language
        $lang = null;

        // Construct the button
		$button = new JObject();
		$button->set('modal', false);
		$button->set('onclick', 'javascript:doGoogleTranslate(\''.$name.'\', \''.$lang.'\', this);return false;');
        $button->set('class', 'btn');
		$button->set('text', 'Google Translate');
		$button->set('name', 'copy');
		$button->set('link', '#');

		return $button;
    }

    /*
     * Add in jQuery
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return null
     */
    protected function jQuery()
    {
        // Load by parameter
        $params = JComponentHelper::getParams('com_googletranslate');
        if($params->get('load_jquery', 1) == 0) return false;

        // Load jQuery using the framework (Joomla! 3.0 and higher)
        if($this->isJoomla25() == false) {
            return JHtml::_('jquery.framework');
        }

        // Check if jQuery is loaded already
        $application = JFactory::getApplication();
        if (method_exists($application, 'get') && $application->get('jquery') == true) {
            return;
        }

        // Do not load this for specific extensions
        if(JRequest::getCmd('option') == 'com_virtuemart') return false;

        // Add the script
        $document = JFactory::getDocument();
        $document->addScript(JURI::base().'../media/com_googletranslate/js/jquery.js');

        // Set the flag that jQuery has been loaded
        if(method_exists($application, 'set')) $application->set('jquery', true);

        return true;
    }

    /*
     * Helper-method to check whether the current Joomla! version equals some value
     *
     * @param $version string|array
     * @return bool
     */
    public function isJoomla($version)
    {
        JLoader::import( 'joomla.version' );
        $jversion = new JVersion();
        if (!is_array($version)) $version = array($version);
        foreach($version as $v) {
            if (version_compare( $jversion->RELEASE, $v, 'eq')) {
                return true;
            }
        }
        return false;
    }

    /*
     * Helper-method to check whether the current Joomla! version is 2.5
     *
     * @param null
     * @return bool
     */
    public function isJoomla25()
    {
        if(self::isJoomla('2.5') || self::isJoomla('1.7') || self::isJoomla('1.6')) {
            return true;
        }
        return false;
    }
}
