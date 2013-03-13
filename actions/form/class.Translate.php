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
 * Create a form to translate a resource of the ontology
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage actions_form
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * Create a form from a  resource of your ontology. 
 * Each property will be a field, regarding it's widget.
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
require_once('tao/actions/form/class.Instance.php');

/* user defined includes */
// section 127-0-1-1-56df1631:1284f2fd9c5:-8000:000000000000248E-includes begin
// section 127-0-1-1-56df1631:1284f2fd9c5:-8000:000000000000248E-includes end

/* user defined constants */
// section 127-0-1-1-56df1631:1284f2fd9c5:-8000:000000000000248E-constants begin
// section 127-0-1-1-56df1631:1284f2fd9c5:-8000:000000000000248E-constants end

/**
 * Create a form to translate a resource of the ontology
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage actions_form
 */
class tao_actions_form_Translate
    extends tao_actions_form_Instance
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Initialize the form
     *
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    protected function initForm()
    {
        // section 127-0-1-1-56df1631:1284f2fd9c5:-8000:000000000000249A begin
        
    	parent::initForm();
    	$this->form->setName('translate_'.$this->form->getName());
		
    	$actions = tao_helpers_form_FormFactory::getCommonActions('top');
		$this->form->setActions($actions, 'top');
		$this->form->setActions($actions, 'bottom');
    	
        // section 127-0-1-1-56df1631:1284f2fd9c5:-8000:000000000000249A end
    }

    /**
     * Initialize the form elements
     *
     * @access protected
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    protected function initElements()
    {
        // section 127-0-1-1-56df1631:1284f2fd9c5:-8000:000000000000249D begin
        
    	parent::initElements();
    	
    	$elements = $this->form->getElements();
    	$this->form->setElements(array());
    	
    	
    	$currentLangElt = tao_helpers_form_FormFactory::getElement('current_lang', 'Textbox');
		$currentLangElt->setDescription(__('Current language'));
		$currentLangElt->setAttributes(array('readonly' => 'true'));
		$currentLangElt->setValue(core_kernel_classes_Session::singleton()->getDataLanguage());	//API lang /data lang
		$this->form->addElement($currentLangElt);
		
		$dataLangElement = tao_helpers_form_FormFactory::getElement('translate_lang', 'Combobox');
		$dataLangElement->setDescription(__('Translate to'));
		$dataLangElement->setOptions(tao_helpers_I18n::getAvailableLangs(true));
		$dataLangElement->setEmptyOption(__('Select a language'));
		$dataLangElement->addValidator( tao_helpers_form_FormFactory::getValidator('NotEmpty') );
		$this->form->addElement($dataLangElement);
		
		$this->form->createGroup('translation_info', __('Translation parameters'), array('current_lang', 'translate_lang'));
    	
		$dataGroup = array();
		foreach($elements as $element){
			
			if( $element instanceof tao_helpers_form_elements_Hidden ||
				$element->getName() == 'uri' || $element->getName() == 'classUri'){
					
				$this->form->addElement($element);
				
			}
			else{
				
				$propertyUri = tao_helpers_Uri::decode($element->getName());
				$property = new core_kernel_classes_Property($propertyUri);
				
				//translate only language dependent properties or Labels
				//supported widget are: Textbox, TextArea, HtmlArea
				//@todo support other widgets
				if(	( $property->isLgDependent() && 
					  ($element instanceof tao_helpers_form_elements_Textbox ||
					   $element instanceof tao_helpers_form_elements_TextArea ||
					   $element instanceof tao_helpers_form_elements_HtmlArea
					  ) ) ||
					$propertyUri == RDFS_LABEL){	
				
					$translatedElt = clone $element;
					
					$viewElt = tao_helpers_form_FormFactory::getElement('view_'.$element->getName(), 'Label');
					$viewElt->setDescription($element->getDescription());
					$viewElt->setValue($element->getValue());
					$viewElt->setAttribute('no-format', true);
					if ($element instanceof tao_helpers_form_elements_HtmlArea){
						$viewElt->setAttribute('htmlentities', false);
					}
					
					$this->form->addElement($viewElt);
					
					$dataGroup[] = $viewElt->getName();
					
					$translatedElt->setDescription(' ');
					$translatedElt->setValue('');
					if($propertyUri == RDFS_LABEL){
						$translatedElt->setForcedValid();
					}
				
					$this->form->addElement($translatedElt);
					
					$dataGroup[] = $translatedElt->getName();
				}
			}
		}
		
		$this->form->createGroup('translation_form', __('Translate'), $dataGroup);
		
        // section 127-0-1-1-56df1631:1284f2fd9c5:-8000:000000000000249D end
    }

} /* end of class tao_actions_form_Translate */

?>