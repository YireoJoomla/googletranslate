<?php
/*
 * Joomla! System Plugin - Google Translate
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

/**
 * Google Translate System Plugin
 */
class PlgSystemGoogleTranslate extends JPlugin
{
	/**
	 * Event onAfterRender
	 */
	public function onBeforeRender()
	{
		$document = JFactory::getDocument();

		if ($this->allowTagReplacement())
		{
			$document->addScript(JUri::base() . '../media/com_googletranslate/js/editor-xtd.js');
		}

		if ($this->allowJavaScript() == false)
		{
			return;
		}

		JHtml::_('jquery.framework');
		$document->addScript(JUri::base() . '../media/com_googletranslate/js/system.js');
		$document->addScript(JUri::base() . '../media/com_googletranslate/js/editor-xtd.js');
		$document->addStyleSheet(JUri::base() . '../media/com_googletranslate/css/system.css');
	}

	/**
	 * Event onAfterRender
	 */
	public function onAfterRender()
	{
		if ($this->allowTagReplacement() == false)
		{
			return;
		}

		// Get the body and fetch a list of files
		$app = JFactory::getApplication();
		$body = $app->getBody();
		$replacedTags = array();

		if (preg_match_all('/\<input([^\>]+)\>/', $body, $matches))
		{
			foreach ($matches[0] as $matchIndex => $inputTag)
			{
				$inputTagHash = md5($inputTag);

				if (in_array($inputTagHash, $replacedTags))
				{
					continue;
				}

				$replacementTag = $this->getInputReplacement($inputTag);

				if (!empty($replacementTag))
				{
					$body = str_replace($inputTag, $replacementTag, $body);
					$replacedTags[] = $inputTagHash;
				}
			}
		}

		if (preg_match_all('/\<textarea([^\>]+)\>(.*)\<\/textarea\>/', $body, $matches))
		{
			foreach ($matches[0] as $matchIndex => $inputTag)
			{
				$inputTagHash = md5($inputTag);

				if (in_array($inputTagHash, $replacedTags))
				{
					continue;
				}

				if (stristr($inputTag, 'style="display:none"'))
				{
					continue;
				}

				$replacementTag = $this->getInputReplacement($inputTag, 'textarea');

				if (!empty($replacementTag))
				{
					$body = str_replace($inputTag, $replacementTag, $body);
					$replacedTags[] = $inputTagHash;
				}
			}
		}

		$app->setBody($body);
	}

	/**
	 * @param string $inputTag
	 *
	 * @return null|string
	 */
	protected function getInputReplacement($inputTag, $fieldType = 'input')
	{
		if ($fieldType == 'input')
		{
			if (preg_match('/type=\"([^\"]+)/', $inputTag, $matchType) == false)
			{
				return null;
			}

			$type = $matchType[1];

			if (in_array($type, $this->getAllowedTypes()) == false)
			{
				return null;
			}
		}

		$inputId = null;
		$inputScript = null;

		if (preg_match('/id=\"([^\"]+)/', $inputTag, $matchType))
		{
			$inputId = 'input#' . $matchType[1];
			$inputScript = 'javascript:doGoogleTranslate(\'' . $inputId . '\', \'\');';
			$inputScript .= 'return false;';
		}
		elseif (preg_match('/name=\"([^\"]+)/', $inputTag, $matchType))
		{
			$inputName = $matchType[1];
			$inputScript = 'javascript:doGoogleTranslateByName(\'' . $inputName . '\', \'\');';
			$inputScript .= 'return false;';
		}

		if (empty($inputName) && empty($inputId))
		{
			return null;
		}

		$inputHtml = array();
		$inputHtml[] = '<div class="input-append">';
		$inputHtml[] = $inputTag;
		$inputHtml[] = '<span class="add-on">';
		$inputHtml[] = '<a title="GoogleTranslate" href="#" onclick="' . $inputScript . '"><i class="icon-copy"></i></a>';
		$inputHtml[] = '</span>';
		$inputHtml[] = '</div>';

		return implode('', $inputHtml);
	}

	/**
	 * @return bool
	 */
	protected function allowJavaScript()
	{
		// Fetch variables
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$task = $jinput->getCmd('task');
		$view = $jinput->getCmd('view');

		// Check for the current view
		if (in_array($task, $this->getAllowedTasks()))
		{
			return false;
		}

		// Check for the current view
		if (!in_array($view, $this->getAllowedViews()))
		{
			return false;
		}

		return true;
	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	protected function allowTagReplacement()
	{
		// Fetch variables
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$task = $jinput->getCmd('task');

		// Check for the current view
		if (in_array($task, $this->getAllowedTasks()))
		{
			return true;
		}

		return false;
	}

	/**
	 * @return array
	 */
	protected function getAllowedTypes()
	{
		return array(
			'text',
		);
	}

	/**
	 * @return array
	 */
	protected function getAllowedTasks()
	{
		return array(
			'translate.edit',
			'translate.apply',
		);
	}

	/**
	 * @return array
	 */
	protected function getAllowedViews()
	{
		return array(
			'module',
			'category',
			'article',
			'item',
		);
	}

	/**
	 * @return array
	 */
	protected function getAllowedLayouts()
	{
		return array(
			'edit',
			'form',
		);
	}
}
