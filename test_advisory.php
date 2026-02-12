<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

use App\Models\Post;

// Check if 'asd' advisory exists
$advisory = Post::where('title', 'asd')->first();

if ($advisory) {
    echo "Found advisory: ID={$advisory->id}, Title={$advisory->title}, Category={$advisory->category}\n";
} else {
    echo "Advisory 'asd' not found\n";
}

// List all advisories
$allAdvisories = Post::where('category', 'advisory')->get();
echo "Total advisories: " . $allAdvisories->count() . "\n";

foreach ($allAdvisories as $adv) {
    echo "- {$adv->title} (ID: {$adv->id})\n";
}
