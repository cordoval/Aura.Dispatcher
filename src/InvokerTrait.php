<?php
/**
 * 
 * This file is part of Aura for PHP.
 * 
 * @package Aura.Invoker
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Invoker;

use BadMethodCallException;
use Closure;
use ReflectionFunction;
use ReflectionMethod;

/**
 * 
 * Provides `invokeMethod()` and `invokeClosure()` to invoke an object method
 * or closure using named parameters.
 * 
 * @package Aura.Invoker
 * 
 */
trait InvokerTrait
{
    /**
     * 
     * Given an object, invokes a method on it, passing named parameters to
     * that method; alternatively, invokes a closure using named parameters.
     * 
     * @param object $object The object (or Closure) to work with.
     * 
     * @param string $method The method to invoke on the object; this is
     * ignored if the object is a closure.
     * 
     * @param array $params An array of key-value pairs to use as params for
     * the method; the array keys are matched to the method param names.
     * 
     * @return mixed The return of the invoked object method.
     * 
     */
    protected function invokeMethod(
        $object,
        $method,
        array $params = []
    ) {
        // is the method callable?
        if (! is_callable([$object, $method])) {
            $message = get_class($object) . '::' . $method;
            throw new BadMethodCallException($message);
        }
        
        // reflect on the object method
        $reflect = new ReflectionMethod($object, $method);
        
        // sequential arguments when invoking
        $args = [];
        
        // match named params with arguments
        foreach ($reflect->getParameters() as $param) {
            if (isset($params[$param->name])) {
                // a named param value is available
                $args[] = $params[$param->name];
            } else {
                // use the default value, or null if there is none
                $args[] = $param->isDefaultValueAvailable()
                        ? $param->getDefaultValue()
                        : null;
            }
        }
        
        // invoke with the args, and done
        return $reflect->invokeArgs($object, $args);
    }
    
    protected function invokeClosure(Closure $closure, array $params = [])
    {
        // treat as a function; cf. https://bugs.php.net/bug.php?id=65432
        $reflect = new ReflectionFunction($closure);
        
        // sequential arguments when invoking
        $args = [];
        
        // match named params with arguments
        foreach ($reflect->getParameters() as $param) {
            if (isset($params[$param->name])) {
                // a named param value is available
                $args[] = $params[$param->name];
            } else {
                // use the default value, or null if there is none
                $args[] = $param->isDefaultValueAvailable()
                        ? $param->getDefaultValue()
                        : null;
            }
        }
        
        // invoke with the args, and done
        return $reflect->invokeArgs($args);
    }
}