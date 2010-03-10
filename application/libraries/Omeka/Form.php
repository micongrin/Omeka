<?php
/**
 * @version $Id$
 * @copyright Center for History and New Media, 2009
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka
 **/

/**
 * A Zend_Form subclass that sets up forms to be properly displayed in Omeka.
 *
 * @package Omeka
 * @copyright Center for History and New Media, 2009
 **/
class Omeka_Form extends Zend_Form
{
    /**
     * @var string Class name of Omeka DisplayGroup subclass.
     */
    protected $_defaultDisplayGroupClass = 'Omeka_Form_DisplayGroup';
    
    /**
     * @var boolean Whether or not to automatically apply Omeka-specific decorators
     * and styling information to form elements prior to rendering.
     */
    protected $_autoApplyOmekaStyles = true;
    
    public function init()
    {
        $this->addElementPrefixPath('Omeka_', 'Omeka/');
        
        // set the default element decorators
        $this->setElementDecorators($this->getDefaultElementDecorators());
    }
    
    public function loadDefaultDecorators()
    {
        $this->setDecorators(array(
            'FormElements',
            'Form'
        ));    
    }
    
    public function getDefaultElementDecorators()
    {
        // <div class="field">
        //     <label for="whatever">Label Name</label>
        // 
        //     <div class="inputs">
        //         <input name="whatever" type="text" />
        //         <ul class="errors">
        //             <li>Here's your error</li>
        //         </ul>
        //     </div>
        // 
        //     <p class="explanation">Here's the explanation</p>
        // 
        // </div>
        // <div>
        // <input type="submit" />
        // </div>
            
        return array(
                        'ViewHelper', 
                        'Errors', 
                        array(array('InputsTag' => 'HtmlTag'), array('tag' => 'div', 'class' => 'inputs')), 
                        array('Description', array('tag' => 'p', 'class' => 'explanation')), 
                        'Label', 
                        array(array('FieldTag' => 'HtmlTag'), array('tag' => 'div', 'class' => 'field'))
                    );
    }
    
    /**
     * Configure element styles / decorators based on the type of element.
     * 
     * This may be called after elements to the form, as the decorator 
     * configuration in init() runs before elements can be added.
     */
    public function applyOmekaStyles()
    {
        foreach ($this->getElements() as $element) {
            if ($element instanceof Zend_Form_Element_Submit) {
                // All submit form elements should have class 'submit'.
                $this->_addClassNameToElement($element, 'submit');
                // All submit form elements should be wrapped in a div with 
                // no class.
                $element->setDecorators(array(
                    'ViewHelper', 
                    array('HtmlTag', array('tag' => 'div'))));
            } else if ($element instanceof Zend_Form_Element_Text) {
                // Text inputs should have class = "textinput".
                $this->_addClassNameToElement($element, 'textinput');
            } else if ($element instanceof Zend_Form_Element_Textarea) {
                $this->_addClassNameToElement($element, 'textinput');
            } else if ($element instanceof Zend_Form_Element_Password) {
                $this->_addClassNameToElement($element, 'textinput');
            } else if ($element instanceof Zend_Form_Element_File) {       
                $this->_addClassNameToElement($element, 'fileinput');
                $element->setDecorators(array(
                    'File', 
                    'Errors', 
                    array(array('InputsTag' => 'HtmlTag'), array('tag' => 'div', 'class' => 'inputs')), 
                    array('Description', array('tag' => 'p', 'class' => 'explanation')), 
                    'Label', 
                    array(array('FieldTag' => 'HtmlTag'), array('tag' => 'div', 'class' => 'field'))
                ));
            }
        }
    }
    
    /**
     * Retrieve all of the form error messages as a nicely formatted string.  
     * 
     * Useful for displaying all form errors at the top of a form, or for flashing
     * form errors after redirects.
     * 
     * @since 1.2
     * @param string $messageDelimiter The string to display between different
     * error messages for an element.
     * @param string $elementDelimiter The string to display between different
     * elements.
     * @return string
     */
    public function getMessagesAsString($messageDelimiter = '  ', $elementDelimiter = ', ')
    {
        $errors = array();
        foreach ($this->getMessages() as $elementName => $errorArray) {
            $errors[] = Inflector::humanize($elementName) . ': ' . join($messageDelimiter, $errorArray);
        }
        return join($elementDelimiter, $errors);
    }
    
    /**
     * Specify whether or not to automatically apply Omeka-specific decorators
     * and styles prior to rendering the form.
     */
    public function setAutoApplyOmekaStyles($flag)
    {
        $this->_autoApplyOmekaStyles = (boolean)$flag;
    }
    
    public function render(Zend_View_Interface $view = null)
    {        
        if ($this->_autoApplyOmekaStyles) {
            $this->applyOmekaStyles();
        }
        return parent::render($view);
    }
    
    /**
     * Add a specific class name to an element.
     */
    private function _addClassNameToElement(Zend_Form_Element $element, $className)
    {
        $existingClassName = $element->getAttrib('class');
        $newClassName = (strpos($existingClassName, $className) !== false) ? $existingClassName : "$existingClassName $className";
        $element->setAttrib('class', $newClassName);
    }
}