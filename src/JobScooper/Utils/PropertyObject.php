<?php

/**
 * Copyright 2014-18 Bryan Selner
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */
namespace JobScooper\Utils;

abstract class PropertyObject extends \ArrayObject implements \ArrayAccess
{
	public function __get($name)
    {
	    if(method_exists($this, $name)){
		    return $this->$name();
	    }
	    elseif (method_exists($this, ($method = 'get'.$name)))
	    {
		    return $this->$method();
	    }
	    elseif(property_exists($this,$name)){
		    // Getter/Setter not defined so return property if it exists
		    return $this->$name;
	    }

        return false;
    }

    public function __isset($name)
    {
        if (method_exists($this, ($method = 'isset'.$name)))
        {
            return $this->$method();
        }
        else return false;
    }

    public function __set($name, $value)
    {
	    if(method_exists($this, $name)){
		    $this->$name($value);
	    }
	    elseif (method_exists($this, ($method = 'set'.$name)))
	    {
		    $this->$method($value);
	    }
	    elseif(property_exists($this,$name)){
		    // Getter/Setter not defined so return property if it exists
		    $this->$name;
	    }
	    else {
		    return false;
	    }
    }

    public function __unset($name)
    {
        if (method_exists($this, ($method = 'unset'.$name)))
        {
            $this->$method();
        }
    }

}

