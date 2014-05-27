/*
 * Joomla! component - GoogeTranslate
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com
 */

function doGoogleTranslate(editor, language, button) 
{
    // Loading
    setGoogleTranslateLoading(button);

    // Support for Joomla! languages
    if(jQuery('#jform_language').length !== 0) {
        language = jQuery('#jform_language').val();
    }

    // Support for JoomFish
    if(jQuery('#language_id').length !== 0) {
        language = 'joomfish' + jQuery('#language_id').val();
    }

    // Support for VirtueMart
    if(jQuery('#vmlang').length !== 0) {
        language = jQuery('#vmlang').val();
    }

    // Do not continue if no language was detected
    if(language == '' || language == undefined) {
        setGoogleTranslateError(button);
        alert('Failed to detect which language to translate to');
        return false;
    }

    // Fetch the text
    if(textfield = jQuery(editor)) {
        var originalText = textfield.val();
    }

    if (tinyMCE) {
        var originalText = tinyMCE.get(editor).getContent();
    }

    // Detect whether the text is empty
    if(originalText == '') {
        setGoogleTranslateError(button);
        alert('No text to translate');
        return false;
    }

    // Perform the POST
    var postdata = {to:language, text:originalText}; // @todo: Add-in a JToken
    var url = 'index.php?option=com_googletranslate&task=translate';
    jQuery.ajax({
        type: 'POST',
        url: url,
        data: postdata,
        success: function(data){
            newText = data.text;
            if(data.code == 0) {
                setGoogleTranslateError(button);
                alert(newText);
            } else {
                setGoogleTranslateComplete(button);
            }
             
            if(data.code == 1 && newText != originalText) {
                textfield.val(newText);
                if (typeof isBrowserIE == 'function') {
                    if (isBrowserIE()) {
                        if (window.parent.tinyMCE) {
                            window.parent.tinyMCE.selectedInstance.selection.moveToBookmark(window.parent.global_ie_bookmark);
                        }
                    }
                }
                tinyMCE.execCommand('mceSetContent', false, newText);

                /*
                textfield.val(newText);
                textfield.html(newText);
                textfield.text(newText);
                if(tinyMCE) {
                    formFieldId = textfield.attr(\'id\');
                    var editor = tinyMCE.getInstanceById(formFieldId);
                    if(editor) {
                        editor.focus();
                        editor.setContent(newText);
                    }
                }
                */
            }
        },
        dataType: 'json'
    });

    return false;
}

function setGoogleTranslateLoading(button)
{
    // Add the class-name
    jQuery(button).addClass('loading');
    jQuery(button).children().attr('class', 'icon-loop');
}

function setGoogleTranslateComplete(button)
{
    // Add the class-name
    jQuery(button).removeClass('loading');
    jQuery(button).children().attr('class', 'icon-copy');
}

function setGoogleTranslateError(button)
{
    // Add the class-name
    jQuery(button).removeClass('loading');
    jQuery(button).children().attr('class', 'icon-warning');
}

