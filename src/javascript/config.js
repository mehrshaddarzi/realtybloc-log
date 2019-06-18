/* Start Admin Js */
var rbl_js = {};

/* Get global Data From Frontend */
rbl_js.global = (typeof rbl_global != 'undefined') ? rbl_global : [];

/* Check Active Option */
rbl_js.is_active = function (option) {
    return rbl_js.global.options[option] === 1;
};