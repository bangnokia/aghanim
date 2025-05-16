<?php

namespace BangNokia\Aghanim\Security;

use Illuminate\Support\Facades\Log;

class ActionAuthorizer
{
    /**
     * Check if the action is authorized to be executed.
     *
     * @param string $actionClass The fully qualified class name of the action
     * @return bool
     */
    public function isAuthorized(string $actionClass): bool
    {
        $authorizedActions = config('aghanim.authorized_actions');

        // If all actions are authorized
        if ($authorizedActions === 'all') {
            return true;
        }

        // If authorized_actions is an array, check if the action is in the list
        if (is_array($authorizedActions)) {
            return in_array($actionClass, $authorizedActions);
        }

        // Log an error if the configuration is invalid
        Log::error('Invalid configuration for aghanim.authorized_actions. Expected "all" or array, got: ' . gettype($authorizedActions));
        
        // Default to false for security
        return false;
    }

    /**
     * Get a list of all authorized action classes.
     *
     * @return array
     */
    public function getAuthorizedActions(): array
    {
        $authorizedActions = config('aghanim.authorized_actions');
        $actionNamespace = config('aghanim.action_namespace');
        $actionPath = config('aghanim.action_path');

        // If all actions are authorized, return all action classes
        if ($authorizedActions === 'all') {
            if (!file_exists($actionPath)) {
                return [];
            }

            return collect(\Illuminate\Support\Facades\File::allFiles($actionPath))
                ->map(fn($file) => $actionNamespace . '\\' . str_replace('.php', '', $file->getFilename()))
                ->filter(fn($class) => class_exists($class))
                ->all();
        }

        // If authorized_actions is an array, return it
        if (is_array($authorizedActions)) {
            return array_filter($authorizedActions, fn($class) => class_exists($class));
        }

        // Default to empty array
        return [];
    }
}
