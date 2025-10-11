<?php

namespace Core;
use Core\Request;

interface Middleware
{
    /**
     * Handle the incoming request.
     *
     * @param callable $next The next middleware in the pipeline or the final handler.
     * @return mixed
     */
    public function handle(callable $next, Request $request): mixed;
}