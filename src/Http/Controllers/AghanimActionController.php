<?php

namespace BangNokia\Aghanim\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AghanimActionController
{
    public function handle(Request $request)
    {
        $action = $request->input('action');
        $params = $request->input('params', []);

        $className = config('aghanim.action_namespace') . '\\' . Str::studly($action);

        if (!class_exists($className)) {
            return response()->json(['error' => "Action {$className} not found"], 404);
        }

        $result = app($className)->execute(...$params);

        return Inertia::render($request->header('X-Inertia-Component'), [
            'aghanim' => [
                'actionResult' => $result,
            ],
        ]);
    }
}