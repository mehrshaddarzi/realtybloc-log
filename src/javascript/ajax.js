/** Set AjaxQ Option */
rbl_js.ajax_queue = {
    key: 'realty-bloc-log',
    time: 400 // millisecond
};

/**
 * Base AjaxQ function For All request
 *
 * @param url
 * @param params
 * @param callback
 * @param error_callback
 * @param type
 */
rbl_js.ajaxQ = function (url, params, callback, error_callback, type = 'GET') {

    // prepare Ajax Parameter
    let ajaxQ = {
        url: url,
        type: type,
        dataType: "json",
        cache: false,
        data: params,
        success: function (data) {

            // If Not Meta Box Ajax
            rbl_js[callback](data);

            // Check After Load Hook
            // if (rbl_js[callback]['meta_box_init']) {
            //     setTimeout(function () {
            //         rbl_js[callback]['meta_box_init'](data);
            //     }, 150);
            // }
        },
        error: function (xhr, status, error) {
            rbl_js[error_callback](xhr.responseText)
        }
    };

    // Send Request and Get Response
    jQuery.ajaxq(rbl_js.ajax_queue.key, ajaxQ);
};