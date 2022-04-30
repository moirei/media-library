<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MOIREI\MediaLibrary\Models\SharedContent;

class CreateMediaTables extends Migration
{
    public function up()
    {
        Schema::create('storages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('location');
            $table->text('description')->nullable();
            $table->string('disk');
            $table->boolean('private')->default(false);
            $table->unsignedBigInteger('capacity')->nullable();
            $table->json('meta')->nullable();

            $table->string('model_type')->nullable();
            $table->string('model_id')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->unique(['location', 'name', 'disk']);
        });

        Schema::create('folders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->boolean('private')->default(false);
            $table->json('meta')->nullable();

            $table->uuid('parent_id')->nullable();
            $table->uuid('storage_id');
            $table->foreign('storage_id')->references('id')->on('storages')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
            $table->unique(['location', 'name', 'storage_id']);
        });

        Schema::table('folders', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('folders')->onDelete('cascade');
        });

        Schema::create('files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('fqfn')->unique()->index(); // fully qualified file name
            $table->string('name');
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->boolean('private')->default(false);
            $table->string('filename');
            $table->string('mime');
            $table->string('mimetype');
            $table->string('type');
            $table->string('extension')->nullable();
            $table->unsignedBigInteger('size');
            $table->unsignedBigInteger('original_size');
            $table->unsignedBigInteger('total_size');
            $table->json('meta')->nullable();
            $table->json('image')->nullable();
            $table->json('responsive')->nullable();

            $table->string('model_type')->nullable();
            $table->string('model_id')->nullable();

            $table->uuid('folder_id')->nullable();
            $table->uuid('storage_id');
            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');
            $table->foreign('storage_id')->references('id')->on('storages')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
            $table->unique(['location', 'name', 'folder_id', 'storage_id']);
        });

        Schema::create('fileables', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('fileable_type');
            $table->string('fileable_id');
            $table->uuid('file_id');
            $table->foreign('file_id')->references('id')->on('files');

            $table->index(['fileable_type', 'fileable_id']);
        });

        Schema::create('attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('url')->index();
            $table->string('disk');
            $table->boolean('pending')->default(true);
            $table->string('alt')->nullable();
            $table->string('filename');
            $table->string('attachable_type')->nullable();
            $table->string('attachable_id')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();
            $table->index(['attachable_type', 'attachable_id']);
        });

        Schema::create('shared_contents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('access_emails')->nullable();
            $table->string('access_type', 12)->default(SharedContent::ACCESS_TYPE_TOKEN);
            $table->json('access_keys')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->boolean('public')->default(false);
            $table->boolean('can_remove')->default(false);
            $table->boolean('can_upload')->default(false);
            $table->unsignedSmallInteger('downloads')->default(0);
            $table->tinyInteger('max_downloads')->default(-1);
            $table->integer('upload_size')->default(0);
            $table->unsignedInteger('max_upload_size')->default(config('media-library.shared_content.defaults.max_upload_size', 5242880));
            $table->json('allowed_upload_types')->nullable();
            $table->json('allowed_models')->nullable();
            $table->json('denied_models')->nullable();
            $table->json('meta')->nullable();

            $table->string('shareable_type');
            $table->string('shareable_id');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shared_contents');
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('fileables');
        Schema::dropIfExists('files');
        Schema::dropIfExists('folders');
        Schema::dropIfExists('storages');
    }
}
