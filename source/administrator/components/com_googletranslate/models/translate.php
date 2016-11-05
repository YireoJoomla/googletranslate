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

class GoogleTranslateModelTranslate extends YireoCommonModel
{
	/**
	 * Make sure this model is connected to any table
	 *
	 * @var bool
	 */
	protected $_skip_table = true;

	/**
	 * Array of errors
	 *
	 * @var array
	 */
	protected $errors = array();

	/**
	 * @var array
	 */
	protected $request_fields = array();

	/**
	 * Translate task
	 *
	 * @param $text string
	 * @param $toLang string
	 * @param $fromLang string
	 *
	 * @return string
	 */
	public function translate($text, $toLang = null, $fromLang = null)
	{
		$params = JComponentHelper::getParams('com_googletranslate');

		if ($params->get('bork', 0) == 1)
		{
			$newText = $this->bork($text);

			if (empty($newText))
			{
				return 'Bork failed';
			}

			return $newText;
		}

		// Convert text to UTF-8
		if ($params->get('fix_encoding', 0) == 1 && function_exists('utf8_decode'))
		{
			$newText = utf8_decode($text);

			if (!empty($text) && strstr($newText, '????') == false)
			{
				$text = $newText;
			}
		}

		// Fix encoding issue
		if ($params->get('fix_encoding', 0) == 1 && function_exists('iconv'))
		{
			$newText = @iconv('UTF-8', 'ISO8859-1', $text);

			if (!empty($text) && strstr($newText, '????') == false)
			{
				$text = $newText;
			}
		}

		// Fetch parameters
		$api_id = $params->get('api_id');

		// Sanity checks
		if (empty($api_id))
		{
			$this->errors[] = JText::_('GoogleTranslate API-key is not configured');

			return false;
		}

		if (empty($text))
		{
			$this->errors[] = JText::_('No text to translate');

			return false;
		}

		if (empty($toLang))
		{
			$this->errors[] = JText::_('Failed to detect destination-language');

			return false;
		}

		// Get the result
		$result = $this->getCurlTranslate($text, $toLang, $fromLang);

		if (empty($result))
		{
			$this->errors[] = JText::_('No response from Google');

			return false;
		}

		// Parse the result
		$result = json_decode($result, true);

		if (isset($result['data']['translations'][0]['translatedText']))
		{
			$text = $result['data']['translations'][0]['translatedText'];

			if (empty($text))
			{
				$this->errors[] = JText::_('Empty translation result');

				return false;
			}

			$text = urldecode($text);

			return $text;
		}

		if (isset($result['error']['errors'][0]['message']))
		{
			$this->errors[] = JText::_('GoogleTranslate message');
			$this->errors[] = var_export($result['error']['errors'][0]['message'], true);
			$this->errors[] = var_export($this->request_fields, true);

			return false;
		}

		$this->errors[] = JText::_('Unknown GoogleTranslate error');
		$this->errors[] = var_export($result, true);

		return false;
	}

	/*
	 * Helper method to get a CURL-response
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	protected function getCurlDetect($text)
	{
		$params = JComponentHelper::getParams('com_googletranslate');
		$api_id = $params->get('api_id');
		$fields = array(
			'key' => $api_id,
			'q' => $text,);

		return $this->getCurlResponse('detect', $fields);
	}

	/*
	 * Helper method to get a CURL-response
	 *
	 * @param string $text
	 * @param string $toLang
	 * @param string $fromLang
	 *
	 * @return string
	 *
	 * @link http://msdn.microsoft.com/en-us/library/ff512406.aspx
	 */
	protected function getCurlTranslate($text, $toLang, $fromLang)
	{
		$debug = false;

		if ($debug)
		{
			$data = array(
				'data' => array(
					'translations' => array(
						array('translatedText' => 'Mijn vertaling'),),),);

			return json_encode($data);
		}

		$params = JComponentHelper::getParams('com_googletranslate');
		$api_id = $params->get('api_id');

		$this->request_fields = array(
			'key' => $api_id,
			'target' => $toLang,
			//'source' => $fromLang,
			'format' => 'html',
			'prettyprint' => '1',
			'q' => $text,);

		return $this->getCurlResponse(null, $this->request_fields);
	}

	/*
	 * Helper method to get a CURL-response
	 *
	 * @param string $task
	 * @param array $fields
	 *
	 * @return string
	 */
	protected function getCurlResponse($task = null, $fields)
	{
		$url = 'https://www.googleapis.com/language/translate/v2';

		if (!empty($task))
		{
			$url .= '/' . $task;
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'X-HTTP-Method-Override: GET',
			'Content-Type: application/x-www-form-urlencoded; charset=utf-8',));
		curl_setopt($ch, CURLOPT_REFERER, JURI::current());

		$result = curl_exec($ch);

		if ($result == false)
		{
			$this->errors[] = JText::_('CURL error') . ' = ' . curl_error($ch);

			return false;
		}

		return $result;
	}

	/**
	 * Check whether there are any errors
	 *
	 * @return bool
	 */
	public function hasErrors()
	{
		if (!empty($this->errors))
		{
			return true;
		}

		return false;
	}

	/**
	 * Get the listing of errors
	 *
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * Method to borkify a given text
	 *
	 * @param $text
	 *
	 * @return mixed|string
	 */
	public function bork($text)
	{
		$textBlocks = preg_split('/(%[^ ]+)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
		$newTextBlocks = array();

		foreach ($textBlocks as $text)
		{
			if (strlen($text) && $text[0] == '%')
			{
				$newTextBlocks[] = (string) $text;
				continue;
			}

			$originalText = $text;
			$searchMap = array(
				'/au/',
				'/\Bu/',
				'/\Btion/',
				'/an/',
				'/a\B/',
				'/en\b/',
				'/\Bew/',
				'/\Bf/',
				'/\Bir/',
				'/\Bi/',
				'/\bo/',
				'/ow/',
				'/ph/',
				'/th\b/',
				'/\bU/',
				'/y\b/',
				'/v/',
				'/w/',
				'/oo/',
				'/oe/');
			$replaceMap = array(
				'oo',
				'oo',
				'shun',
				'un',
				'e',
				'ee',
				'oo',
				'ff',
				'ur',
				'ee',
				'oo',
				'oo',
				'f',
				't',
				'Oo',
				'ai',
				'f',
				'v',
				'ø',
				'œ',);

			$text = preg_replace($searchMap, $replaceMap, $text);

			if ($originalText == $text && count($newTextBlocks))
			{
				$text .= '-a';
			}

			if (empty($text))
			{
				$text = $originalText;
			}

			$newTextBlocks[] = (string) $text;
		}

		$text = implode('', $newTextBlocks);
		$text = preg_replace('/([:.?!])(.*)/', '\\2\\1', $text);

		//$text .= '['.$this->getData('toLang').']';

		return $text;
	}
}
