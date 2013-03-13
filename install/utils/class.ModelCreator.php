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
if(!defined('RDFAPI_INCLUDE_DIR')){
	define('RDFAPI_INCLUDE_DIR', dirname(__FILE__).'/../../../generis/includes/rdfapi-php/api/');
}
require_once(RDFAPI_INCLUDE_DIR . "RdfAPI.php");

/**
 * The ModelCreator enables you to import Ontologies into a TAO module
 *
 * @author Bertrand CHEVRIER <bertrand.chevrier@tudor.lu>
 *
 */
class tao_install_utils_ModelCreator{

	/**
	 * @var string the module namesapce
	 */
	protected $localNs = '';

	/**
	 * Instantiate a creator for a module
	 * @param string $localNamespace
	 */
	public function __construct($localNamespace){
		if(empty($localNamespace) || !preg_match("/^http/", $localNamespace)){
			throw new tao_install_utils_Exception("$localNamespace is not valid namespace URI for the local namespace!");
		}
		$this->localNs = $localNamespace;
		if(!preg_match("/#$/", $this->localNs)){
			$this->localNs .= '#';
		}
	}

	/**
	 * Specifiq method to insert the super user model,
	 * by using a template RDF file
	 * @param array $userData
	 */
	public function insertSuperUser(array $userData){

		if (empty($userData['login']) || empty($userData['password'])){
			throw new tao_install_utils_Exception("To create a super user you must provide at least a login and a password");
		}

		$superUserOntology = dirname(__FILE__) . "/../ontology/superuser.rdf";

		if (!@is_readable($superUserOntology)){
			throw new tao_install_utils_Exception("Unable to load ontology : ${superUserOntology}");
		}

		$doc = new DOMDocument();
		$doc->load($superUserOntology);

		foreach ($userData as $key => $value){
			$tags = $doc->getElementsByTagNameNS('http://www.tao.lu/Ontologies/generis.rdf#', $key);
			foreach ($tags as $tag){
				$tag->appendChild($doc->createCDATASection($value));
			}
		}
		return $this->insertLocalModel($doc->saveXML());
	}
	
	public function insertGenerisUser($login, $password){
		
		$generisUserOntology = dirname(__FILE__) . '/../ontology/generisuser.rdf';
		
		if (!@is_readable($generisUserOntology)){
			throw new tao_install_utils_Exception("Unable to load ontology : ${generisUserOntology}");
		}
		
		$doc = new DOMDocument();
		$doc->load($generisUserOntology);
		
		return $this->insertLocalModel($doc->saveXML(), array('{SYS_USER_LOGIN}'	=> $login,
															  '{SYS_USER_PASS}'		=> $password));
	}

	/**
	 * Insert a model into the local namespace
	 * @throws tao_install_utils_Exception
	 * @param string $file the path to the RDF file
	 * @return boolean true if inserted
	 */
	public function insertLocalModelFile($file){
		if(!file_exists($file) || !is_readable($file)){
			throw new tao_install_utils_Exception("Unable to load ontology : $file");
		}
		return $this->insertLocalModel(file_get_contents($file));
	}

	/**
	 * Insert a model into the local namespace
	 * @param string $model the XML data
	 * @return boolean true if inserted
	 */
	public function insertLocalModel($model, $replacements = array()){
		$model = str_replace('LOCAL_NAMESPACE#', $this->localNs, $model);
		$model = str_replace('{ROOT_PATH}', ROOT_PATH, $model);
		
		foreach ($replacements as $k => $r){
			$model = str_replace($k, $r, $model);
		}

		return $this->insertModel($this->localNs, $model);
	}

	/**
	 * Insert a model
	 * @throws tao_install_utils_Exception
	 * @param string $file the path to the RDF file
	 * @return boolean true if inserted
	 */
	public function insertModelFile($namespace, $file){
		if(!file_exists($file) || !is_readable($file)){
			throw new tao_install_utils_Exception("Unable to load ontology : $file");
		}
		return $this->insertModel($namespace, file_get_contents($file));
	}

	/**
	 * Insert a model
	 * @param string $model the XML data
	 * @return boolean true if inserted
	 */
	public function insertModel($namespace, $model){

		$returnValue = false;
		if(!preg_match("/#$/", $namespace)){
			$namespace .= '#';
		}

		// Init RDF API for PHP.
		$modFactory = new ModelFactory();
		$memModel 	= $modFactory->getMemModel($namespace);
		$dbModel	= $modFactory->getDefaultDbModel($namespace);

		// Load and parse the model
		$memModel->loadFromString($model, 'rdf');
		//$memModel->load($model);

		$added = 0;

		$it = $memModel->getStatementIterator();
		$size = $memModel->size();
		while ($it->hasNext()) {
			$statement = $it->next();
			if($dbModel->add($statement, SYS_USER_LOGIN) === true){
				$added++;
			}
		}

        if($size > 0 && $added > 0){
			$returnValue = true;
        }

        error_reporting(E_ALL);

        return $returnValue;
	}

	/**
	 * Conveniance method to get the list of models to install from the extensions
	 * @param array $simpleExtensions array of common_ext_Extension
	 * @return array of ns => files (array)
	 */
	public static function getModelsFromExtensions(array $simpleExtensions){
		$models = array();
		foreach($simpleExtensions as $extension){
			if(!$extension instanceof common_ext_Extension){
				throw new tao_install_utils_Exception("{$extension} is not a common_ext_Extension");
			}

			if(isset($extension->installFiles['rdf'])){
				$rdfFiles = $extension->installFiles['rdf'];
				//$rdfFiles : array of structure (ns, file)
				foreach($rdfFiles as $struct){
					foreach($extension->model as $model){
						if($model == $struct['ns']){
							if (!isset($models[$struct['ns']])) $models[$struct['ns']] = array();
							$models[$struct['ns']][] = $struct['file'];
							break;
						}
					}
				}
			}
		}
		return $models;
	}

	/**
	 * Convenience method to get the models to install from extension's locales.
	 * @param common_ext_Extension a common_ext_Extension instance.
	 * @return array of ns => files
	 */
	public static function getTranslationModelsFromExtension(common_ext_Extension $simpleExtension){
		$models = array();
		$extensionPath = dirname(__FILE__) . '/../../../' . $simpleExtension->getID();
		$localesPath = $extensionPath . '/locales';

		// Get the target model.
		if (!isset($simpleExtension->model) || empty($simpleExtension->model) || !isset($simpleExtension->installFiles['rdf'])) {
			return $models;
		}

        // Detect the models that are installed with the extension
        // to look for similar rdf file names in locales.
        $installModelsBaseNames = array();
		foreach ($simpleExtension->installFiles['rdf'] as $installFile){
		    
            if (!isset($installModelsBaseNames[$installFile['ns']])){
                $installModelsBaseNames[$installFile['ns']] = array();
            }
            
		    $installModelsBaseNames[$installFile['ns']][] = basename($installFile['file']);
		}

		if (@is_dir($localesPath) && is_readable($localesPath)) {
			// Locales directory exists and is readable.
			$directories = scandir($localesPath);

			if ($directories !== false) {

				foreach ($directories as $dir) {
					if ($dir[0] != '.' && $dir != '_raw' && is_dir($localesPath . '/' . $dir)) {
						// Let's scan each language directory to find the messages.rdf file.
						$files = scandir($localesPath . '/' . $dir);

						if ($files !== false){
							foreach ($files as $file) {
							    foreach ($installModelsBaseNames as $ns => $rdfFiles){
    								if ($file[0] != '.' && in_array($file, $rdfFiles)){
    
    									// Add this file to the return results.
    									if (!isset($models[$ns])) {
    										$models[$ns] = array();
    									}
    
    									$models[$ns][] = $localesPath . '/' . $dir . '/' . $file;
    								}
    							}
                            }
						} else {
							throw new tao_install_utils_Exception("Unable to list files from language directory ' ${dir}'.");
						}
					}
				}

			} else {
				throw new tao_install_utils_Exception("Unable to read 'locales' from extension '" . $simpleExtension->name . "'");
			}
		} else {
			throw new tao_install_utils_Exception("Cannot read 'locales' directory in extension '" . $simpleExtension->name . "'.");
		}

		return $models;
	}

    /**
     * Convenience method that returns available language descriptions to be inserted in the
     * knowledge base.
     * 
     * @return array of ns => files
     */
    public function getLanguageModels() {
        $models = array();
        $ns = $this->localNs;
        
        $extensionPath = dirname(__FILE__) . '/../../../tao';
        $localesPath = $extensionPath . '/locales';
        
        if (@is_dir($localesPath) && @is_readable($localesPath)) {
            $localeDirectories = scandir($localesPath);
            
            foreach ($localeDirectories as $localeDir) {
                $path = $localesPath . '/' . $localeDir;
                if ($localeDir[0] != '.' && @is_dir($path)){
                    // Look if the lang.rdf can be read.
                    $languageModelFile = $path . '/lang.rdf';
                    if (@file_exists($languageModelFile) && @is_readable($languageModelFile)){
                        // Add this file to the returned values.
                        if (!isset($models[$ns])){
                            $models[$ns] = array();
                        }
                        
                        $models[$ns][] = $languageModelFile;
                    }
                }
            }
        
            return $models;
        }
        else{
            throw new tao_install_utils_Exception("Cannot read 'locales' directory in extenstion 'tao'.");
        }
    }
}
?>