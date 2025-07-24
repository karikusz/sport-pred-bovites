<?php
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class JoomsportViewprediction_config extends JViewLegacy
{
    public $_model = null;
    public function __construct(&$model)
    {
        $this->_model = $model;
    }
    public function display($tpl = null)
    {
        global $mainframe, $option;

        $db = JFactory::getDBO();
        $uri = JFactory::getURI();

        // Get data from the model
        
        $lists = $this->_model->_lists;
        // $editor = JEditor::getInstance();
        $editor = JFactory::getEditor();
        $this->addToolbar($this->_model->_mode);

        $this->editor = $editor;
        $this->lists = $lists;

        require_once dirname(__FILE__).'/tmpl/default'.($tpl ? '_'.$tpl : '').'.php';
    }

    protected function addToolbar($edit)
    {
        JToolBarHelper::title(JText::_('BLBE_MENCONF'), 'config.png');
        if (JFactory::getUser()->authorise('core.edit', 'com_joomsport')) {
            JToolBarHelper::apply('save_prediction_config');
        }
    }
}
