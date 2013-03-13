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
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);\n *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
?>
<?php 
// -- Install bootstrap
$rootDir = dir(dirname(__FILE__).'/../../');
$root = realpath($rootDir->path).'/';
define('TAO_UPDATE_PATH', $root);
define('GENERIS_PATH', $root.'generis/');
set_include_path(get_include_path() . PATH_SEPARATOR . $root. PATH_SEPARATOR . GENERIS_PATH);

function __autoload($class_name) {
	foreach (array(TAO_UPDATE_PATH, GENERIS_PATH) as $dir) {
		$path = str_replace('_', '/', $class_name);
		$file =  'class.' . basename($path). '.php';
		$filePath = $dir . dirname($path) . '/' . $file;
		if (file_exists($filePath)){
			require_once  $filePath;
			break;
		}
		else{
			$file = 'interface.' . basename($path). '.php';
			$filePath = $dir . dirname($path) . '/' . $file;
			if (file_exists($filePath)){
				require_once $filePath;
				break;
			}
		}
	}
}

common_log_Dispatcher::singleton()->init(array(
	array(
		'class'			=> 'SingleFileAppender',
		'threshold'		=> common_Logger::TRACE_LEVEL,
		'file'			=> TAO_UPDATE_PATH.'tao/update/log/update.log',
)));

require_once ('tao/helpers/class.Display.php');
require_once ('tao/helpers/class.Uri.php');

?>