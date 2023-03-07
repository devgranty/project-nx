$(document).ready(function() { 
    $('body').bootstrapMaterialDesign(); 
    $('[data-toggle="tooltip"]').tooltip();

    function setTooltip(btn, message){
        $(btn).attr('data-original-title', message).tooltip('show').removeAttr('data-original-title');
    }
    if(typeof clipboard !== 'undefined') {
        clipboard.on('success', function(e) {
            setTooltip(e.trigger, 'Copied!');
        });
        clipboard.on('error', function(e) {
            setTooltip(e.trigger, 'Failed to copy!');
        });
    }
});

function optionSelected(selectId, optionValue){
    var selectElement = document.getElementById(selectId);
    var selectOptions = selectElement.options;
    for(var opt,i = 0; opt = selectOptions[i]; i++){
        if(opt.value == optionValue){
            selectElement.selectedIndex = i;
            break;
        }
    }
}

(function ($) {
    $.fn.loaddata = function(options, postOptions){
        var settings = $.extend({
            start_page 		: 1
        }, options);
        var el = this;
        loading  = false;
        end_record = false;
        contents(el, settings, postOptions);
        $(window).scroll(function(){
            if(($(window).scrollTop() + $(window).height()) >= ($(document).height() - 100)){
                contents(el, settings, postOptions);
            }
        });		
    };
    function contents(el, settings, postOptions){
        var load_img = $('<div/>').prepend($('<div/>').addClass('spinner-grow text-primary')).addClass('col-12 text-center');
        var record_end_txt = $('<div/>').text(settings.end_record_text).addClass('col-12').addClass('s-no-fetch-data-msg');
        if(loading == false && end_record == false){
            loading = true;
            el.append(load_img);
            $.post(settings.data_url, $.extend({'page':settings.start_page}, postOptions), function(data){
                if(data.trim().length == 0){
                    el.append(record_end_txt);
                    load_img.remove();
                    end_record = true;
                    return;
                }
                loading = false;
                load_img.remove();
                el.append(data);
                settings.start_page ++;
            })
        }
    }
}(jQuery));