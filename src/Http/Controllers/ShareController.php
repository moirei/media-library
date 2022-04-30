<?php

namespace MOIREI\MediaLibrary\Http\Controllers;

use ZipArchive;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use MOIREI\MediaLibrary\Models\SharedContent;
use Illuminate\Support\Facades\Validator;
use MOIREI\MediaLibrary\Api;
use MOIREI\MediaLibrary\Upload;

class ShareController extends Controller
{
    /**
     * Get shareable content view
     *
     * @param SharedContent $shareable
     * @return \Illuminate\Http\Response
     */
    public function get(SharedContent $shareable)
    {
        if (Api::isFolder($shareable->shareable)) {
            $files = $shareable->shareable->files;
            $folders = $shareable->shareable->folders;
        } else {
            $files = collect([$shareable->shareable]);
            $folders = [];
        }

        $route_name = config('media-library.route.name', '');
        $total = $files->sum('size');

        return view('media-library::content')->with([
            'total_size' => $total / 1e6,
            'is_auth' => !!count($shareable->access_keys),
            'files' => $files,
            'folders' => $folders,
            'can_remove' => $shareable->can_remove,
            'can_upload' => $shareable->can_upload && Api::isFolder($shareable->shareable),
            'can_download' => ($shareable->max_downloads === -1) || ($shareable->max_downloads < $shareable->downloads),
            'upload_url' => route("${route_name}share.upload", ['shared' => $shareable->id]),
            'download_url' => route("${route_name}share.download", ['shared' => $shareable->id]),
            'signout_url' => route("${route_name}share.signout", ['shared' => $shareable->id]),
        ]);
    }

    /**
     * Get authorization view
     *
     * @return \Illuminate\Http\Response
     */
    public function auth(SharedContent $shareable)
    {
        $route_name = config('media-library.route.name', '');

        return view('media-library::auth')
            ->with([
                'shareable' => $shareable,
                'route' => route("${route_name}share.auth.post", ['shared' => $shareable->id]),
            ]);
    }

    /**
     * Submit authorization credentials
     *
     * @param  \Illuminate\Http\Request $request
     * @param SharedContent $shareable
     * @return \Illuminate\Http\Response
     */
    public function postAuth(Request $request, SharedContent $shareable)
    {
        $rules = [
            'code' => 'required',
        ];

        if (count($shareable->access_emails)) {
            $rules['email'] = 'required|email';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        if (!$this->validateAccess($shareable, $request->only(['email', 'code']))) {
            abort(401);
        }

        $request->session()->put("authenticated:$shareable->id", time());
        $name = config('media-library.route.name');

        return redirect()->intended(
            route("${name}share", ['shared' => $shareable->id])
        );
    }

    /**
     * Download shareable content
     *
     * @param  \Illuminate\Http\Request $request
     * @param SharedContent $shareable
     * @return \Illuminate\Http\Response
     */
    public function download(SharedContent $shareable)
    {
        if (($shareable->max_downloads != 0) and ($shareable->downloads >= $shareable->max_downloads)) {
            abort(403);
        }

        if (Api::isFolder($shareable->shareable)) {
            $name = $shareable->name;
            if (is_null($name)) $name = Arr::get($shareable, 'shareable.name');
            if (is_null($name)) $name = 'download';
            $zip_file = sys_get_temp_dir() . '/' . $name . time() . '.zip';
            $zip = new ZipArchive();
            $zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            foreach ($shareable->shareable->files as $file) {
                $content = Storage::disk($file->disk())->get($file->uri());
                $zip->addFromString($file->filename, $content);
            }
            $zip->close();

            $response = response()->download($zip_file);
        } else {
            $response = app(ApiController::class)->download($shareable->shareable);
        }

        $shareable->downloads++;
        $shareable->save();

        return $response;
    }

    /**
     * Upload into shareable content space
     *
     * @param  \Illuminate\Http\Request $request
     * @param SharedContent $shareable
     */
    public function upload(Request $request, SharedContent $shareable)
    {
        if (!Api::isFolder($shareable->shareable) || !$shareable->can_upload) {
            abort(403);
        }

        $request->validate([
            'file' => 'required|file',
        ]);

        // TODO: include current folder size
        $max_upload_size = config('media-library.shared_content.defaults.max_upload_size', 5242880);
        $allowed_upload_types = config('media-library.shared_content.defaults.allowed_upload_types', []);

        $upload = Upload::make($request->file('file'))
            ->maxSize($max_upload_size)
            ->allow($allowed_upload_types);

        if (config('media-library.clean_file_name'))
            $upload->cleanFilename(config('media-library.clean_file_name.special_characters', false));

        $upload->folder($shareable->shareable);
        $upload->save();

        return redirect()->back();
    }

    /**
     * @param SharedContent $shareable
     * @param  array  $credentials
     * @return bool
     */
    public function validateAccess(SharedContent $shareable, array $credentials)
    {
        $requires_email = !!count($shareable->access_emails);
        $email = Arr::get($credentials, 'email');
        $code = $key =  Arr::get($credentials, 'code');
        $auth = false;

        if ($shareable->access_type === SharedContent::ACCESS_TYPE_TOKEN) {
            $access_keys = array_map(fn ($key) => SharedContent::hashKey($key), $shareable->access_keys);
        } else {
            $access_keys = $shareable->access_keys;
        }
        foreach ($access_keys as $key) {
            if (Hash::check($code, $key)) {
                $auth = true;
                break;
            }
        }

        if ($requires_email) {
            $auth = $auth && in_array($email, $shareable->access_emails);
        }

        return $auth;
    }

    /**
     * @param  \Illuminate\Http\Request $request
     * @param SharedContent $shareable
     * @return \Illuminate\Http\Response
     */
    public function signout(Request $request, SharedContent $shareable)
    {
        $request->session()->forget("authenticated:$shareable->id");
        $name = config('media-library.route.name');

        return redirect(
            route("${name}share", ['shared' => $shareable->id])
        );
    }
}
