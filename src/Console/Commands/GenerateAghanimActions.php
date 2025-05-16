<?php

namespace BangNokia\Aghanim\Console\Commands;

use BangNokia\Aghanim\Security\ActionAuthorizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

class GenerateAghanimActions extends Command
{
    protected $signature = 'aghanim:generate-actions {--all : Generate all actions, including unauthorized ones}';
    protected $description = 'Generate TypeScript definitions for Aghanim actions';

    /**
     * @var ActionAuthorizer
     */
    protected $authorizer;

    /**
     * Create a new command instance.
     *
     * @param ActionAuthorizer $authorizer
     */
    public function __construct(ActionAuthorizer $authorizer)
    {
        parent::__construct();
        $this->authorizer = $authorizer;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $includeAll = $this->option('all');

        if ($includeAll) {
            $actions = $this->getAllActionClasses();
            $this->warn('Generating TypeScript definitions for ALL actions, including unauthorized ones.');
        } else {
            $actions = $this->getAuthorizedActionClasses();
        }

        if (empty($actions)) {
            $this->warn('No actions found or authorized.');
            return 0;
        }

        $tsContent = $this->generateTypeScript($actions);
        File::put(resource_path('js/aghanim-actions.ts'), $tsContent);

        $this->info('Aghanim actions generated at resources/js/aghanim-actions.ts');
        $this->info('Generated ' . count($actions) . ' action' . (count($actions) !== 1 ? 's' : ''));

        return 0;
    }

    /**
     * Get all action classes from the configured path.
     *
     * @return array
     */
    protected function getAllActionClasses(): array
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

    /**
     * Get only authorized action classes.
     *
     * @return array
     */
    protected function getAuthorizedActionClasses(): array
    {
        return $this->authorizer->getAuthorizedActions();
    }

    /**
     * Generate TypeScript definitions for the given actions.
     *
     * @param array $actions
     * @return string
     */
    protected function generateTypeScript(array $actions): string
    {
        $methods = collect($actions)
            ->map(function ($class) {
                $name = Str::camel(class_basename($class));
                $paramTypes = $this->getParameterTypes($class);

                return "  $name($paramTypes): Promise<any>;";
            })->implode("\n");

        return "export const aghanim = {\n  actions: {\n$methods\n  }\n};";
    }

    /**
     * Get parameter types for the execute method of an action class.
     *
     * @param string $class
     * @return string
     */
    protected function getParameterTypes(string $class): string
    {
        try {
            $reflection = new ReflectionClass($class);

            if (!$reflection->hasMethod('execute')) {
                return '...params: any[]';
            }

            $method = $reflection->getMethod('execute');
            $parameters = $method->getParameters();

            if (empty($parameters)) {
                return '';
            }

            return collect($parameters)
                ->map(function (ReflectionParameter $param) {
                    $name = $param->getName();
                    $type = 'any';

                    if ($param->hasType()) {
                        $paramType = $param->getType();
                        $typeName = $paramType->getName();

                        // Map PHP types to TypeScript types
                        $type = match($typeName) {
                            'int', 'integer', 'float', 'double' => 'number',
                            'bool', 'boolean' => 'boolean',
                            'string' => 'string',
                            'array' => 'any[]',
                            default => 'any'
                        };
                    }

                    $optional = $param->isOptional() ? '?' : '';
                    return "$name$optional: $type";
                })
                ->implode(', ');
        } catch (\Exception $e) {
            $this->warn("Could not reflect on class $class: " . $e->getMessage());
            return '...params: any[]';
        }
    }
}
