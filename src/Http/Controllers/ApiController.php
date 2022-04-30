<?php

namespace MOIREI\MediaLibrary\Http\Controllers;

use MOIREI\MediaLibrary\Rules\SharedContentTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use MOIREI\MediaLibrary\Api;
use MOIREI\MediaLibrary\Upload;
use Illuminate\Support\Facades\Storage;
use MOIREI\MediaLibrary\MagicImager;
use MOIREI\MediaLibrary\Models\File;
use MOIREI\MediaLibrary\Models\Folder;
use MOIREI\MediaLibrary\Models\MediaStorage;
use MOIREI\MediaLibrary\Models\SharedContent;

class ApiController extends Controller
{
    /**
     * Get file
     *
     * @param Request $request
     * @param File $file
     * @return \Illuminate\Http\Response
     */
    public function get(Request $request, File $file)
    {
        if ($file->isImage()) {
            $content = MagicImager::fromRequest($request)->get($file);
        } else {
            $content = $file->getContent();
        }

        return response($content, 200)
            ->header('Content-Type', $file->mimetype)
            ->header('Content-Disposition', 'inline');
    }

    /**
     * Get file but ensure public
     *
     * @param Request $request
     * @param File $file
     * @return \Illuminate\Http\Response
     */
    public function getPublic(File $file)
    {
        if ($file->private) {
            abort(401);
        }

        return response($file->getContent(), 200)
            ->header('Content-Type', $file->mimetype)
            ->header('Content-Disposition', 'inline');
    }

    /**
     * Get image but ensure public
     *
     * @param Request $request
     * @param File $file
     * @return \Illuminate\Http\Response
     */
    public function getPublicImage(Request $request, File $file)
    {
        if ($file->private) {
            abort(401);
        }

        if (!$file->isImage()) {
            abort(403);
        }

        $content = MagicImager::fromRequest($request)->get($file);

        return response($content, 200)
            ->header('Content-Type', $file->mimetype)
            ->header('Content-Disposition', 'inline');
    }

    /**
     * Doanload file
     *
     * @param File $file
     * @return \Illuminate\Http\Response
     */
    public function download(File $file)
    {
        return response()->make($file->getContent(), 200, [
            'Content-Type'          => $file->mimetype,
            'Content-Length'        => $file->size,
            'Cache-Control'         => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Disposition'   => "attachment; filename=\"$file->filename\"",
            'Pragma'                => 'public',
        ]);
    }

    /**
     * Doanload file but ensure public
     *
     * @param File $file
     * @return \Illuminate\Http\Response
     */
    public function downloadPublic(File $file)
    {
        if ($file->private) {
            abort(401);
        }

        return $this->download($file);
    }

    /**
     * Stream file
     *
     * @param File $file
     * @return \Illuminate\Http\Response
     */
    public function stream(File $file)
    {
        $path = $file->path();
        $disk = $file->disk();

        return response()->stream(function () use ($path, $disk) {
            $stream = Storage::disk($disk)->readStream($path);
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type'          => $file->mimetype,
            'Content-Length'        => Storage::disk($disk)->size($path),
        ]);
    }

    /**
     * Stream file but ensure public
     *
     * @param File $file
     * @return \Illuminate\Http\Response
     */
    public function streamPublic(File $file)
    {
        if ($file->private) {
            abort(401);
        }

        return $this->stream($file);
    }

    /**
     * Upload file
     *
     * @param  \Illuminate\Http\Request $request
     * @param  MediaStorage $storage
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request, MediaStorage $storage)
    {
        $request->validate([
            'file' => 'required|file',
            'name' => 'max:64',
            'location' => 'max:128',
            'description' => 'max:1024',
            'private' => 'boolean',
        ]);

        $upload = Upload::make($request->file('file'))
            ->storage($storage);

        if (config('media-library.clean_file_name'))
            $upload->cleanFilename(config('media-library.clean_file_name.special_characters', false));

        if ($request->has('location')) {
            $upload->location($request->get('location'));
        }
        if ($request->has('name')) {
            $upload->name($request->get('name'));
        }
        if ($request->has('description')) {
            $upload->description($request->get('description'));
        }
        if ($request->has('private')) {
            $upload->private($request->get('private'));
        }
        if ($request->has('disk')) {
            $upload->disk($request->get('disk'));
        }

        return response()->json(
            $upload->save()
        );
    }

    /**
     * Update file
     *
     * @param  \Illuminate\Http\Request $request
     * @param File $file
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, File $file)
    {
        $request->validate([
            'name' => 'max:64',
            'description' => 'max:1024',
            'private' => 'boolean',
        ]);

        $file->update(
            $request->only(['name', 'description', 'private'])
        );
        $file->fresh();

        return response()->json($file);
    }

    /**
     * Move file
     *
     * @param  \Illuminate\Http\Request  $request
     * @param File $file
     * @return \Illuminate\Http\JsonResponse
     */
    public function move(Request $request, File $file)
    {
        $request->validate([
            'location' => 'required|string'
        ]);

        $location = $request->get('location');

        if (Api::isUuid($location)) {
            $folderClass = config('media-library.models.folder');
            $location = $folderClass::findOrFail($location);
        }
        $file->storage->move($file, $location);

        $file->fresh();

        return response()->json($file);
    }

    /**
     * Delete file
     *
     * @param  \Illuminate\Http\Request  $request
     * @param File $file
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, File $file)
    {
        $request->validate([
            'force' => 'boolean'
        ]);

        if ($request->get('force', false)) {
            $file->forceDelete();
        } else {
            $file->delete();
        }

        return response()->json($file);
    }

    /**
     * Create Folder
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  MediaStorage $storage
     * @return \Illuminate\Http\JsonResponse
     */
    public function folderCreate(Request $request, MediaStorage $storage)
    {
        $request->validate([
            'name' => 'required|max:64',
            'location' => 'max:128',
            'description' => 'max:1024',
            'private' => 'boolean',
        ]);

        $location = $request->get('location');
        $folderClass = config('media-library.models.folder');

        if (Api::isUuid($location)) {
            $location = $folderClass::findOrFail($location);
        }

        $data = $request->only(['name', 'description', 'private']);
        $data['location'] = $location;
        $folder = $storage->createFolder($data);

        return response()->json($folder);
    }

    /**
     * Update Folder
     *
     * @param  \Illuminate\Http\Request  $request
     * @param Folder $folder
     * @return \Illuminate\Http\JsonResponse
     */
    public function folderUpdate(Request $request, Folder $folder)
    {
        $request->validate([
            'name' => 'max:64',
            'location' => 'max:128',
            'description' => 'max:1024',
            'private' => 'boolean',
        ]);

        $folder->update(
            $request->only(['name', 'description', 'private'])
        );
        $folder->fresh();

        return response()->json($folder);
    }

    /**
     * Move Folder
     *
     * @param  \Illuminate\Http\Request  $request
     * @param Folder $folder
     * @return \Illuminate\Http\JsonResponse
     */
    public function folderMove(Request $request, Folder $folder)
    {
        $request->validate([
            'location' => 'required|string'
        ]);
        $location = $request->get('location');

        if (Api::isUuid($location)) {
            $folderClass = config('media-library.models.folder');
            $location = $folderClass::findOrFail($location);
        }

        $folder->storage->moveFolder($folder, $location);
        $folder->fresh();

        return response()->json($folder);
    }

    /**
     * Delete Folder
     *
     * @param  \Illuminate\Http\Request  $request
     * @param Folder $folder
     * @return \Illuminate\Http\JsonResponse
     */
    public function folderDelete(Request $request, Folder $folder)
    {
        $request->validate([
            'force' => 'boolean'
        ]);

        if ($request->get('force', false)) {
            $folder->forceDelete();
        } else {
            $folder->delete();
        }

        return response()->json($folder);
    }

    /**
     * Browse file location
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  MediaStorage $storage
     * @return \Illuminate\Http\JsonResponse
     */
    public function browse(Request $request, MediaStorage $storage)
    {
        $request->validate([
            'location' => 'string|max:32',
            'filesOnly' => 'boolean',
            'type' => 'string|max:12',
            'mime' => 'string|max:12',
            'private' => 'boolean',
        ]);

        $location = $request->get('location');
        $options = $request->only(['filesOnly', 'type', 'mime', 'private']);

        return response()->json(
            $storage->browse($location, $options)
        );
    }

    /**
     * Get file downloadable link
     *
     * @param File $file
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function downloadableLink(Request $request, File $file)
    {
        $min_ttl = 60 * 30; // 30 mins
        $request->validate([
            'ttl' => "integer|min:$min_ttl",
        ]);
        $ttl = now()->addSeconds($request->get('ttl', $min_ttl));

        return response()->json([
            'url' => $file->dowloadUrl($ttl)
        ]);
    }

    /**
     * Get file shareable link
     *
     * @param Folder|File $file
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function shareablebleLink(Request $request, Folder|File $file)
    {
        $request->validate([
            'name' =>                 'string|max:64',
            'description' =>          'string|max:1024',
            'public' =>               'boolean',
            'access_emails' =>        'array|min:1',
            "access_emails.*"  =>     'distinct|email',
            'access_type' =>          SharedContentTypes::class,
            "access_keys"    =>       'exclude_if:public,true|required|array|min:1',
            "access_keys.*"  =>       'exclude_if:public,true|required|string|distinct|min:6',
            'expires_at' =>           'date|after:today',
            'can_remove' =>           'boolean',
            'can_upload' =>           'boolean',
            'max_downloads' =>        'integer|min:1',
            'max_upload_size' =>      'integer|min:1000',
            'allowed_upload_types' => 'array',
            'meta' => 'json',
        ]);

        $shareable = SharedContent::make($file);
        $shareable->fill(
            $request->only([
                'name', 'description',
                'public', 'access_emails', 'access_type', 'access_keys',
                'expires_at', 'can_remove', 'can_upload',
                'max_downloads', 'max_upload_size', 'allowed_upload_types',
            ])
        );

        $shareable->save();
        $shareable->fresh();

        return response()->json([
            'id' => $shareable->id,
            'url' => $shareable->url(),
        ]);
    }
}
