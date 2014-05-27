<?php
/*
 * Joomla! component GoogleTranslate
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * GoogleTranslate Structure
 */
class HelperAbstract
{
    /**
     * Structural data of this component
     */
    static public function getStructure()
    {
        return array(
            'title' => 'GoogleTranslate',
            'menu' => array(
                'home' => 'Home',
            ),
            'views' => array(
                'home' => 'Home',
            ),
            'obsolete_files' => array(
            ),
        );
    }
}
