(function() {

    jQuery("a button.btn-success, a button.btn-danger").on('click', function(event) {
        event.stopImmediatePropagation();
        event.preventDefault();

        var element = jQuery(this);
        var href = getHrefAttribute(element);

        jQuery.ajax({
            url: href,
            method: 'GET'
        })
            .done(function(data) {
                window.location.href = data.returnUrl;
            })
    });

})();

function getHrefAttribute(element) {
    if (!(!!element.attr('href'))) {
        return element.parent('a').attr('href');
    }
    return element.attr('href');
}