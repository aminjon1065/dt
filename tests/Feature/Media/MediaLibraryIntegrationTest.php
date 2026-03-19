<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('can attach media to a model using the media library', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $media = $user
        ->addMedia(UploadedFile::fake()->image('avatar.png'))
        ->toMediaCollection('avatars');

    expect($user->getMedia('avatars'))->toHaveCount(1)
        ->and($media->collection_name)->toBe('avatars')
        ->and($media->model->is($user))->toBeTrue();
});
