<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <script type="text/javascript" src="js/likedStuffFunctions.js"></script>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">

    <title>Likes Form</title>
</head>
<body>
    <div class="modal" tabindex="-1" id="myModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Enter captcha</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="col-md-8 mb-5 border">
                            <img class="image" id="captchaImage" src="">
                        </div>
                        <label for="captchaText" class="col-form-label">Captcha text:</label>
                        <input type="text" class="form-control" id="captchaText">
                        <input type="hidden" id="captchaSid" value="">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" type="submit" class="btn btn-primary" id="sendCaptchaButton">Send</button>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="container mt-5">
            <div class="row mt-2">
                <div class="col-md">
                    <div class="d-grid gap-2">
                        <button id="buttonPostTest" class="btn btn-info btn-primary">Test</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>