<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

abstract class Controller
{
    protected function generateAvatar(string $name): string
    {
        $letter = strtoupper(mb_substr(trim($name), 0, 1)) ?: '?';

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200">'
             . '<rect width="200" height="200" fill="#E26B3D"/>'
             . '<text x="100" y="100" font-family="Helvetica Neue,Arial,sans-serif" font-size="90" font-weight="700" fill="#F2EEE5" text-anchor="middle" dominant-baseline="central">'
             . htmlspecialchars($letter, ENT_XML1)
             . '</text>'
             . '</svg>';

        $path = 'profile-images/avatar_' . uniqid('', true) . '.svg';
        Storage::disk('public')->put($path, $svg);

        return $path;
    }

    protected function storeAttachments(array $inputs, array $files, $user, string $folder): void
    {
        foreach ($inputs as $index => $attInput) {
            $key      = $attInput['key'] ?? null;
            $fileGroup = $files[$index] ?? null;
            $file     = is_array($fileGroup) ? ($fileGroup['file'] ?? null) : null;

            if ($file && $file->isValid() && filled($key)) {
                $path = $file->store($folder . '/' . $user->id, 'public');
                $user->attachments()->create(['key' => $key, 'attachment_path' => $path]);
            }
        }
    }
}
