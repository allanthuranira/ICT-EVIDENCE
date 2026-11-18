<div class="mb-3">
    <label class="form-label">Google Drive folder (link or ID)</label>
    <input type="text" name="drive_folder"
           class="form-control @error('drive_folder') is-invalid @enderror"
           value="{{ old('drive_folder', $item->drive_folder_url ?? $item->drive_folder_id ?? '') }}"
           placeholder="https://drive.google.com/drive/folders/XXXXXXXXXXXX">
    @error('drive_folder')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    @if(!empty($item->drive_folder_url))
        <div class="form-text">
            Current: <a href="{{ $item->drive_folder_url }}" target="_blank">{{ $item->drive_folder_url }}</a>
            — uploading will write inside this folder.
        </div>
    @endif
    <div class="form-text">
        Share this folder with the service account email as <strong>Editor</strong>.
        Leave blank if you don't want Drive uploads for this item.
    </div>
</div>