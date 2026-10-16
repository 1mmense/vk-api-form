<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- <script type="text/javascript" src="js/uploadFunctions.js"></script> -->
    <script type="text/javascript" src="js/gridImages.js"></script>
    <script type="text/javascript" src="js/gridVideos.js"></script>

    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">

    <title>Upload Form</title>
</head>

<body class="bg-gray-800 min-h-screen">
    <div class="container mx-auto px-4 py-8 text-gray-200">
        <input id="offset" type="hidden" value="0">
        <input id="photo_id" type="hidden" value="">

        <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-md overflow-hidden p-6 bg-white/10">
            <h1 class="text-3xl font-bold text-center mb-8">VK Upload App</h1>

            <div class="flex justify-between mb-8 gap-6">
                <button id="buttonPostAll"
                    class="
                        w-full relative group overflow-hidden rounded-xl bg-gradient-to-r from-sky-600 to-indigo-700 px-6 py-3.5 text-white font-medium shadow-lg shadow-blue-500/25 transition-all duration-300 hover:shadow-blue-500/40 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer
                    "
                >
                    <div class="absolute inset-0 rounded-xl border border-white/20 pointer-events-none">
                    </div>

                    <span class="relative z-10 flex items-center justify-center gap-2">
                        Post Photos
                    </span>

                    <div class="
                        absolute inset-0 bg-gradient-to-r from-white/0 via-white/30 to-white/0 opacity-0 group-hover:opacity-100 transition-all duration-700 ease-out transform -translate-x-full group-hover:translate-x-full pointer-events-none
                    ">
                    </div>
                </button>

                <button id="buttonPostVideos"
                    class="
                        w-full relative group overflow-hidden rounded-xl bg-gradient-to-r from-purple-600 to-pink-700 px-6 py-3.5 text-white font-medium shadow-lg shadow-purple-500/25 transition-all duration-300 hover:shadow-purple-500/40 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer
                    "
                >
                    <div class="absolute inset-0 rounded-xl border border-white/20 pointer-events-none">
                    </div>

                    <span class="relative z-10 flex items-center justify-center gap-2">
                        Post Videos
                    </span>

                    <div class="
                        absolute inset-0 bg-gradient-to-r from-white/0 via-white/30 to-white/0 opacity-0 group-hover:opacity-100 transition-all duration-700 ease-out transform -translate-x-full group-hover:translate-x-full pointer-events-none
                    ">
                    </div>
                </button>
            </div>

            <div class="flex flex-col items-center justify-center gap-6">
                <!-- <div class="flex items-center justify-center gap-4 w-full">
                    <button id="buttonPrev"
                        class="
                            relative group overflow-hidden rounded-xl bg-gradient-to-r from-emerald-600 to-teal-700 px-6 py-3.5 text-white font-medium shadow-lg shadow-emerald-500/25 transition-all duration-300 hover:shadow-emerald-500/40 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer
                        "
                    >
                        <div class="absolute inset-0 rounded-xl border border-white/20 pointer-events-none">
                        </div>

                        <span class="relative z-10 flex items-center justify-center gap-2">
                            Load Prev
                        </span>

                        <div class="
                            absolute inset-0 bg-gradient-to-r from-white/0 via-white/30 to-white/0 opacity-0 group-hover:opacity-100 transition-all duration-700 ease-out transform -translate-x-full group-hover:translate-x-full pointer-events-none
                        ">
                        </div>
                    </button>

                    <div class="border-2 border-dashed border-gray-100 rounded-xl w-full max-w-md h-80 flex items-center justify-center overflow-hidden">
                        <img id="image" src="" alt="Preview" class="object-contain max-h-full max-w-full">
                    </div>

                    <button id="buttonNext"
                        class="
                            relative group overflow-hidden rounded-xl bg-gradient-to-r from-amber-600 to-orange-700 px-6 py-3.5 text-white font-medium shadow-lg shadow-amber-500/25 transition-all duration-300 hover:shadow-amber-500/40 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer
                        "
                    >
                        <div class="absolute inset-0 rounded-xl border border-white/20 pointer-events-none">
                        </div>

                        <span class="relative z-10 flex items-center justify-center gap-2">
                            Load Next
                        </span>

                        <div class="
                            absolute inset-0 bg-gradient-to-r from-white/0 via-white/30 to-white/0 opacity-0 group-hover:opacity-100 transition-all duration-700 ease-out transform -translate-x-full group-hover:translate-x-full pointer-events-none
                        ">
                        </div>
                    </button>
                </div> -->

                <div id="gridImages"
                    class="
                        grid border border-gray-500 bg-black/10 rounded-xl
                        w-full max-w-4xl mx-auto grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4
                    ">
                </div>

                <div id="gridVideos"
                    class="
                        grid border border-gray-500 bg-black/10 rounded-xl
                        w-full max-w-4xl mx-auto grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4
                    ">
                </div>
            </div>
        </div>
    </div>

    <template id="imageCardTemplate">
        <div class="photo-card relative group bg-gray-800 rounded-xl overflow-hidden shadow-lg border border-gray-700 transition-all duration-300" data-path="">
            <div class="aspect-square flex items-center justify-center bg-gray-900 overflow-hidden">
                <img class="card-image object-contain max-h-full max-w-full" src="" alt="">
            </div>
            <div class="card-status absolute inset-0 flex items-center justify-center bg-black/60 opacity-0 transition-opacity duration-300 pointer-events-none">
                <svg class="card-spinner animate-spin h-8 w-8 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg class="card-success h-10 w-10 text-green-400 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <svg class="card-error h-10 w-10 text-red-400 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            <div class="card-name absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-2 text-xs truncate text-gray-300"></div>
        </div>
    </template>

    <template id="videoCardTemplate">
        <div class="photo-card relative group bg-gray-800 rounded-xl overflow-hidden shadow-lg border border-gray-700 transition-all duration-300" data-path="">
            <div class="aspect-square flex items-center justify-center bg-gray-900 overflow-hidden">
                <video class="card-video object-contain max-h-full max-w-full" src="" preload="metadata" muted playsinline></video>
            </div>
            <div class="card-status absolute inset-0 flex items-center justify-center bg-black/60 opacity-0 transition-opacity duration-300 pointer-events-none">
                <svg class="card-spinner animate-spin h-8 w-8 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg class="card-success h-10 w-10 text-green-400 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <svg class="card-error h-10 w-10 text-red-400 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            <div class="card-name absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-2 text-xs truncate text-gray-300"></div>
        </div>
    </template>
</body>
</html>