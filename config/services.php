<?php

return [
    'google' => [
        'root_folder_id' => env('GOOGLE_DRIVE_ROOT_FOLDER_ID'),
    ],
    'ksg' => [
        'ddict_email' => env('DDICT_EMAIL', 'ddict@ksg.ac.ke'),            // CC on every reminder
        'compile_review_email' => env('COMPILE_REVIEW_EMAIL', 'mwithali.thuranira@ksg.ac.ke'),
    ],
];
