<?php
namespace UnitTest;
/**
 * Enable some extra awesome functionality to  phpunit
 * Created by PhpStorm.
 * User: Johans
 * Date: 2017/9/13
 * Time: 15:46
 */

abstract class ExtTestCase extends \PHPUnit\Framework\TestCase
{
    protected static $ObjectCached =  null;
	protected function assertException(callable $callback, $expectedException = 'Exception', $expectedCode = null, $expectedMessage = null)
	{
		$expectedException = ltrim((string) $expectedException, '\\');
		if (!class_exists($expectedException) && !interface_exists($expectedException)) {
			$this->fail(sprintf('An exception of type "%s" does not exist.', $expectedException));
		}
		try {
			$callback();
		} catch (\Exception $e) {
			$class = get_class($e);
			$message = $e->getMessage();
			$code = $e->getCode();
			$errorMessage = 'Failed asserting the class of exception';
			if ($message && $code) {
				$errorMessage .= sprintf(' (message was %s, code was %d)', $message, $code);
			} elseif ($code) {
				$errorMessage .= sprintf(' (code was %d)', $code);
			}
			$errorMessage .= '.';
			$this->assertInstanceOf($expectedException, $e, $errorMessage);
			if ($expectedCode !== null) {
				$this->assertEquals($expectedCode, $code, sprintf('Failed asserting code of thrown %s.', $class));
			}
			if ($expectedMessage !== null) {
				$this->assertContains($expectedMessage, $message, sprintf('Failed asserting the message of thrown %s.', $class));
			}
			return;
		}
		$errorMessage = 'Failed asserting that exception';
		if (strtolower($expectedException) !== 'exception') {
			$errorMessage .= sprintf(' of type %s', $expectedException);
		}
		$errorMessage .= ' was thrown.';
		$this->fail($errorMessage);
	}


    protected function call($class,$method,$method_args = array(),$args=array(),$cached = false ) {

        if (!is_string($method) || !is_array($args)) {
            return false;
        }
        $refcls = new \ReflectionClass($class);
        if ( !empty(static::$ObjectCached) && $refcls->isInstance(static::$ObjectCached) &&  $cached ) {
            $class = static::$ObjectCached;
        } else {
            if (!is_object($class)) {
                $class = $refcls->newInstanceArgs($args);
            }
            static::$ObjectCached = $class;
        }
        $refmethod = $refcls->getMethod($method);
        $refmethod->setAccessible(true);
        return $refmethod->invokeArgs($class, $args);
    }

    protected function getProperty($class,$field,$args=array(),$cached = false){
        if (!is_string($field)) {
            return false;
        }
        $refcls = new \ReflectionClass($class);
        if (!empty(static::$ObjectCached) &&  $refcls->isInstance(static::$ObjectCached) && $cached) {
            $class = static::$ObjectCached;
        } else {
            if (!is_object($class)) {
                $class = $refcls->newInstanceArgs($args);
            }
            static::$ObjectCached = $class;
        }
        $refprops = $refcls->getProperties();
        while(($refcls->getParentClass()) != false) {
            $refcls = $refcls->getParentClass();
            $properties = $refcls->getProperties();
            $refprops = empty($properties) ? $refprops : array_merge($refprops,$properties);
        }
        foreach ($refprops as $prop)  {
            if ($prop->getName() == $field) {
                $refprop = $prop;
                break;
            }
        }
        $refprop->setAccessible(true);
        return $refprop->getValue($class);
    }

    protected function setProperty($class,$field,$value,$cached = false) {
        if (!is_string($class) || !is_string($field)) {
            return false;
        }
        $refcls = new \ReflectionClass($class);
        if ( !empty(static::$ObjectCached) && $refcls->isInstance(static::$ObjectCached) && $cached ) {
            $class = static::$ObjectCached;
        } else {
            $class = new $class();
        }
        $refprop = $refcls->getProperty($field);
        $refprop->setAccessible(true);
        $refprop->setValue($class,$value);
    }

    protected function getInAccessibleMethod($class,$method,$cached = false ){
        if (!is_string($class) || !is_string($method)) {
            return false;
        }
        $refcls = new \ReflectionClass($class);
        if ( !empty(static::$ObjectCached) &&  $refcls->isInstance(static::$ObjectCached) && $cached) {
            $class = static::$ObjectCached;
        } else {
            $class = new $class();
            static::$ObjectCached = $class;
        }
        $refprop = $refcls->getMethod($method);
        $refprop->setAccessible(true);
        return $refprop->getClosure($class);
    }
}
