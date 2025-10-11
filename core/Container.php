<?php

namespace Core;

use ReflectionClass;
use PDO;

class Container
{

    public function buildInstance(string $className): object
    {
        $reflector = new ReflectionClass($className);
        $constructor = $reflector->getConstructor();

        if (!$constructor) {
            return $reflector->newInstance();
        }

        $parameters = $constructor->getParameters();
        $dependencies = []; // Array to hold the dependencies

        foreach ($parameters as $parameter) {
            $parameterName = $parameter->getName();
            $type = $parameter->getType(); // Get the type hint for the parameter

            if (!$type && !$parameter->isOptional()) {
                throw new \Exception("Cannot resolve untyped parameter '{$parameterName}' of class '{$className}'.");
            }

            $dependency = null; // Variable to hold the resolved dependency

            if ($type && !$type->isBuiltin()) {
                $parameterClassName = $type->getName();

                if ($parameterClassName === PDO::class && $parameterName === 'db') {
                    $dependency = Database::getConnection()->getPDO();
                } else {
                    $dependency = $this->buildInstance($parameterClassName);
                }
            } elseif ($type && $type->isBuiltin()) {
                if ($parameter->isOptional()) {
                    $dependency = $parameter->getDefaultValue();
                } else {
                    throw new \Exception("Cannot resolve built-in parameter '{$parameterName}' of class '{$className}'. A value or default must be provided.");
                }
            }

            if ($parameter->isOptional() && $dependency === null) {
                $dependency = $parameter->getDefaultValue();
            }

            if ($dependency === null && !$parameter->isOptional()) {
                 throw new \Exception("Failed to resolve dependency for parameter '{$parameterName}' in class '{$className}'.");
            }

            $dependencies[] = $dependency;
        }

        return $reflector->newInstanceArgs($dependencies);
    }

}