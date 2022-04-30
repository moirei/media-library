<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <!-- This is our compiled CSS file which is created by Tailwind CLI -->
    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">

    <title>Authenticate</title>
</head>

<body class="bg-grey-lighter h-screen font-sans">
    <div class="container mx-auto h-full flex justify-center items-center">
        <div class="w-1/2">
            <div class="max-w-md mx-auto bg-white shadow-xl rounded-md overflow-hidden my-8">
                <div class="grid justify-items-stretch mt-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="justify-self-center text-gray-500 h-10 w-10"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <div class="text-2xl font-medium text-center text-gray-600 my-5 py-4">
                    The file(s) you're trying <br>
                    to access is protected.
                </div>
                @if (config('media-library.shared_content.ui.auth_page_links', []))
                    <div class="flex justify-center mb-5 mb-10">
                        @foreach (config('media-library.shared_content.ui.auth_page_links') as $link)
                            <a href="{{ $link['href'] }}"
                                class="flex items-center bg-gray-100 shadow-md border border-gray-200 rounded px-4 py-2 mx-1">
                                <div class="text-indigo-700">{{ $link['title'] }}</div>
                            </a>
                        @endforeach
                    </div>
                @endif
                <div class="bg-gray-200 pt-8 pb-16">
                    <form action="{{ $route }}" method="POST">
                        @csrf
                        <div class="text-center font-medium text-gray-600 mb-6">Enter access credentials</div>
                        <div class="w-4/5 mx-auto">
                            @if ($shareable->access_emails)
                                <div class="flex items-center bg-white rounded shadow-md overflow-hidden mb-4">
                                    <span class="px-3">
                                        <svg class="fill-current text-gray-500 w-4 h-4"
                                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path
                                                d="M18 2a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4c0-1.1.9-2 2-2h16zm-4.37 9.1L20 16v-2l-5.12-3.9L20 6V4l-10 8L0 4v2l5.12 4.1L0 14v2l6.37-4.9L10 14l3.63-2.9z" />
                                        </svg>
                                    </span>
                                    <input class="w-full h-12 focus:outline-none" type="email" name="email"
                                        placeholder="Email" required>
                                </div>
                            @endif
                            <div class="flex items-center bg-white rounded shadow-md overflow-hidden mb-4">
                                <span class="px-3">
                                    <svg class="fill-current text-gray-500 w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20">
                                        <path
                                            d="M4 8V6a6 6 0 1 1 12 0h-3v2h4a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-8c0-1.1.9-2 2-2h1zm5 6.73V17h2v-2.27a2 2 0 1 0-2 0zM7 6v2h6V6a3 3 0 0 0-6 0z" />
                                    </svg>
                                </span>
                                <input class="w-full h-12 focus:outline-none" type="password" name="code"
                                    placeholder="Access code" required-h>
                            </div>
                            <button
                                class="bg-indigo-500 block mx-auto text-white text-sm uppercase rounded shadow-md px-6 py-2"
                                type="submit">Access</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
