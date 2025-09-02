<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <script type="text/javascript" src="js/uploadFunctions.js"></script>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">

    <title>Upload Form</title>
</head>
<body>
    <div class="container">
        <input id="offset" type="hidden" value="0">
        <input id="photo_id" type="hidden" value="">

        <div class="container mt-5">
            <div class="row mt-5">
                <div class="col-md">
                    <div class="d-grid gap-2">
                        <button id="buttonPostAll" type="button" class="btn btn-primary">Post All</button>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md">
                    <div class="d-grid gap-2">
                        <button id="buttonPost" type="button" class="btn btn-success button-post">Post</button>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md">
                    <div class="d-grid gap-2">
                        <button id="buttonPostVideos" type="button" class="btn btn-info btn-primary">Post Videos</button>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-sm">
                    <div class="d-grid gap-2">
                        <button id="buttonPrev"
                            type="button"
                            class="btn btn-primary"
                        >
                            Load Prev
                        </button>
                    </div>
                </div>
                <div class="col-md-8 mb-5 border">
                    <img class="image" id="image" src="">
                </div>
                <div class="col-sm">
                    <div class="d-grid gap-2">
                        <button id="buttonNext"
                            type="button"
                            class="btn btn-primary"
                        >
                            Load Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>