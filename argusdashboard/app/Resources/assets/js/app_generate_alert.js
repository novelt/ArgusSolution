let f = '#app_generate_alert';
let token = $(f + '__token');
let disease = $(f + '_disease');

disease.change(function()
{
    let form = $(this).closest('form');
    let data = {};

    data[token.attr('name')] = token.val();
    data[disease.attr('name')] = disease.val();

    $.post(form.attr('action'), data).then(function(response)
    {
        console.log(response);
    });
});
