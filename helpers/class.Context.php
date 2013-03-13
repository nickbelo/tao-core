<?php
/*  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
?>
<?php

error_reporting(E_ALL);

/**
 * The context class enables you to define some context to the app 
 * and to check staticly which context/mode is actually load
 *
 * @author Cedric Alfonsi, <cedric.alfonsi@tudor.lu>
 * @package tao
 * @subpackage helpers
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
// section 127-0-1-1--7978326a:129a2dd1980:-8000:000000000000209D-includes begin
// section 127-0-1-1--7978326a:129a2dd1980:-8000:000000000000209D-includes end

/* user defined constants */
// section 127-0-1-1--7978326a:129a2dd1980:-8000:000000000000209D-constants begin
// section 127-0-1-1--7978326a:129a2dd1980:-8000:000000000000209D-constants end

/**
 * The context class enables you to define some context to the app 
 * and to check staticly which context/mode is actually load
 *
 * @access public
 * @author Cedric Alfonsi, <cedric.alfonsi@tudor.lu>
 * @package tao
 * @subpackage helpers
 */
class tao_helpers_Context
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * the list of current loaded modes
     *
     * @access protected
     * @var array
     */
    protected static $current = array();

    // --- OPERATIONS ---

    /**
     * load a new current mode
     *
     * @access public
     * @author Cedric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string mode
     * @return mixed
     */
    public static function load($mode)
    {
        // section 127-0-1-1--7978326a:129a2dd1980:-8000:00000000000020A5 begin
        
		if(!is_string($mode)){
			throw new Exception("Try to load an irregular mode in the context");
		}
    	if(empty($mode)){
    		throw new Exception("Cannot load an empty mode in the context");
    	}
    	
    	if(!in_array($mode, self::$current)){
    		self::$current[] = $mode;
    	}
        
        // section 127-0-1-1--7978326a:129a2dd1980:-8000:00000000000020A5 end
    }

    /**
     * check if the mode in parameter is loaded in the context
     *
     * @access public
     * @author Cedric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string mode
     * @return boolean
     */
    public static function check($mode)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--7978326a:129a2dd1980:-8000:00000000000020B0 begin
        
    	if(!is_string($mode)){
			throw new Exception("Try to check an irregular mode");
		}
    	if(empty($mode)){
    		throw new Exception("Cannot check an empty mode");
    	}
    	
    	$returnValue = in_array($mode, self::$current);
        
        // section 127-0-1-1--7978326a:129a2dd1980:-8000:00000000000020B0 end

        return (bool) $returnValue;
    }

    /**
     * reset the context
     *
     * @access public
     * @author Cedric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @return mixed
     */
    public static function reset()
    {
        // section 127-0-1-1--7978326a:129a2dd1980:-8000:00000000000020B3 begin

    	self::$curent = array();
    	
        // section 127-0-1-1--7978326a:129a2dd1980:-8000:00000000000020B3 end
    }

    /**
     * Short description of method unload
     *
     * @access public
     * @author Cedric Alfonsi, <cedric.alfonsi@tudor.lu>
     * @param  string mode
     * @return mixed
     */
    public function unload($mode)
    {
        // section 127-0-1-1--11f0cc4c:12fba405a1e:-8000:0000000000003AA8 begin
        
    	if(!is_string($mode)){
			throw new Exception("Try to unload an irregular mode in the context");
		}
    	if(empty($mode)){
    		throw new Exception("Cannot unload an empty mode in the context");
    	}
    	
    	if(in_array($mode, self::$current)){
    		$index = array_search ($mode, self::$current);
    		if ($index !== false){
    			unset (self::$current[$index]);
    		}
    	}
    	
        // section 127-0-1-1--11f0cc4c:12fba405a1e:-8000:0000000000003AA8 end
    }

} /* end of class tao_helpers_Context */

?>