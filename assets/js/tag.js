$(document).ready(function(){
    let tagInput = $('input.tag-input');
    tagInput.attr('type', 'hidden');
    let container = buildContainer();
    tagInput.parent().append(container);
    const tags = getTags(tagInput);
    for (let key in tags){
        appendTag(tags[key], container);
    }
});

function buildContainer()
{
    return $('<div />').addClass('tag-container').click(function (e) {
        e.stopPropagation();
        createInput(this);
    });
}

function getTags(tagInput)
{
    return tagInput.val().split(',').map(function (string) {
        return trim(string);
    }).filter(tag => tag.length > 0);
}

function trim(string) {
    return string.replace(/^\s/g, '').replace(/\s$/g, '');
}

function appendTag(tag, container)
{
    let item = $('<div />').addClass('tag').text(tag).click(function(e) {
        e.stopPropagation();
    });

    item.append(
        $('<a href="#" />').append(
            $('<i />').addClass('fa-solid fa-xmark ms-1 me-1')
        ).click(function (e) {
            e.preventDefault();
            e.stopPropagation();
            removeTag(e.currentTarget);
        })
    );
    item.prepend($('<i />').addClass('fa-solid fa-tag ms-1 me-1'));

    $(container).append(item);
}

function addTag(tag, container)
{
    tag = tag.replace(/[^A-Za-z0-9_-]+/g, '');
    if (tag.length > 0) {
        if (updateValue(tag)) {
            appendTag(tag, container);
        }
    }
}

function removeTag(object)
{
    const parent = $(object).parents('div:first');
    const input = $('.tag-input');
    input.val(getTags(input).filter(tag => tag !== parent.text()).join(','))
    parent.remove();
}

function createInput(tagContainer)
{
    $(tagContainer).find('.tag-new').remove();
    let input = $('<input type="text" class="tag-new" maxlength="64" />').click(function(e){
        e.stopPropagation();
    }).keydown(function(e) {
        let code = e.keyCode || e.which;
        if (code === 13) { //enter
            e.preventDefault();
        }
    }).keyup(function(e){
        let code = e.keyCode || e.which;
        if (code === 13 || code === 188 || code === 191) { //enter
            let tagName = trim(code === 13 ? $(this).val() : $(this).val().replace(/,/g, ''));
            if (tagName.length > 0) {
                addTag(tagName, tagContainer);
                $(this).val('');
                $(tagContainer).append($(this));
                $(this).focus();
                $('.tag-drop-down-container').remove();
            }
        } else {
            if ($(this).val().length > 2) {
                preloadExistingTags($(this).val());
            }
        }
    });

    $(document).click(function(e) {
        $(document).off('click');
        $(input).remove();
    });

    $(tagContainer).append(input);
    $(input).focus();
}

function updateValue(tagName)
{
    let input = $('.tag-input');
    let tags = getTags(input);
    for (key in tags) {
        if (tags[key] === tagName) {
            return false;
        }
    }
    tags.push(tagName);
    input.val(tags.join(','));
    return true;
}

function preloadExistingTags(tagName)
{
    $.get('/administrator/tag/search', {
        'name': tagName,
    }, function( data ){
        $('.tag-drop-down-container').remove();
        if (data.length) {
            buildDropDownMenu(data);
        }
    });
}

function buildDropDownMenu(data)
{
    const container = $('<ul class="tag-drop-down-container" />');
    const tagContainer = $('.tag-container');

    for(const key in data) {
        const tag = data[key]['name'];

        $('<li />').text(tag).click(function (e){
            e.stopPropagation();
            addTag($(this).text(), tagContainer);
            $(this).parent('ul:first').remove();
            $('body').click();
        }).appendTo(container);
    }

    container.appendTo(tagContainer);
}