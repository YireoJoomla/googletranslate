/*
 * Joomla! component - GoogleTranslate
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 */

jQuery(function() {
    jQuery("input").each(function() {
        addButtonToInput(jQuery(this));
    });
});

var allowedInputTypes = ['text'];
var skipInputNames = ['alias', 'publish_up', 'publish_down', 'created', 'created_by_alias', 'modified', 'hits',
    'id', 'xreference', 'metadata_author', 'metadata_xreference', 'images_image_intro', 'images_image_fulltext',
    'treeselectfilter', 'params_cache_time', 'params_header_class'];

function addButtonToInput(input)
{
    var inputId = input.attr('id');
    var inputName = input.attr('name');
    var inputType = input.attr('type');

    if(inputName == undefined) {
        return false;
    }

    if(inputId == undefined) {
        return false;
    }

    if(input.attr('disabled') == 'disabled' || input.prop('readonly')) {
        return false;
    }

    if(inArray(inputName, skipInputNames) || inArray(inputId, skipInputNames)) {
        return true;
    }

    if(inArray(inputType, allowedInputTypes) == false) {
        return false;
    }

    var parent = input.parent();
    var addonStyle = null;

    if (input.hasClass('input-xxlarge')) {
        addonStyle = 'height:auto; line-height:22px;';
    }

    if(parent.hasClass('input-append') == false) {
        var html = '<div class="input-append">'
            + input.prop('outerHTML')
            + '<span class="add-on googletranslate-add-on" style="' + addonStyle + '">'
            + '<a href="#" title="GoogleTranslate" onclick="javascript:doGoogleTranslate(\'#' + inputId + '\'); return false;">'
            + '<i class="icon-copy"></i>'
            + '</a>'
            + '</span>'
            + '</div>';

    } else {
        var html = input.html()
            + '<span class="add-on googletranslate-add-on" style="' + addonStyle + '">'
            + '<a href="#" title="GoogleTranslate" onclick="javascript:doGoogleTranslate(\'#' + inputId + '\'); return false;">'
            + '<i class="icon-copy"></i>'
            + '</a>'
            + '</span>';
    }

    input.replaceWith(html);
    //console.log(inputName + ' / ' + inputId + ' = ' + inputType);

    return true;
}

function inArray(name, array)
{
    var count = array.length;
    for(var i = 0; i < count; i++)
    {
        if(array[i] === name ) {
            return true;
        }

        if('jform_' + array[i] === name ) {
            return true;
        }
    }

    return false;
}
