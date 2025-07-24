<?php
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class JoomsportViewpredround_list extends JViewLegacy
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
        $items = $this->_model->_data;
        $lists = $this->_model->_lists;
        $total = $this->_model->_total;
        $pagination = $this->_model->_pagination;

        $this->addToolbar();
        $user = JFactory::getUser();
        $this->assignRef('user',        $user);
        $this->assignRef('lists',        $lists);
        $this->assignRef('rows',        $items);
        $this->assignRef('page',    $pagination);
        $this->assignRef('season_id',        $season_id);

        require_once dirname(__FILE__).'/tmpl/default'.($tpl ? '_'.$tpl : '').'.php';
    }

    protected function addToolbar()
    {
        if (JFactory::getUser()->authorise('core.edit', 'com_joomsport')) {
            JToolBarHelper::addNew('predround_add');
            JToolBarHelper::editList('predround_edit');
        }
        JToolBarHelper::title(JText::_('JSPL_PREDICTION_ROUND_LIST'), 'player.png');
        if (JFactory::getUser()->authorise('core.delete', 'com_joomsport')) {
            JToolBarHelper::deleteList('', 'predround_del', JText::_('BLBE_DELETE'));
        }
    }
}
