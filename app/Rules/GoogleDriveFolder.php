<?php

namespace App\Rules;

use App\Services\GoogleDriveService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class GoogleDriveFolder implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value)) {
            return; // nullable
        }

        $id = GoogleDriveService::parseFolderId((string) $value);
        if (!$id) {
            $fail('Enter a valid Google Drive folder link or folder ID.');
            return;
        }

        // Optional but recommended: verify the folder exists & is reachable by the service account.
        $svc = app(GoogleDriveService::class);
        if ($svc->enabled() && !$svc->folderExists($id)) {
            $fail('The service account cannot access that folder. Share it with the service account email (Editor).');
        }
    }
}