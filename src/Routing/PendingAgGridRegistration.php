<?php

namespace Clickbar\AgGrid\Routing;

use Illuminate\Support\Facades\Route;

class PendingAgGridRegistration
{
    protected string $route;

    protected ?string $name;

    /** @var class-string */
    protected string $controller;

    protected ?array $methods;

    /**
     * The resource's registration status.
     */
    protected bool $registered = false;

    /** @param  class-string  $controller */
    public function __construct(string $route, string $controller)
    {
        $this->route = $route;
        $this->controller = $controller;
    }

    public function name(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function register(): void
    {
        $this->registered = true;

        $getRowsRoute = Route::post($this->route, [$this->controller, 'rows']);
        $setValuesRoute = Route::post("$this->route/set-values", [$this->controller, 'setValues']);

        if ($this->name) {
            $getRowsRoute->name($this->name);
            $setValuesRoute->name("$this->name.set-values");
        }
    }

    /**
     * Handle the object's destruction.
     */
    public function __destruct()
    {
        if (! $this->registered) {
            $this->register();
        }
    }
}
