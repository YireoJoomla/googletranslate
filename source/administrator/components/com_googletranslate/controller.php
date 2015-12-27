<?php
/**
 * Joomla! component GoogleTranslate
 *
 * @author    Yireo
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link      http://www.yireo.com/
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Include the loader
require_once JPATH_COMPONENT . '/lib/loader.php';

class GoogleTranslateController extends YireoController
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->_default_view = 'home';

		return parent::__construct();
	}

	/**
	 * Translate task
	 */
	public function translate()
	{
		// Get the language from request
		$input = JFactory::getApplication()->input;
		$text = $input->get('text', null, 'raw');
		$toLang = $input->getCmd('to');
		$fromLang = $input->getCmd('from');

		// Detect JoomFish languages
		if (preg_match('/joomfish([0-9\-]+)/', $toLang))
		{
			$languageId = preg_replace('/([^0-9]+)/', '', $toLang);
			$toLang = null;

			$db = JFactory::getDBO();
			$db->setQuery('SELECT * FROM #__languages');
			$languages = $db->loadObjectList();

			if (!empty($languages))
			{
				foreach ($languages as $language)
				{
					if (isset($language->id) && $language->id == $languageId)
					{
						$matchLanguage = $language;
						break;
					}
					else
					{
						if (isset($language->lang_id) && $language->lang_id == $languageId)
						{
							$matchLanguage = $language;
							break;
						}
					}
				}

				if (!empty($matchLanguage))
				{
					if (!empty($matchLanguage->lang_code))
					{
						$toLang = $matchLanguage->lang_code;
					}
					else
					{
						if (!empty($matchLanguage->shortcode))
						{
							$toLang = $matchLanguage->shortcode;
						}
						else
						{
							if (!empty($matchLanguage->sef))
							{
								$toLang = $matchLanguage->sef;
							}
						}
					}
				}
			}
		}

		// Parse the language
		$toLang = preg_replace('/-([a-zA-Z0-9]+)$/', '', $toLang);
		$fromLang = preg_replace('/-([a-zA-Z0-9]+)$/', '', $fromLang);

		// Get the translation model
		$model = $this->getModel('translate');

		if (empty($model))
		{
			$this->response(JText::_('Unable to fetch translation model'), false);

			return false;
		}

		// Fetch the translation
		$translation = $model->translate($text, $toLang, $fromLang);

		if (!empty($translation))
		{
			$this->response($translation);

			return true;
		}

		$translationErrors = $model->getErrors();
		$this->response(implode('; ', $translationErrors), false);

		return false;
	}

	/*
	 * Helper method to send a response
	 *
	 * @param string $text
	 * @param bool $success
	 */
	protected function response($text, $success = true)
	{
		$response = array('text' => $text, 'code' => (int) $success);
		print json_encode($response);

		$application = JFactory::getApplication();
		$application->close();
	}
}
