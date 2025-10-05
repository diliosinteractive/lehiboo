"use strict";
function ova_lg_recapcha_v2() {
    
    const recaptcha         = document.getElementsByClassName('ovalg-recaptcha-wrapper');
    if ( recaptcha.length ) {
        for (let i = 0; i < recaptcha.length; i++) {
            let $options = {
                sitekey: recapcha_object.site_key,
            };
            grecaptcha.ready(function(){
                grecaptcha.render(recaptcha.item(i), $options);
            });
        }
    }
    // Send mail vendor
    if ( document.getElementById("ovaevent-recaptcha-wrapper") ) {
        const $event_recapcha = document.getElementById("ovaevent-recaptcha-wrapper");
        let $options = {
            'sitekey': recapcha_object.site_key,
            'callback': ova_lg_recapcha_callback,
            'expired-callback': ova_lg_expired_callback,
        };
        grecaptcha.ready(function(){
            grecaptcha.render($event_recapcha, $options);
        });
    }


}
function ova_lg_recapcha_v3() {

    grecaptcha.ready(function(){
        grecaptcha.execute(recapcha_object.site_key, {
            action: 'validate_recaptchav3'
        }).then(function (token) {
            if ( document.querySelectorAll('.g-recaptcha-response').length ) {
                document.querySelectorAll('.g-recaptcha-response').forEach(function (elem) {
                    elem.value = token;
                });
            }
        });
    });
}

var ova_lg_recapcha_callback = function(token){
    if ( document.getElementById("ovaevent_recapcha_token") ) {
        document.getElementById("ovaevent_recapcha_token").value = token;
    }
}

var ova_lg_expired_callback = function(){
    if ( document.getElementById("ovaevent_recapcha_token") ) {
        document.getElementById("ovaevent_recapcha_token").setAttribute("data-pass","no");
    }
}