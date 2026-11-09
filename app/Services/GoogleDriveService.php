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

    // Find-or-create the folder for one item: "{code} - {name}"
    public function itemFolder(PerformanceItem $item): ?string
    {
        $name = "{$item->code} - {$item->name}";
        $rootId = config('services.google.root_folder_id');

        $found = $this->service()->files->listFiles([
            'q' => "name='{$name}' and '{$rootId}' in parents and mimeType='application/vnd.google-apps.folder' and trashed=false",
            'fields' => 'files(id)',
        ]);
        if (count($found->files)) {
            return $found->files[0]->id;
        }

        $folder = $this->service()->files->create(new Drive\DriveFile([
            'name' => $name,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => [$rootId],
        ]), ['fields' => 'id']);

        return $folder->id;
    }

    // Upload evidence file named e.g. B1.1_Baringo_Q1_inventory.pdf into the item folder
    public function uploadEvidence(EvidenceSubmission $submission): ?array
    {
        if (!$this->enabled()) {
            return null;
        }

        try {
            $item = $submission->item;
            $campus = $submission->campus;
            $folderId = $this->itemFolder($item);

            $safeOriginal = preg_replace('/[^A-Za-z0-9_.-]/', '_', $submission->original_filename);
            $driveName = "{$item->code}_{$campus->name}_Q{$submission->quarter}_{$safeOriginal}";

            $file = $this->service()->files->create(new Drive\DriveFile([
                'name' => $driveName,
                'parents' => [$folderId],
            ]), [
                'data' => file_get_contents(storage_path('app/' . $submission->disk_path)),
                'mimeType' => mime_content_type(storage_path('app/' . $submission->disk_path)),
                'uploadType' => 'multipart',
                'fields' => 'id,webViewLink',
            ]);

            return ['id' => $file->id, 'link' => $file->webViewLink];
        } catch (\Throwable $e) {
            Log::error('Drive upload failed: ' . $e->getMessage());
            return null;
        }
    }
}
