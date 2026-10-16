$(document).ready(function () {
    const buttonPostTest = $('#buttonPostTest');
    const myModal = new bootstrap.Modal(document.getElementById('myModal'));
    const captchaImg = $('#captchaImage');
    const sendCaptchaButton = $('#sendCaptchaButton');
    const captchaText = $('#captchaText');
    const captchaSid = $('#captchaSid');

    buttonPostTest.click(function () {
        process();
    });

    sendCaptchaButton.click(function () {
        sendCaptcha();
    });

    function process() {
        $.ajax({
            url: 'controllers/likes.php?mode=get_liked_photos',
            type: 'post',
            cache: false,
            data: {},
            error: function (response) {
                alert(response);
            },
            success: function (response) {
                if (typeof response !== 'undefined' && response != '') {
                    let data;

                    try {
                        data = $.parseJSON(response);
                    } catch (error) {
                        process();
                    }

                    if (data) {
                        if (
                            data.error &&
                            data.error.captcha_img &&
                            data.error.captcha_sid
                        ) {
                            captchaText.val('');
                            captchaImg.attr('src', '');
                            saveCaptchaImage(data.error.captcha_img);
                            captchaSid.val(data.error.captcha_sid);

                            myModal.toggle();
                            captchaText.focus();
                        } else if (data === 'reboot') {
                            process();
                        }
                    }
                }
            },
        });
    }

    $(document).on('keypress', function (e) {
        if (e.which == 13 && captchaText.is(':visible') && captchaText.is(':focus')) {
            sendCaptcha();
        }
    });

    function saveCaptchaImage(imageSrc) {
        $.ajax({
            url: 'controllers/likes.php?mode=save_captcha',
            type: 'post',
            cache: false,
            data: {
                image: imageSrc,
            },
            success: function (response) {
                let data = $.parseJSON(response);

                captchaImg.attr('src', data.src);

                if (data.decoded_result) {
                    captchaText.val(data.decoded_result);
                }
            },
        });
    }

    function sendCaptcha() {
        myModal.toggle();

        $.ajax({
            url: 'controllers/likes.php?mode=get_liked_photos',
            type: 'post',
            cache: false,
            data: {
                captcha_sid: captchaSid.val(),
                captcha_key: captchaText.val(),
                captcha_image: captchaImg.attr('src'),
            },
            success: function (response) {
                process();
            },
        });
    }
});
