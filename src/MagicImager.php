<?php

namespace MOIREI\MediaLibrary;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Image;
use Intervention\Image\ImageCache;
use Intervention\Image\ImageManager;
use MOIREI\MediaLibrary\Models\File;
use MOIREI\MediaLibrary\Rules\HexColor;

final class MagicImager
{
    /**
     * Transform options
     * @var array
     */
    protected array $options;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * Make a new MagicImager
     *
     * @param array $options
     * @return MagicImager
     */
    public static function make(array $options)
    {
        return new MagicImager($options);
    }

    /**
     * Make an instance from request options
     *
     * @param  \Illuminate\Http\Request  $request
     * @return MagicImager
     */
    public static function fromRequest(Request $request)
    {
        //  Alternate name maps.
        $map = [
            'w' => 'width',
            'h' => 'height',
            'bg_colour' => 'background',
            'bright' => 'brightness',
            'pixellate' => 'pixelate',
            'greyscale' => 'grey',
            'gray' => 'grey',
            'f' => 'func',
            'q' => 'quality',
            'force_format' => 'convert',
        ];

        $options = $request->except(array_keys($map));

        $func = Arr::pull($options, 'func', []);
        $options['func'] = [];
        if (!is_array($func)) $func = [$func];
        foreach ($func as $f) {
            if (in_array($f, [
                'grey', 'gray', 'greyscale',
            ])) {
                $options['func'][] = 'grey';
            } elseif (in_array($f, [
                'crop', 'invert', 'fit', 'trim',
            ])) {
                $options['func'][] = $f;
            } else {
                if (Str::contains($f, ':')) {
                    [$f, $args] = explode(':', $f);
                    $options[$f] = explode(',', $args);
                } else {
                    $options[$f] = [];
                }
            }
        }

        foreach ($map as $key => $name) {
            if ($request->has($key)) {
                $options[$name] = $request->get($key);
            }
        }

        // transform colours
        if (Arr::has($options, 'background')) Arr::set($options, 'background', static::adjustColor(Arr::get($options, 'background')));
        if (Arr::has($options, 'border.1')) Arr::set($options, 'border.1', static::adjustColor(Arr::get($options, 'border.1')));

        return static::make($options);
    }

    /**
     * Get the filtered image for File
     *
     * @param string|File $file
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function get(string|File $file)
    {
        /** @var File */
        $file = is_string($file) ? File::findOrFail($file) : $file;
        if ($file->type != 'image') {
            throw new \Exception('File is not an image type');
        }

        $this->validate(true);

        $manager = new ImageManager(['driver' => config('media-library.driver', 'gd')]);
        $content = Storage::disk($file->disk())->get($file->uri());

        return $manager->cache(function (Image|ImageCache $image) use ($content) {
            $image = $image->make($content);

            $this->applyFlip($image);
            $this->applyGreyscale($image);
            $this->applyFunctions($image);
            $this->applyResize($image);
            $this->applyCircle($image);
            $this->applyText($image);
            $this->applyBrightness($image);
            $this->applyContrast($image);
            $this->applyBlur($image);
            $this->applySharpness($image);
            $this->applyPixelate($image);

            if (Arr::has($this->options, 'convert')) {
                $quality = Arr::get($this->options, 'quality', config('media-library.uploads.quality'));
                return $image->encode($this->options['convert'], $quality);
            }
        });
    }

    /**
     * Get options as array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->options;
    }

    /**
     * Apply text option
     *
     * @param Image|ImageCache $image
     */
    protected function applyFlip(Image|ImageCache $image)
    {
        if ($direction = Arr::get($this->options, 'flip')) {
            $image->flip($direction);
        }
    }

    /**
     * Apply text option
     *
     * @param Image|ImageCache $image
     */
    protected function applyText(Image|ImageCache $image)
    {
        if ($text = Arr::get($this->options, 'text')) {
            $image->text(...$text);
        }
    }

    /**
     * Apply circle option
     *
     * @param Image|ImageCache $image
     */
    protected function applyCircle(Image|ImageCache $image)
    {
        if ($circle = Arr::has($this->options, 'circle')) {
            $image->circle(
                Arr::get($circle, 0),
                Arr::get($circle, 1),
                Arr::get($circle, 2),
                function ($draw) {
                    if ($background = Arr::get($this->options, 'background')) {
                        $draw->background($background);
                    }
                    if ($border = Arr::get($this->options, 'border')) {
                        $draw->border(...$border);
                    }
                }
            );
        }
    }

    /**
     * Apply resize option
     *
     * @param Image|ImageCache $image
     */
    protected function applyResize(Image|ImageCache $image)
    {
        $width = Arr::get($this->options, 'width');
        $height = Arr::get($this->options, 'height');
        if ($width || $height) {
            $func = Arr::get($this->options, 'func', []);
            if (in_array('crop', $func)) {
                $image->crop($width, $height);
            } elseif (in_array('fit', $func)) {
                $image->fit(
                    $width,
                    $height,
                    function ($constraint) {
                        $constraint->upsize();
                    },
                );
            } else {
                $image->resize($width, $height, function ($constraint) use ($width, $height) {
                    if (!$width or !$height) $constraint->aspectRatio();
                    // if (!$upSize) $constraint->upsize();
                });
            }
        } elseif ($crop = Arr::get($this->options, 'crop')) {
            $image->crop(...$crop);
        } elseif (Arr::has($this->options, 'fit')) {
            $image->fit(
                Arr::get($this->options, 'fit.0'),
                Arr::get($this->options, 'fit.1'),
                function ($constraint) {
                    $constraint->upsize();
                },
                Arr::get($this->options, 'fit.2', 'center')
            );
        }
    }

    /**
     * Apply brightness option
     *
     * @param Image|ImageCache $image
     */
    protected function applyBrightness(Image|ImageCache $image)
    {
        if ($brightness = Arr::get($this->options, 'brightness')) {
            $image->brightness($brightness);
        }
    }

    /**
     * Apply contrast option
     *
     * @param Image|ImageCache $image
     */
    protected function applyContrast(Image|ImageCache $image)
    {
        if ($contrast = Arr::get($this->options, 'contrast')) {
            $image->contrast($contrast);
        }
    }

    /**
     * Apply blur option
     *
     * @param Image|ImageCache $image
     */
    protected function applyBlur(Image|ImageCache $image)
    {
        if ($blur = Arr::get($this->options, 'blur')) {
            $image->blur($blur);
        }
    }

    /**
     * Apply sharpness option
     *
     * @param Image|ImageCache $image
     */
    protected function applySharpness(Image|ImageCache $image)
    {
        if ($sharpness = Arr::get($this->options, 'sharp')) {
            $image->sharpen($sharpness);
        }
    }

    /**
     * Apply greyscale
     *
     * @param Image|ImageCache $image
     */
    protected function applyGreyscale(Image|ImageCache $image)
    {
        if (Arr::get($this->options, 'grey')) {
            $image->greyscale();
        } else {
            if ($functions = Arr::get($this->options, 'func')) {
                if (in_array('grey', $functions)) {
                    $image->greyscale();
                }
            }
        }
    }

    /**
     * Apply functions
     *
     * @param Image|ImageCache $image
     */
    protected function applyFunctions(Image|ImageCache $image)
    {
        if ($functions = Arr::get($this->options, 'func')) {
            // except resize functions
            foreach (Arr::except($functions, ['crop', 'fit']) as $func) {
                $image->$func();
            }
        }
    }

    /**
     * Apply pixelation option
     *
     * @param Image|ImageCache $image
     */
    protected function applyPixelate(Image|ImageCache $image)
    {
        if ($pixelation = Arr::get($this->options, 'pixelate')) {
            $image->pixelate($pixelation);
        }
    }

    /**
     * Validate the upload
     *
     * @param bool $assert
     * @throws \Illuminate\Validation\ValidationException
     * @return bool
     */
    public function validate(bool $assert = false)
    {
        $validator = Validator::make($this->options, [
            'background' => new HexColor(),
            'border.0' => 'int|max:1000',
            'border.1' => new HexColor(),
            'circle.0' => 'int|max:1000', // Diameter of the circle in pixels.
            'circle.1' => 'int', // x-coordinate of the center.
            'circle.2' => 'int', // x-coordinate of the center.
            'crop.0' => 'int|max:1000', // Width of the rectangular cutout.
            'crop.1' => 'int|required_with:crop.0|max:1000', // Height of the rectangular cutout.
            'crop.2' => 'int', // X-Coordinate of the top-left corner if the rectangular cutout
            'crop.3' => 'int', // Y-Coordinate of the top-left corner if the rectangular cutout
            'flip' => 'in:v,h',
            'width' => 'int|max:1000',
            'height' => 'int|max:1000',
            'brightness' => 'int|min:-100|max:100',
            'contrast' => 'int|max:100',
            'blur' => 'int|min:0|max:100',
            'grey' => 'int|min:0|max:1',
            'fit.0' => 'int|max:1000', // The width the image will be resized to after cropping out the best fitting aspect ratio.
            'fit.1' => 'int|max:1000', // The height the image will be resized to after cropping out the best fitting aspect ratio.
            'fit.2' => 'in:' . implode(',', [
                'top-left',
                'top',
                'top-right',
                'left',
                'center',
                'right',
                'bottom-left',
                'bottom',
                'bottom-right'
            ]), // Set a position where cutout will be positioned.
            'func.*' => 'distinct|in:crop,grey,invert,fit,trim',
            'sharp' => 'int|max:100',
            'quality' => 'int|min:0|max:100',
            'convert' => 'in:' . implode(',', [
                'jpg', 'png', 'gif',
                'tif', 'bmp', 'ico', 'psd',
                'webp', 'data-url'
            ]),
            'pixelate' => 'int|min:0|max:100',
            'trim.0' => 'in:' . implode(',', ['top-left', 'bottom-right', 'transparent']), // base
            'trim.1' => 'in:' . implode(',', ['top', 'bottom', 'left', 'right']), // away
            'trim.2' => 'int|min:0|max:100', // tolerance - Define a percentaged tolerance level between 0 and 100 to trim away similar color values
            'trim.3' => 'int|min:-1000|max:1000', // feather
            'text.0' => 'max:100', // The text string that will be written to the image.
            'text.1' => 'int', // x-ordinate defining the basepoint of the first character
            'text.2' => 'int', // y-ordinate defining the basepoint of the first character
        ]);

        if ($assert) {
            $validator->validate();
        }

        return !$validator->fails();
    }

    /**
     * Color options allow omitting `#` in hex color codes.
     * Predend if missing.
     *
     * @param string $color
     * @return string
     */
    protected static function adjustColor(string $color): string
    {
        if ((strlen($color) === 6) && Str::startsWith($color, '#')) {
            $color = '#' . $color;
        }
        return $color;
    }
}
