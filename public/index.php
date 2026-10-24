<?php
use App\Support\Config;

require_once __DIR__ . '/../vendor/autoload.php';
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script>
        window.APP_CONFIG = {
            REQUEST_SLEEP_TIME_MS: <?= (int) Config::REQUEST_SLEEP_TIME_MS ?>
        };
    </script>

    <script type="text/javascript" src="js/classes/RequestPool.js"></script>
    <script type="text/javascript" src="js/grid_images.js"></script>
    <script type="text/javascript" src="js/grid_videos.js"></script>

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/tailwind/tailwind.css">
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">

    <title>VK Upload App</title>
</head>

<body class="bg-gray-800 min-h-screen">
    <div class="container mx-auto px-4 py-8 text-gray-200">
        <div class="max-w-4xl mx-auto rounded-xl shadow-md overflow-hidden p-6 bg-white/10">
            <h1 class="text-3xl font-bold text-center mb-8">VK Upload App</h1>

            <div class="flex flex-col my-8 gap-4">
                <div id="buttonsContainer" class="flex justify-between gap-6">
                    <button id="buttonUploadImages"
                        class="
                            w-full relative group overflow-hidden rounded-xl bg-linear-to-r from-sky-600 to-indigo-700 px-6 py-3.5 text-white font-medium shadow-lg shadow-blue-500/25 transition-all duration-300 hover:shadow-blue-500/40 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer
                        "
                    >
                        <div class="absolute inset-0 rounded-xl border border-white/20 pointer-events-none">
                        </div>

                        <span class="relative z-10 flex items-center justify-center gap-2">
                            Post Images
                        </span>

                        <div class="
                            absolute inset-0 bg-linear-to-r from-white/0 via-white/30 to-white/0 opacity-0 group-hover:opacity-100 transition-all duration-700 ease-out transform -translate-x-full group-hover:translate-x-full pointer-events-none
                        ">
                        </div>
                    </button>

                    <button id="buttonUploadVideos"
                        class="
                            w-full relative group overflow-hidden rounded-xl bg-linear-to-r from-purple-600 to-pink-700 px-6 py-3.5 text-white font-medium shadow-lg shadow-purple-500/25 transition-all duration-300 hover:shadow-purple-500/40 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer
                        "
                    >
                        <div class="absolute inset-0 rounded-xl border border-white/20 pointer-events-none">
                        </div>

                        <span class="relative z-10 flex items-center justify-center gap-2">
                            Post Videos
                        </span>

                        <div class="
                            absolute inset-0 bg-linear-to-r from-white/0 via-white/30 to-white/0 opacity-0 group-hover:opacity-100 transition-all duration-700 ease-out transform -translate-x-full group-hover:translate-x-full pointer-events-none
                        ">
                        </div>
                    </button>
                </div>

                <details id="optionsContainer"
                    class="group border border-gray-500 rounded-xl bg-gray-800/40 overflow-hidden"
                    open
                >
                    <summary class="
                        flex items-center justify-between p-3 cursor-pointer select-none
                        list-none [&::-webkit-details-marker]:hidden
                    ">
                        <span class="text-gray-100 font-medium">
                            Options
                        </span>

                        <svg
                            class="w-5 h-5 text-gray-400 transition-transform duration-300 group-open:rotate-180"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 9l-7 7-7-7"
                            >
                            </path>
                        </svg>
                    </summary>

                    <form id="optionsForm" class="border-t border-gray-500 flex justify-center gap-2 p-2">
                        <div class="
                            flex flex-col items-start
                            text-sm leading-relaxed
                        ">
                            <div id="isPostImagesToWallContainer" class="w-full">
                                <label
                                    for="isPostImagesToWall"
                                    class="flex items-center gap-2 p-2 rounded-lg cursor-pointer hover:bg-white/5 transition-colors duration-200 group/checkbox"
                                >
                                    <input
                                        type="hidden"
                                        name="isPostImagesToWall"
                                        value="0"
                                    />

                                    <input
                                        id="isPostImagesToWall"
                                        name="isPostImagesToWall"
                                        type="checkbox"
                                        class="peer sr-only"
                                    />

                                    <div class="
                                        w-5 h-5 rounded border-2 border-gray-500 bg-gray-800 flex items-center justify-center
                                        group-has-checked/checkbox:bg-sky-500 group-has-checked/checkbox:border-sky-500
                                        peer-focus-visible:ring-2 peer-focus-visible:ring-sky-400 peer-focus-visible:ring-offset-2 peer-focus-visible:ring-offset-gray-800
                                        transition-all duration-200
                                    ">
                                        <svg
                                            class="w-3.5 h-3.5 text-white opacity-0 scale-50 group-has-checked/checkbox:opacity-100 group-has-checked/checkbox:scale-100 transition-all duration-200"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="3"
                                                d="M5 13l4 4L19 7"
                                            >
                                            </path>
                                        </svg>
                                    </div>

                                    <span class="text-gray-200 transition-colors duration-200">
                                        Post Images to wall
                                    </span>
                                </label>
                            </div>

                            <div id="isMoveImagesAfterUploadContainer" class="w-full">
                                <label
                                    for="isMoveImagesAfterUpload"
                                    class="flex items-center gap-2 p-2 rounded-lg cursor-pointer hover:bg-white/5 transition-colors duration-200 group/checkbox"
                                >
                                    <input
                                        type="hidden"
                                        name="isMoveImagesAfterUpload"
                                        value="0"
                                    />

                                    <input
                                        id="isMoveImagesAfterUpload"
                                        name="isMoveImagesAfterUpload"
                                        type="checkbox"
                                        class="peer sr-only"
                                    />

                                    <div class="
                                        w-5 h-5 rounded border-2 border-gray-500 bg-gray-800 flex items-center justify-center
                                        group-has-checked/checkbox:bg-sky-500 group-has-checked/checkbox:border-sky-500
                                        peer-focus-visible:ring-2 peer-focus-visible:ring-sky-400 peer-focus-visible:ring-offset-2 peer-focus-visible:ring-offset-gray-800
                                        transition-all duration-200
                                    ">
                                        <svg
                                            class="w-3.5 h-3.5 text-white opacity-0 scale-50 group-has-checked/checkbox:opacity-100 group-has-checked/checkbox:scale-100 transition-all duration-200"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="3"
                                                d="M5 13l4 4L19 7"
                                            >
                                            </path>
                                        </svg>
                                    </div>

                                    <span class="text-gray-200 transition-colors duration-200">
                                        Move Images after upload
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="w-px bg-linear-to-b from-transparent via-gray-500/50 to-transparent"></div>

                        <div class="
                            flex flex-col items-start
                            text-sm leading-relaxed
                        ">
                            <div id="isPostVideosToWallContainer" class="w-full">
                                <label
                                    for="isPostVideosToWall"
                                    class="flex items-center gap-2 p-2 rounded-lg cursor-pointer hover:bg-white/5 transition-colors duration-200 group/checkbox"
                                >
                                    <input
                                        type="hidden"
                                        name="isPostVideosToWall"
                                        value="0"
                                    />

                                    <input
                                        id="isPostVideosToWall"
                                        name="isPostVideosToWall"
                                        type="checkbox"
                                        class="peer sr-only"
                                    />

                                    <div class="
                                        w-5 h-5 rounded border-2 border-gray-500 bg-gray-800 flex items-center justify-center
                                        group-has-checked/checkbox:bg-sky-500 group-has-checked/checkbox:border-sky-500
                                        peer-focus-visible:ring-2 peer-focus-visible:ring-sky-400 peer-focus-visible:ring-offset-2 peer-focus-visible:ring-offset-gray-800
                                        transition-all duration-200
                                    ">
                                        <svg
                                            class="w-3.5 h-3.5 text-white opacity-0 scale-50 group-has-checked/checkbox:opacity-100 group-has-checked/checkbox:scale-100 transition-all duration-200"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="3"
                                                d="M5 13l4 4L19 7"
                                            >
                                            </path>
                                        </svg>
                                    </div>

                                    <span class="text-gray-200 transition-colors duration-200">
                                        Post Videos to wall
                                    </span>
                                </label>
                            </div>

                            <div id="isMoveVideosAfterUploadContainer" class="w-full">
                                <label
                                    for="isMoveVideosAfterUpload"
                                    class="flex items-center gap-2 p-2 rounded-lg cursor-pointer hover:bg-white/5 transition-colors duration-200 group/checkbox"
                                >
                                    <input
                                        type="hidden"
                                        name="isMoveVideosAfterUpload"
                                        value="0"
                                    />

                                    <input
                                        id="isMoveVideosAfterUpload"
                                        name="isMoveVideosAfterUpload"
                                        type="checkbox"
                                        class="peer sr-only"
                                    />

                                    <div class="
                                        w-5 h-5 rounded border-2 border-gray-500 bg-gray-800 flex items-center justify-center
                                        group-has-checked/checkbox:bg-sky-500 group-has-checked/checkbox:border-sky-500
                                        peer-focus-visible:ring-2 peer-focus-visible:ring-sky-400 peer-focus-visible:ring-offset-2 peer-focus-visible:ring-offset-gray-800
                                        transition-all duration-200
                                    ">
                                        <svg
                                            class="w-3.5 h-3.5 text-white opacity-0 scale-50 group-has-checked/checkbox:opacity-100 group-has-checked/checkbox:scale-100 transition-all duration-200"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="3"
                                                d="M5 13l4 4L19 7"
                                            >
                                            </path>
                                        </svg>
                                    </div>

                                    <span class="text-gray-200 transition-colors duration-200">
                                        Move Videos after upload
                                    </span>
                                </label>
                            </div>
                        </div>
                    </form>
                </details>
            </div>

            <div class="flex flex-col items-center justify-center gap-6 mt-12">
                <h2 class="text-xl font-bold text-gray-100 flex items-center gap-3 w-full">
                    <span class="w-1.5 h-6 rounded-full bg-linear-to-b from-sky-400 to-indigo-500">
                    </span>

                    Images
                </h2>

                <div id="gridImages" class="
                    border border-gray-500 bg-gray-800/40 rounded-xl
                    p-2 w-full max-w-4xl mx-auto grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4
                ">
                </div>

                <h2 class="text-xl font-bold text-gray-100 flex items-center gap-3 w-full">
                    <span class="w-1.5 h-6 rounded-full bg-linear-to-b from-purple-400 to-pink-500">
                    </span>

                    Videos
                </h2>

                <div id="gridVideos" class="
                    border border-gray-500 bg-gray-800/40 rounded-xl
                    p-2 w-full max-w-4xl mx-auto grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4
                ">
                </div>
            </div>
        </div>
    </div>

    <template id="cardTemplate">
        <div
            class="
                card-template relative group bg-gray-800 rounded-xl overflow-hidden shadow-lg border border-gray-700
                transition-all duration-300 hover:shadow-sky-500/10 hover:border-gray-600
            "
            data-path=""
        >
            <div class="aspect-square flex items-center justify-center bg-gray-900 overflow-hidden">
                <img
                    class="card-image object-contain max-h-full max-w-full transition-transform duration-300 group-hover:scale-105"
                    src=""
                    alt=""
                >
            </div>

            <div
                class="card-status absolute inset-0 flex items-center justify-center bg-black/60 opacity-0 transition-opacity duration-300 pointer-events-none"
            >
                <svg
                    class="card-spinner animate-spin h-8 w-8 text-white hidden"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    >
                    </circle>
                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    >
                    </path>
                </svg>

                <svg
                    class="card-success h-10 w-10 text-green-400 hidden"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 13l4 4L19 7"
                    >
                    </path>
                </svg>

                <svg
                    class="card-error h-10 w-10 text-red-400 hidden"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"
                    >
                    </path>
                </svg>
            </div>

            <div class="
                card-name absolute bottom-0 left-0 right-0 bg-linear-to-t from-black/90 to-black/5 p-2 text-xs truncate text-gray-200 font-medium
            ">
            </div>
        </div>
    </template>
</body>
</html>