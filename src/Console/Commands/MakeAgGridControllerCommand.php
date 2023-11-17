<?php

namespace Clickbar\AgGrid\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class MakeAgGridControllerCommand extends GeneratorCommand
{
    protected $signature = 'make:ag-grid-controller {name} {--force}';

    protected $description = 'Create a new ag grid controller class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Controller';

    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/make-ag-grid-controller.stub');
    }

    /**
     * Resolve the fully-qualified path to the stub.
     */
    protected function resolveStubPath(string $stub): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__.$stub;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Http\Controllers';
    }
}
