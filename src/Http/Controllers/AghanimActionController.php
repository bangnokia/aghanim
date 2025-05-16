<?php

namespace BangNokia\Aghanim\Http\Controllers;

use BangNokia\Aghanim\Security\ActionAuthorizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AghanimActionController
{
    /**
     * @var ActionAuthorizer
     */
    protected $authorizer;

    /**
     * AghanimActionController constructor.
     *
     * @param ActionAuthorizer $authorizer
     */
    public function __construct(ActionAuthorizer $authorizer)
    {
        $this->authorizer = $authorizer;
    }

    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response|\Inertia\Response
     */
    public function handle(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'action' => 'required|string',
                'params' => 'sometimes|array',
            ]);

            $action = $validated['action'];
            $params = $validated['params'] ?? [];

            // Get the full class name
            $className = config('aghanim.action_namespace') . '\\' . Str::studly($action);

            // Check if the class exists
            if (!class_exists($className)) {
                Log::warning("Aghanim: Action class {$className} not found");
                return response()->json(['error' => "Action not found"], 404);
            }

            // Check if the action is authorized
            if (!$this->authorizer->isAuthorized($className)) {
                Log::warning("Aghanim: Unauthorized action attempt: {$className}");
                return response()->json(['error' => "Unauthorized action"], 403);
            }

            // Get the action instance
            $action = app($className);

            // Execute the action
            // If the action extends our base Action class, it will handle validation
            $result = $action->execute(...$params);

            // Return the result
            return Inertia::render($request->header('X-Inertia-Component'), [
                'aghanim' => [
                    'actionResult' => $result,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error("Aghanim: Error executing action: " . $e->getMessage(), [
                'exception' => $e,
                'action' => $request->input('action'),
            ]);

            return response()->json(['error' => 'An error occurred while executing the action'], 500);
        }
    }
}