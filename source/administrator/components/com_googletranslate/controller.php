<?php
/**
 * Joomla! component GoogleTranslate
 *
 * @author Yireo
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com/
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Include the loader
require_once JPATH_COMPONENT.'/lib/loader.php';

class GoogleTranslateController extends YireoController
{
    /**
     * Constructor
     *
     * @access public
     * @param null
     * @return null
     */
    public function __construct()
    {
        $this->_default_view = 'home';
        parent::__construct();
    }

    /**
     * Translate task
     *
     * @access public
     * @param null
     * @return null
     */
    public function translate()
    {
        // Get the language from request
        $text = JRequest::getVar('text', null, null, null, JREQUEST_ALLOWRAW);
        $toLang = JRequest::getCmd('to');
        $fromLang = JRequest::getCmd('from');

        // Detect JoomFish languages
        if (preg_match('/joomfish([0-9\-]+)/', $toLang)) {

            $languageId = preg_replace('/([^0-9]+)/', '', $toLang);
            $toLang = null;

            $db = JFactory::getDBO();
            $db->setQuery('SELECT * FROM #__languages');
            $languages = $db->loadObjectList();
            if (!empty($languages)) {
                foreach ($languages as $language) {
                    if (isset($language->id) && $language->id == $languageId) {
                        $matchLanguage = $language;
                        break;
                    } else if (isset($language->lang_id) && $language->lang_id == $languageId) {
                        $matchLanguage = $language;
                        break;
                    }
                }

                if (!empty($matchLanguage)) {
                    if (!empty($matchLanguage->lang_code)) {
                        $toLang = $matchLanguage->lang_code; 
                    } else if (!empty($matchLanguage->shortcode)) {
                        $toLang = $matchLanguage->shortcode; 
                    } else if (!empty($matchLanguage->sef)) {
                        $toLang = $matchLanguage->sef; 
                    }
                }
            }
        }

        // Parse the language
        $toLang = preg_replace('/-([a-zA-Z0-9]+)$/', '', $toLang);
        $fromLang = preg_replace('/-([a-zA-Z0-9]+)$/', '', $fromLang);

        $params = JComponentHelper::getParams('com_googletranslate');
        $api_id = $params->get('api_id');

        // Sanity checks
        if (empty($api_id)) $this->response(JText::_('GoogleTranslate API-key is not configured'), false);
        if (empty($text)) $this->response(JText::_('No text to translate'), false);
        if (empty($toLang)) $this->response(JText::_('Failed to detect destination-language'), false);

        // Get the result
        $result = $this->getCurlTranslate($text, $toLang, $fromLang);
        if (empty($result)) $this->response(JText::_('No response from Google'), false);

        // Parse the result
        $result = json_decode($result, true);
        if (isset($result['data']['translations'][0]['translatedText'])) {
            $text = $result['data']['translations'][0]['translatedText'];
            $text = urldecode($text);
            $this->response($text);
        }

        if (isset($result['error']['errors'][0]['message'])) {
            $this->response('GoogleTranslate message: '.var_export($result['error']['errors'][0]['message'], true), false);
        }

        $this->response('Unknown GoogleTranslate error: '.var_export($result, true), false);
    }

    /*
     * Helper method to send a response 
     *
     * @access protected
     * @param string $text
     * @param bool $success
     * @return null
     */
    protected function response($text, $success = true)
    {
        $response = array('text' => $text, 'code' => (int)$success);
        print json_encode($response);
        $application = JFactory::getApplication();
        $application->close();
    }
    
    /*
     * Helper method to get a CURL-response 
     *
     * @access protected
     * @param string $text
     * @return string
     */
    protected function getCurlDetect($text)
    {
        $params = JComponentHelper::getParams('com_googletranslate');
        $api_id = $params->get('api_id');
        $fields = array(
            'key' => $api_id,
            'q' => $text,
        );
        return $this->getCurlResponse('detect', $fields);
    }

    /*
     * Helper method to get a CURL-response 
     * 
     * @access protected
     * @param string $text
     * @param string $toLang
     * @param string $fromLang
     * @return string
     * @link http://msdn.microsoft.com/en-us/library/ff512406.aspx
     */
    protected function getCurlTranslate($text, $toLang, $fromLang)
    {
        /*$data = array(
            'data' => array(
                'translations' => array(
                    array('translatedText' => 'Mijn vertaling'),
                ),
            ),
        );
        return json_encode($data);*/
        $params = JComponentHelper::getParams('com_googletranslate');
        $api_id = $params->get('api_id');
        $fields = array(
            'key' => $api_id,
            'target' => $toLang,
            //'source' => $fromLang,
            'format' => 'html',
            'prettyprint' => '1',
            'q' => $text,
        );
        return $this->getCurlResponse(null, $fields);
    }

    /*
     * Helper method to get a CURL-response 
     *
     * @access protected
     * @param string $task
     * @param array $fields
     * @return string
     */
    protected function getCurlResponse($task = null, $fields)
    {
        $url = 'https://www.googleapis.com/language/translate/v2';
        if (!empty($task)) $url .= '/'.$task;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: GET'));
        curl_setopt($ch, CURLOPT_REFERER, JURI::current());
        $result = curl_exec($ch);
        if ($result == false) {
            $this->response(JText::_('CURL error').': '.curl_error($ch), false);
        }
        return $result;
    }

    /*
     * Helper method to check for Joomla! 1.5
     *
     * @access protected
     * @param null
     * @return bool
     */
    protected function isJoomla15()
    {
        JLoader::import( 'joomla.version' );
        $version = new JVersion();
        if (version_compare( $version->RELEASE, '1.5', 'eq')) {
            return true;
        }
        return false;
    }
}
