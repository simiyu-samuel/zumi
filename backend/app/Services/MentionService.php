<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class MentionService
{
    /**
     * Extract usernames from text (e.g., @samuel).
     */
    public function extractUsernames(string $text): Collection
    {
        preg_match_all('/(?<=^|\s)@([a-zA-Z0-9_]+)/', $text, $matches);
        
        return collect($matches[1])->unique();
    }

    /**
     * Get users corresponding to extracted usernames.
     */
    public function getMentionedUsers(string $text): Collection
    {
        $usernames = $this->extractUsernames($text);

        if ($usernames->isEmpty()) {
            return collect();
        }

        return User::whereIn('username', $usernames)->get();
    }

    /**
     * Process mentions in a piece of content (Wave, Comment, etc.).
     * This will be used later to trigger notifications.
     */
    public function processMentions($contentModel, string $text): void
    {
        $users = $this->getMentionedUsers($text);
        $snippet = \Illuminate\Support\Str::limit($text, 50);

        foreach ($users as $user) {
            // Avoid notifying self
            if ($user->id === $contentModel->user_id) {
                continue;
            }

            $user->notify(new \App\Notifications\MentionNotification($contentModel, $snippet));
        }
    }
}
