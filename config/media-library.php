<?php

return [

    /*
	|--------------------------------------------------------------------------
	| Media Root Folder
	|--------------------------------------------------------------------------
	|
	| Specify a folder all attachments and media
	| storage locations is prepended with.
	| Remove or set to `null` to omit.
	*/
    'folder'  => 'media',

    /*
	|--------------------------------------------------------------------------
	| Clean Ups
	|--------------------------------------------------------------------------
	|
	| Here, specify automatic deletion of unwanted files
	| You can specify file/folder age in days for each type. Example `expired-shareables:29`
	| Available clean types:
	| `empty-folders` - Empty folders with no files
	| `lonely-files` - Files associated with no models
	| `expired-shareables` - Expired shared content
	| `attachments` - Pending attachments
	*/
    'clean_ups' => [
        'schedule' => 'weekly',
        'clean' => [
            'empty-folders',
            'lonely-files:21',
            'attachments:1',
        ],
        'enabled' => false,
    ],

    /*
	|--------------------------------------------------------------------------
	| Clean File Names
	|--------------------------------------------------------------------------
	|
	| Indicate to retouch filename. This is the name used in file path/urls.
	| Recomended when storing PUBLIC images to avoid broken urls in <img> tags with whitespaces or special chars.
	| Set `clean_file_name` to false to disable
	*/
    'clean_file_name' => [
        'replace_spaces' => '-',
        'special_characters' => false,
    ],

    /*
	|--------------------------------------------------------------------------
	| Storage
	|--------------------------------------------------------------------------
	|
	| File and Folder storage settings and defaults.
	| Recomended when storing PUBLIC images to avoid broken urls in <img> tags with whitespaces or special chars.
	| Set `clean_file_name` to false to disable
	*/
    'storage' => [
        # The default sotage preset
        'default' => 'app',

        # Used to create a new storage if none is specified at creation.
        # Accepts all Laravel "Supported Drivers"
        'disk' => env('FILESYSTEM_DISK', 'public'),

        # Controls if files/folders are public vs private
        'private' => false,

        # Preset storages. Can be used anywhere in the app.
        'presets' => [
            'app' => [
                'name' => 'App Storage',
                'location' => 'app',
                'disk' => 'local',
            ]
        ]
    ],

    #
    /*
    |--------------------------------------------------------------------------
    | Image Driver
    |--------------------------------------------------------------------------
    |
    | The engine that should be used to perform image conversions: `gd` or `imagick`
    */
    'driver' => 'gd',

    /*
    |--------------------------------------------------------------------------
    | File Options
    |--------------------------------------------------------------------------
    |
    | Maximum upload size for each type
    | Add `Label` => `max_size` in bytes for needed types to enable limitation
    | If you want to disable the limitation - leave empty array
    */
    'uploads' => [
        # location to uploaded files. Relative to storage "folder" above.
        # Be sure not to use the location for any other storage.
        'location' => 'files',

        # 0 - 100
        'quality' => 90,

        # allowed uploadable file types (mime subtypes)
        'types' => [
            'jpg', 'jpeg', 'png', 'gif', 'svg',
            'xls', 'docx', 'xlsx', 'pdf',
            'mp3', 'mp4',
        ],

        # Maximum upload size for each type
        # Add `Label` => `max_size` in bytes for needed types to enable limitation
        # If you want to disable the limitation - leave empty array
        'max_size' => 2 * 1024 * 1024, // 2MB

        # Configure file attachments for rich-text integration.
        'attachments' => [
            # disk to store
            'disk' => env('FILESYSTEM_DISK', 'public'),

            # location to store attached files. Relative to storage "folder" above.
            # Be sure not to use the location for any other storage.
            'location' => 'attachments',

            # Set maximum upload image size
            'resize' => [1200, null, false, false],

            # Regex pattern to extract embedded richtext attachments
            'richtext_match' => [
                '/<img.*src="(.*?)"/s', # match html img tags
            ],

            # Allowed types
            'types' => [
                'video' => ['mp4'],
                'image' => ['*'],
            ],
        ],

        # Configure image files
        'images' => [

            # Maximum width and height in pixels for the original image [ width, height, x, y]
            // 'crop' => [1200, 1200],

            # Maximum width and height in pixels for the original image [ width, height, upSize, aspectRatio ]
            # upSize {bool} - Crop image even if size will be larger. (If set to `false` - size image will be as original).
            # aspectRatio {bool} - Constrain the current aspect-ratio of the image.
            'resize' => [1200, null, false, false],

            'responsive' => [
                # Crop additional image variations [ width, height, upSize, aspectRatio ]
                'sizes' => [
                    'xsmall' => [38, 38, true, false],
                    'small' => [100, 100, true, false],
                    'thumb' => [270, 270, true, false],
                    'medium' => [572, 572, true, true],
                    'large' => [800, null, true, true],
                ],

                # Disable responsive image generation for image files
                'disabled' => false,
            ],

            'filters' => [
                // \Intervention\Image\Filters\DemoFilter::class,
            ],
        ],

        # File Thumbnails
        # Here you can speficy which non-image files to generate preview thumbnails for.
        # Does not apply to image files.
        # !!Currenly not implemented!!
        'thumbs' => [
            # Used to generate thumbnails for file images
            # Same config as images.resize above.
            'thumb' => [100, 100, true, false],

            # Registered generators for image thumb
            'generators' => [
                // \MOIREI\MediaLibrary\ImageGenerators\Image::class,
                // \MOIREI\MediaLibrary\ImageGenerators\Webp::class,
                // \MOIREI\MediaLibrary\ImageGenerators\Pdf::class,
                // \MOIREI\MediaLibrary\ImageGenerators\Svg::class,
                // \MOIREI\MediaLibrary\ImageGenerators\Video::class,
            ],
        ],
    ],

    # Placeholder for pending or non-image files with image field
    'placeholder' => 'https://via.placeholder.com/32',

    /*
	|--------------------------------------------------------------------------
	| Models
	|--------------------------------------------------------------------------
	|
	| Media library models. Specify other classes for customized behaviour.
	| When using custom models, be sure to extend the current models.
	*/
    'models' => [
        # The media library file model
        'file' => MOIREI\MediaLibrary\Models\File::class,

        # The media library folder model
        'folder' => MOIREI\MediaLibrary\Models\Folder::class,

        # The shared content model
        'shared' => MOIREI\MediaLibrary\Models\SharedContent::class,
    ],

    /*
	|--------------------------------------------------------------------------
	| Route Configuration
	|--------------------------------------------------------------------------
	|
	| Configure the route group parameters.
	| Recommend setting auth or guarding middleware for protected routes
	| Additional configuration for the route group https:#lumen.laravel.com/docs/routing#route-groups
	*/
    'route' => [

        # Default route prefix
        # Set the endpoint prefix to which the media-library server responds.
        # example  `yourdomain.com/media`
        'prefix' => 'media',

        # Subdomain routing
        # see https:#laravel.com/docs/8.x/routing#route-group-subdomain-routing
        # 'domain' => 'media.yourdomain.com',

        # Apply middlewares to routes.
        # String values apply to all routes
        # Key-value pairs may take string or array as value
        'middleware' => [
            'throttle:60,1',
            # 'throttle:uploads',
            'media.protected' => ['auth']
            # 'file' => 'guest',
            # 'file.protected' => ['auth'],
        ],

        # Route name
        'name' => 'media.',

        # Disable all package routes and middleware
        # Alternatetively, set to `false` to disable all routes
        'disabled' => [
            'magic_imager' => false,
            'file_url' => false,
            'file_api' => false,
            'file_share' => false,
        ],
    ],

    /*
	|--------------------------------------------------------------------------
	| Shared Content (defaults)
	|--------------------------------------------------------------------------
	|
	| Configure the default behaviour of shared files and folders.
	*/
    'shared_content' => [

        # UI
        'ui' => [
            'title' => 'Media Library',
            'auth_page_links' => [
                ['title' => 'Contact us', 'href' => '/contact'],
            ],
        ],

        # Share defaults
        'defaults' => [
            # Make shared content publicly accessible to anyone
            'public' => false,

            # Limit the maximum downloads of a shared content
            # Set to 0 to unlimit
            'max_downloads' => 0,

            # Allow users to upload into a shared folder
            'can_upload' => false,

            # Limit the maximum upload size
            # Set to null to unlimit
            'max_upload_size' => 5242880,

            # The allowed file type the user can upload
            'allowed_upload_types' => [
                'image' => ['jpg', 'jpeg', 'png', 'gif', 'svg'],
                'docs' => ['*'],
            ],

            # If the shared content can be removed by the user
            'can_remove' => true,

            # The number of days after creation to automatically expire shared content
            # Set to null to disable
            'expire_after' => 7,
        ],
    ],

    /*
	|--------------------------------------------------------------------------
	| File Types
	|--------------------------------------------------------------------------
	|
	| Used to rename file mimes
	*/
    'types' => [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'svg'],
        'audio' => ['mp3'],
        'video' => ['mp4'],
        'docs' => [
            'doc', 'docx', 'docm', 'dotx', 'dotm', 'docb', # word
            'xls', 'xlsx', 'xlsm', 'xltx', 'xltm', # excel
            'ppt', 'pptx', 'pptm', 'potx', 'potm', 'ppam', 'ppsx', 'ppsm', 'sldx', 'sldm', # power point
            'pdf', 'one',
        ],
        # 'application' => ['gzip', 'json'],
        #'other' => ['*'],
    ],
];
