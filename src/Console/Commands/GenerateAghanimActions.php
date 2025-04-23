<?php

namespace BangNokia\Aghanim\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateAghanimActions extends Command
{
    protected $signature = 'aghanim:generate-actions';
    protected $description = 'Generate TypeScript definitions for Aghanim actions';

    public function handle()
    {
        $actions = $this->getActionClasses();
        $tsContent = $this->generateTypeScript($actions);
        File::put(resource_path('js/aghanim-actions.ts'), $tsContent);

        $this->info('Aghanim actions generated at resources/js/aghanim-actions.ts');
    }

    protected function getActionClasses(): array
    {
        $path = config('aghanim.action_path');
        if (!File::exists($path)) {
            return [];
        }

        return collect(File::allFiles($path))
            ->map(fn($file) => config('aghanim.action_namespace') . '\\' . str_replace('.php', '', $file->getFilename()))
            ->filter(fn($class) => class_exists($class))
            ->all();
    }

    protected function generateTypeScript(array $actions): string
    {
        $methods = collect($actions)->map(fn($class) => {
            $name = Str::camel(class_basename($class));
            return "  $name(...params: any[]): Promise<any>;";
        })->implode("\n");

        return "export const aghanim = {\n  actions: {\n$methods\n  }\n};";
    }
}