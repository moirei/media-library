<?php

namespace MOIREI\MediaLibrary\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use MOIREI\MediaLibrary\Models\Attachment;
use MOIREI\MediaLibrary\Upload;

class AttachmentController extends Controller
{
    /**
     * Store an attachment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $upload = Upload::attachment(
            $request->file('file'),
        );

        if (config('media-library.clean_file_name'))
            $upload->cleanFilename(config('media-library.clean_file_name.special_characters', false));

        $attachment = $upload->save();

        return response()->json([
            'id' => $attachment->id,
            'alt' => $attachment->alt,
            'url' => $attachment->url,
        ]);
    }

    /**
     * Purge persisted or pending attachment by URL.
     *
     * @param  string $url
     * @return \Illuminate\Http\Response
     */
    public function destroy(string $url)
    {
        $attachment = Attachment::get($url);
        if (!$attachment) {
            abort(404);
        }

        $attachment->purge();

        return response()->json([
            'id' => $attachment->id,
            'alt' => $attachment->alt,
            'url' => $attachment->url,
        ]);
    }
}
