<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <!-- This is our compiled CSS file which is created by Tailwind CLI -->
    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">

    <title>{{ config('media-library.shared_content.ui.title') }}</title>
</head>

<body class="bg-grey-lighter h-screen font-sans">
    <div class="bg-gray-100 dark:bg-gray-900 dark:text-white text-gray-600 h-screen flex overflow-hidden text-sm">
        <div
            class="bg-white dark:bg-gray-900 dark:border-gray-800 w-20 flex-shrink-0 border-r border-gray-200 flex-col hidden sm:flex">
            <div class="h-16 text-blue-500 flex items-center justify-center">
                <svg enable-background="new 0 0 512 512" class="w-9" viewBox="0 0 512 512"
                    xmlns="http://www.w3.org/2000/svg">
                    <g>
                        <g>
                            <g>
                                <g>
                                    <g>
                                        <g>
                                            <g>
                                                <circle cx="256" cy="256" fill="#1dd882" r="256" />
                                            </g>
                                        </g>
                                    </g>
                                </g>
                            </g>
                        </g>
                        <path
                            d="m512 256c0-20.388-2.384-40.22-6.887-59.231l-70.113-70.113-358 258.688 119.769 119.769c19.011 4.503 38.843 6.887 59.231 6.887 141.385 0 256-114.615 256-256z"
                            fill="#06b26b" />
                        <g>
                            <path d="m77 126.656h358v258.689h-358z" fill="#fff" />
                        </g>
                        <g>
                            <path d="m256 126.656h179v258.689h-179z" fill="#e9edf5" />
                        </g>
                        <g>
                            <path d="m107.62 159.673h296.759v192.653h-296.759z" fill="#7584f2" />
                        </g>
                        <g>
                            <path d="m256 159.673h148.38v192.654h-148.38z" fill="#4855b7" />
                        </g>
                        <g>
                            <path
                                d="m107.62 352.327v-42.265l62.121-68.416 48.869 54.733 97.737-105.556 88.033 80.752v80.752z"
                                fill="#a9baff" />
                        </g>
                        <g>
                            <path d="m404.38 271.575-88.033-80.752-60.347 65.175v96.329h148.38z" fill="#7584f2" />
                        </g>
                        <g>
                            <circle cx="212.286" cy="210.23" fill="#ffce00" r="28.427" />
                        </g>
                    </g>
                </svg>
            </div>
            <div class="flex mx-auto flex-grow mt-4 flex-col text-gray-400 space-y-4">
                <button
                    class="h-10 w-12 dark:text-gray-500 rounded-md flex items-center justify-center bg-blue-100 text-blue-500">
                    <svg viewBox="0 0 24 24" class="h-5" stroke="currentColor" stroke-width="2" fill="none"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                </button>
                {{-- <button class="h-10 w-12 dark:text-gray-500 rounded-md flex items-center justify-center">
                    <svg viewBox="0 0 24 24" class="h-5" stroke="currentColor" stroke-width="2" fill="none"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                        <line x1="12" y1="11" x2="12" y2="17"></line>
                        <line x1="9" y1="14" x2="15" y2="14"></line>
                    </svg>
                </button>
                <button class="h-10 w-12 dark:text-gray-500 rounded-md flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path fill-rule="evenodd" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                </button> --}}
            </div>
        </div>
        <div class="flex-grow overflow-hidden h-full flex flex-col">
            <div class="h-16 lg:flex w-full border-b border-gray-200 dark:border-gray-800 hidden px-10">
                <div class="flex h-full text-gray-600 dark:text-gray-400">
                    <h1 class="font-semibold text-lg h-full inline-flex items-center mr-8">
                        {!! config('media-library.shared_content.ui.title') !!}</h1>
                </div>
                <div class="ml-auto flex items-center">
                    @if ($is_auth)
                        <a href="{{ $signout_url }}"
                            class="py-2 px-3 rounded-md shadow space-x-3 flex items-center bg-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            <span class="hidden md:inline">Signout</span>
                        </a>
                    @endif
                </div>
            </div>
            <div class="flex-grow flex overflow-x-hidden">
                <div
                    class="xl:w-72 w-48 flex-shrink-0 border-r border-gray-200 dark:border-gray-800 h-full overflow-y-auto lg:block hidden p-5">
                    <div class="relative mt-2">
                        <input type="text" id="search"
                            class="pl-8 h-9 bg-transparent border border-gray-300 dark:border-gray-700 dark:text-white w-full rounded-md text-sm"
                            placeholder="Search" />
                        <svg viewBox="0 0 24 24"
                            class="w-4 absolute text-gray-400 top-1/2 transform translate-x-0.5 -translate-y-1/2 left-2"
                            stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"
                            stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </div>
                    <div class="space-y-4 mt-10">
                        @if (count($folders))
                            <div class="text-xs text-gray-400 tracking-wider">Folders</div>
                        @endif
                        @foreach ($folders as $folder)
                            <button class="bg-white p-3 w-full flex flex-col rounded-md dark:bg-gray-800 shadow">
                                <div
                                    class="flex xl:flex-row flex-col items-center font-medium text-gray-900 dark:text-white pb-2 mb-2 xl:border-b border-gray-200 border-opacity-75 dark:border-gray-700 w-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-linejoin="round">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z" />
                                    </svg>
                                    {{ $folder->name }}
                                </div>
                                <div class="flex items-center w-full">
                                    <div class="ml-auto text-xs text-gray-400">{{ count($folder->files) }} files</div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
                <div class="flex-grow bg-white dark:bg-gray-900 overflow-y-auto">
                    <div
                        class="py-4 sm:px-7 sm:pt-7 px-4 flex flex-col w-full border-b border-gray-200 bg-white dark:bg-gray-900 dark:text-white dark:border-gray-800 sticky top-0">
                        <div class="flex w-full items-center">
                            <div class="ml-auto sm:flex hidden items-center justify-end">
                                <div class="text-right">
                                    <div class="text-xs text-gray-400 dark:text-gray-400">
                                        Total size:
                                        <span class="text-gray-900 text-md dark:text-white ml-2">
                                            {{ round($total_size, 2) }}MB
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="py-3">
                        <table class="w-full text-left mt-0">
                            <thead>
                                <tr class="text-gray-400 border-b border-gray-200 dark:border-gray-800">
                                    <th class="font-normal px-3 pt-0 pb-3"></th>
                                    <th class="font-normal px-3 pt-0 pb-3">Name</th>
                                    <th class="font-normal px-3 pt-0 pb-3 md:table-cell">Description</th>
                                    <th class="font-normal px-3 pt-0 pb-3">Size</th>
                                    <th class="font-normal px-3 pt-0 pb-3 sm:text-gray-400 text-white">Uploaded</th>
                                </tr>
                            </thead>
                            <tbody class="item-list text-gray-600 dark:text-gray-100">
                                @foreach ($files as $file)
                                    <tr class=" border-b border-gray-200 dark:border-gray-800">
                                        <td class="pl-2 py-2">
                                            <div class="flex items-center">
                                                <svg viewBox="0 0 24 24"
                                                    class="w-7 h-7 p-1.5 mr-2.5 mr-5 rounded-lg border border-gray-200 dark:border-gray-800"
                                                    stroke="currentColor" stroke-width="3" fill="none"
                                                    stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                                    <line x1="1" y1="10" x2="23" y2="10"></line>
                                                </svg>
                                                <img src="{{ data_get($file->image, config('media-library.uploads.images.thumb', 'thumb')) }}"
                                                    class="w-14 h-14 mr-2 rounded-full border border-gray-200 dark:border-gray-800"
                                                    alt="profile" />
                                            </div>
                                        </td>
                                        <td class="sm:p-3 py-2 px-1">
                                            <div class="flex items-center">

                                                <div class="sm:flex hidden flex-col">
                                                    {{ $file->name }}
                                                    <div class="text-gray-400 text-xs">{{ $file->filename }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="sm:p-3 py-2 px-1 md:table-cell hidden">
                                            @if ($file->description)
                                                {{ $file->description }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="sm:p-3 py-2 px-1">
                                            {{ round($file->size / ($file->size < 9e5 ? 1e3 : 1e6), 2) }}
                                            {{ $file->size < 9e5 ? 'k' : 'M' }}B
                                        </td>
                                        <td class="sm:p-3 py-2 px-1">
                                            <div class="flex items-center">
                                                <div class="sm:flex hidden flex-col">
                                                    {{ $file->created_at->toFormattedDateString() }}
                                                    <div class="text-gray-400 text-xs">
                                                        {{ $file->created_at->diffForHumans() }}</div>
                                                </div>
                                                <button
                                                    class="w-8 h-8 inline-flex items-center justify-center text-gray-400 ml-auto">
                                                    <svg viewBox="0 0 24 24" class="w-5"
                                                        stroke="currentColor" stroke-width="2" fill="none"
                                                        stroke-linecap="round" stroke-linejoin="round">
                                                        <circle cx="12" cy="12" r="1"></circle>
                                                        <circle cx="19" cy="12" r="1"></circle>
                                                        <circle cx="5" cy="12" r="1"></circle>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="absolute bottom-10 right-10 flex">
                            @if ($can_upload)
                                <form action="{{ $upload_url }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="file" name="file" id="file" hidden onchange="form.submit()" />
                                    <label for="file"
                                        class="flex items-center px-3 py-2 space-x-3 text-gray-900 transition-colors duration-200 transform border rounded-lg dark:text-gray-200 dark:border-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path fill-rule="evenodd" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>
                                        <span class="hidden md:inline">Upload</span>
                                    </label>
                                </form>
                            @endif
                            @if ($can_download)
                                <a href="{{ $download_url }}" target="_blank"
                                    class="ml-2 flex items-center px-3 py-2 space-x-3 text-gray-900 transition-colors duration-200 transform border rounded-lg dark:text-gray-200 dark:border-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none">
                                    <svg viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                        <path fill-rule="evenodd"
                                            d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="hidden md:inline">Download All</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
<style>
    .hidden-item {
        display: none;
    }

</style>

<script>
    var input = document.querySelector('#search');
    var items = document.querySelector('.item-list').getElementsByTagName('tr');

    input.addEventListener('keyup', function(ev) {
        var text = ev.target.value;
        var pat = new RegExp(text, 'i');
        for (var i = 0; i < items.length; i++) {
            var item = items[i];
            if (pat.test(item.innerText)) {
                item.classList.remove("hidden-item");
            } else {
                item.classList.add("hidden-item");
            }
        }
    });
</script>

</html>
