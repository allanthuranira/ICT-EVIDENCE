<?php

namespace App\Services;

use App\Models\Campus;
use App\Models\EvidenceSubmission;
use App\Models\PerformanceItem;
use Google\Client;
use Google\Service\Drive;
use Illuminate\Support\Facades\Log;

class GoogleDriveService
{
    private ?Drive $drive = null;

    public function enabled(): bool
    {
        return file_exists(storage_path('app/google/service-account.json'))
            && !empty(config('services.google.root_folder_id'));
    }

    private function service(): Drive
    {
        if ($this->drive) {
            return $this->drive;
        }
        $client = new Client();
        $client->setAuthConfig(storage_path('app/google/service-account.json'));
        $client->addScope(Drive::DRIVE);
        $this->drive = new Drive($client);
        return $this->drive;
    }

    /* ──────────────────────────────────────────────────────────────
     |  URL / ID helpers
     * ────────────────────────────────────────────────────────────── */

    /**
     * Extract a Drive folder ID from:
     *   - a full Drive URL:  https://drive.google.com/drive/folders/ABC123...
     *   - an open?id= URL:   https://drive.google.com/open?id=ABC123...
     *   - a raw folder ID:   ABC123...
     * Returns null when nothing usable is found.
     */
    public static function parseFolderId(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        // /folders/<id>
        if (preg_match('~/folders/([a-zA-Z0-9_-]{10,})~', $value, $m)) {
            return $m[1];
        }
        // ?id=<id> or &id=<id>
        if (preg_match('~[?&]id=([a-zA-Z0-9_-]{10,})~', $value, $m)) {
            return $m[1];
        }
        // bare ID
        if (preg_match('~^[a-zA-Z0-9_-]{10,}$~', $value)) {
            return $value;
        }

        return null;
    }

    /** Canonical Drive folder URL for an ID. */
    public static function folderUrl(string $folderId): string
    {
        return "https://drive.google.com/drive/folders/{$folderId}";
    }

    /** Whether the folder exists and is readable by the service account. */
    public function folderExists(string $folderId): bool
    {
        if (!$this->enabled()) {
            return true; // don't block saving when Drive is off
        }
        try {
            $meta = $this->service()->files->get($folderId, [
                'fields' => 'id,mimeType,trashed',
                'supportsAllDrives' => true,
            ]);
            return $meta->mimeType === 'application/vnd.google-apps.folder'
                && !$meta->trashed;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /* ──────────────────────────────────────────────────────────────
     |  Folders
     * ────────────────────────────────────────────────────────────── */

    /** Escape single quotes for Drive query strings. */
    private function q(string $s): string
    {
        return str_replace("'", "\\'", $s);
    }

    /** Find a child folder by name under a parent, or create it. */
    private function findOrCreateFolder(string $name, string $parentId): string
    {
        $q = sprintf(
            "name='%s' and '%s' in parents and mimeType='application/vnd.google-apps.folder' and trashed=false",
            $this->q($name),
            $this->q($parentId)
        );

        $res = $this->service()->files->listFiles([
            'q' => $q,
            'fields' => 'files(id,webViewLink)',
            'supportsAllDrives' => true,
            'includeItemsFromAllDrives' => true,
        ]);

        if (count($res->files)) {
            return $res->files[0]->id;
        }

        $folder = $this->service()->files->create(
            new Drive\DriveFile([
                'name' => $name,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents' => [$parentId],
            ]),
            ['fields' => 'id,webViewLink', 'supportsAllDrives' => true]
        );

        return $folder->id;
    }

    /**
     * Return the *stored* item folder. Never creates one — the admin sets it
     * when creating/editing the item in the dashboard.
     *
     * @return array{id:string,url:string}|null
     */
    public function itemFolder(PerformanceItem $item): ?array
    {
        if (!$this->enabled() || !$item->drive_folder_id) {
            return null;
        }

        return [
            'id'  => $item->drive_folder_id,
            'url' => $item->drive_folder_url
                ?: self::folderUrl($item->drive_folder_id),
        ];
    }

    /**
     * Campus subfolder inside the item folder, created if missing.
     *
     * @return array{id:string,url:string}|null
     */
    public function campusFolder(PerformanceItem $item, Campus $campus): ?array
    {
        $itemFolder = $this->itemFolder($item);
        if (!$itemFolder) {
            return null;
        }

        $folderId = $this->findOrCreateFolder($campus->name, $itemFolder['id']);
        $meta = $this->service()->files->get($folderId, [
            'fields' => 'id,webViewLink',
            'supportsAllDrives' => true,
        ]);

        return ['id' => $meta->id, 'url' => $meta->webViewLink];
    }

    /* ──────────────────────────────────────────────────────────────
     |  Upload
     * ────────────────────────────────────────────────────────────── */

    /**
     * Upload an evidence file to:
     *   ItemFolder / CampusName / Qn / {code}_{Campus}_Q{n}_{original}
     *
     * @return array{id:string,link:string,folder_id:string,folder_url:string,item_folder_url:?string}|null
     */
    public function uploadEvidence(EvidenceSubmission $submission): ?array
    {
        if (!$this->enabled()) {
            return null;
        }

        try {
            $item   = $submission->item;
            $campus = $submission->campus;

            if (!$item || !$campus) {
                Log::warning('Drive upload: missing item or campus on submission', [
                    'submission_id' => $submission->id,
                ]);
                return null;
            }

            $campusFolder = $this->campusFolder($item, $campus);
            if (!$campusFolder) {
                Log::warning('Drive upload: item has no drive folder configured', [
                    'item_id' => $item->id,
                ]);
                return null;
            }

            // Quarter subfolder
            $quarterFolderId = $this->findOrCreateFolder(
                "Q{$submission->quarter}",
                $campusFolder['id']
            );

            $diskPath = storage_path('app/' . ltrim($submission->disk_path, '/'));
            if (!is_file($diskPath)) {
                Log::error('Drive upload: local file missing', ['path' => $diskPath]);
                return null;
            }

            $safeOriginal = preg_replace('/[^A-Za-z0-9_.-]/', '_', $submission->original_filename);
            $driveName = "{$item->code}_{$campus->name}_Q{$submission->quarter}_{$safeOriginal}";

            $file = $this->service()->files->create(
                new Drive\DriveFile([
                    'name' => $driveName,
                    'parents' => [$quarterFolderId],
                ]),
                [
                    'data' => file_get_contents($diskPath),
                    'mimeType' => mime_content_type($diskPath) ?: 'application/octet-stream',
                    'uploadType' => 'multipart',
                    'fields' => 'id,webViewLink',
                    'supportsAllDrives' => true,
                ]
            );

            return [
                'id'              => $file->id,
                'link'            => $file->webViewLink,
                'folder_id'       => $quarterFolderId,
                'folder_url'      => $campusFolder['url'],
                'item_folder_url' => $this->itemFolder($item)['url'] ?? null,
            ];
        } catch (\Throwable $e) {
            Log::error('Drive upload failed: ' . $e->getMessage(), [
                'submission_id' => $submission->id ?? null,
            ]);
            return null;
        }
    }
}