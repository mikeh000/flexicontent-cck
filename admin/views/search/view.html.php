<?php
/**
 * @version 1.5 stable $Id: view.html.php 1902 2014-05-10 16:06:11Z ggppdk $ 
 * @package Joomla
 * @subpackage FLEXIcontent
 * @copyright (C) 2009 Emmanuel Danan - www.vistamedia.fr
 * @license GNU/GPL v2
 * 
 * FLEXIcontent is a derivative work of the excellent QuickFAQ component
 * @copyright (C) 2008 Christoph Lukes
 * see www.schlu.net for more information
 *
 * FLEXIcontent is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.view');

/**
 * View class for the FLEXIcontent search indexes screen
 *
 * @package Joomla
 * @subpackage FLEXIcontent
 * @since 1.0
 */
class FLEXIcontentViewSearch extends JViewLegacy
{
	function display( $tpl = null )
	{
		$layout = JRequest::getVar('layout', 'default');
		if($layout=='indexer') {
			$this->indexer($tpl);
			return;
		}
		$app      = JFactory::getApplication();
		$cparams  = JComponentHelper::getParams( 'com_flexicontent' );
		$db       = JFactory::getDBO();
		$document = JFactory::getDocument();
		$option   = JRequest::getCmd('option');
		$view     = JRequest::getVar('view');
		
		$print_logging_info = $cparams->get('print_logging_info');
		if ( $print_logging_info )  global $fc_run_times;
		
		flexicontent_html::loadFramework('select2');
		JHTML::_('behavior.tooltip');

		// Get filters
		$count_filters = 0;
		
		// Get filter vars
		$filter_order     = $app->getUserStateFromRequest( $option.'.'.$view.'.filter_order', 		'filter_order',     'a.title', 'cmd' );
		$filter_order_Dir = $app->getUserStateFromRequest( $option.'.'.$view.'.filter_order_Dir',	'filter_order_Dir', '', 'word' );
		
		$filter_fieldtype	= $app->getUserStateFromRequest( $option.'.'.$view.'.filter_fieldtype', 	'filter_fieldtype', 	'', 'word' );
		$filter_itemtype	= $app->getUserStateFromRequest( $option.'.'.$view.'.filter_itemtype', 	'filter_itemtype', 		'', 'int' );
		$filter_itemstate	= $app->getUserStateFromRequest( $option.'.'.$view.'.filter_itemstate', 'filter_itemstate', 	'', 'word' );
		if ($filter_fieldtype) $count_filters++; if ($filter_itemtype) $count_filters++; if ($filter_itemstate) $count_filters++;
		
		$search			= $app->getUserStateFromRequest( $option.'.'.$view.'.search',			'search', '', 'string' );
		$search			= FLEXI_J16GE ? $db->escape( trim(JString::strtolower( $search ) ) ) : $db->getEscaped( trim(JString::strtolower( $search ) ) );
		
		$search_itemtitle	= $app->getUserStateFromRequest( $option.'.'.$view.'.search_itemtitle',	'search_itemtitle', '', 'string' );
		$search_itemid		= $app->getUserStateFromRequest( $option.'.'.$view.'.search_itemid',	'search_itemid', '', 'string' );
		$search_itemid		= !empty($search_itemid) ? (int)$search_itemid : '';
		if ($search_itemtitle) $count_filters++; if ($search_itemid) $count_filters++;
		
		$filter_indextype	= $app->getUserStateFromRequest( $option.'.'.$view.'.filter_indextype',		'filter_indextype',	'advanced',		'word' );
		
		$f_active['filter_fieldtype']	= (boolean)$filter_fieldtype;
		$f_active['filter_itemtype']	= (boolean)$filter_itemtype;
		$f_active['filter_itemstate']	= (boolean)$filter_itemstate;
		
		$f_active['search']			= strlen($search);
		$f_active['search_itemtitle']	= strlen($search_itemtitle);
		$f_active['search_itemid']		= (boolean)$search_itemid;
		
		// Add custom css and js to document
		$document->addStyleSheet(JURI::base(true).'/components/com_flexicontent/assets/css/flexicontentbackend.css');
		if      (FLEXI_J30GE) $document->addStyleSheet(JURI::base(true).'/components/com_flexicontent/assets/css/j3x.css');
		else if (FLEXI_J16GE) $document->addStyleSheet(JURI::base(true).'/components/com_flexicontent/assets/css/j25.css');
		else                  $document->addStyleSheet(JURI::base(true).'/components/com_flexicontent/assets/css/j15.css');
		
		// Create Submenu (and also check access to current view)
		FLEXISubmenu('CanIndex');
		
		
		// ******************
		// Create the toolbar
		// ******************
		
		// Create document/toolbar titles
		$doc_title = JText::_( 'FLEXI_SEARCH_INDEX' );
		$site_title = $document->getTitle();
		JToolBarHelper::title( $doc_title, FLEXI_J16GE ? 'searchtext.png' : 'searchindex' );
		$document->setTitle($doc_title .' - '. $site_title);
		
		// Create the toolbar
		$this->setToolbar();
		
		$types			= $this->get( 'Typeslist' );
		$fieldtypes = flexicontent_db::getFieldTypes($_grouped=false, $_usage=true, $_published=false);
		
		// Build select lists
		$lists = array();
		
		//build backend visible filter
		$fftypes = array();
		$fftypes[] = JHTML::_('select.option',  '', '-' /*JText::_( 'FLEXI_ALL_FIELDS_TYPE' )*/ );
		$fftypes[] = JHTML::_('select.option',  'C', JText::_( 'FLEXI_CORE_FIELDS' ) );
		$fftypes[] = JHTML::_('select.option',  'NC', JText::_( 'FLEXI_NON_CORE_FIELDS' ) );
		foreach ($fieldtypes as $field_type => $ftdata) {
			$fftypes[] = JHTML::_('select.option', $field_type, '-'.$ftdata->assigned.'- '. $field_type);
		}
		
		$lists['filter_fieldtype'] = ($filter_fieldtype || 1 ? '<label class="label">'.JText::_('FLEXI_FIELD_TYPE').'</label>' : '').
			JHTML::_('select.genericlist', $fftypes, 'filter_fieldtype', 'class="use_select2_lib" size="1" onchange="submitform( );"', 'value', 'text', $filter_fieldtype );
		
		//build type select list
		$lists['filter_itemtype'] = ($filter_itemtype|| 1 ? '<label class="label">'.JText::_('FLEXI_TYPE').'</label>' : '').
			flexicontent_html::buildtypesselect($types, 'filter_itemtype', $filter_itemtype, '-'/*true*/, 'class="use_select2_lib" size="1" onchange="submitform( );"', 'filter_itemtype');
		
		//publish unpublished filter
		$ffstates = array();
		$ffstates[] = JHTML::_('select.option',  '', '-' /*JText::_( 'FLEXI_SELECT_STATE' )*/ );
		$ffstates[] = JHTML::_('select.option',  'P', JText::_( 'FLEXI_PUBLISHED' ) );
		$ffstates[] = JHTML::_('select.option',  'U', JText::_( 'FLEXI_UNPUBLISHED' ) );
		
		$lists['filter_itemstate'] = ($filter_itemstate || 1 ? '<label class="label">'.JText::_('FLEXI_STATE').'</label>' : '').
			JHTML::_('select.genericlist', $ffstates, 'filter_itemstate', 'class="use_select2_lib" size="1" onchange="submitform( );"', 'value', 'text', $filter_itemstate );
		
		// build filter index type record listing
		//$itn['basic'] = JText::_( 'FLEXI_INDEX_BASIC' );
		$itn['advanced'] = JText::_( 'FLEXI_INDEX_ADVANCED' );
		$indextypes = array();
		//foreach ($itn as $i => $v) $indextypes[] = JHTML::_('select.option', $i, $v);
		//$lists['filter_indextype'] = JHTML::_('select.radiolist', $indextypes, 'filter_indextype', 'size="1" class="inputbox" onchange="submitform();"', 'value', 'text', $filter_indextype );
		$lists['filter_indextype'] = '';
		foreach ($itn as $i => $v) {
			$checked = $filter_indextype == $i ? ' checked="checked" ' : '';
			$lists['filter_indextype'] .= '<input type="radio" onchange="submitform();" class="inputbox" size="1" '.$checked.' value="'.$i.'" id="filter_indextype'.$i.'" name="filter_indextype" />';
			$lists['filter_indextype'] .= '<label class="" id="filter_indextype'.$i.'-lbl" for="filter_indextype'.$i.'">'.$v.'</label>';
		}
		
		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_order;
		
		// search index & item title  filter
		$lists['search']= $search;
		$lists['search_itemtitle']= $search_itemtitle;
		$lists['search_itemid']= $search_itemid;
		
		$rows   = $this->get('Data');  // MUST BE BEFORE getCount and getPagination because it also calculates total rows
		$total  = $this->get('Count');
		$pagination = $this->get('Pagination');
		$limitstart = $this->get('LimitStart');

		$js = "window.addEvent('domready', function(){";
		if ($filter_fieldtype) {
			$js .= "jQuery('.col_fieldtype').each(function(){ jQuery(this).addClass('yellow'); });";
		}		
		if ($search) {
			$js .= "jQuery('.col_search').each(function(){ jQuery(this).addClass('yellow'); });";
		}		
		$js .= "});";
		$document->addScriptDeclaration($js);

		$query = "SHOW VARIABLES LIKE '%ft_min_word_len%'";
		$db->setQuery($query);
		$_dbvariable = $db->loadObject();
		$ft_min_word_len = (int) @ $_dbvariable->Value;
		$notice_ft_min_word_len	= $app->getUserStateFromRequest( $option.'.fields.notice_ft_min_word_len',	'notice_ft_min_word_len',	0, 'int' );
		//if ( $cparams->get('show_usability_messages', 1) )     // Important usability messages
		//{
			if ( $notice_ft_min_word_len < 2) {
				$app->setUserState( $option.'.fields.notice_ft_min_word_len', $notice_ft_min_word_len+1 );
				$app->enqueueMessage("NOTE : Database limits minimum search word length (ft_min_word_len) to ".$ft_min_word_len, 'notice');
				//$app->enqueueMessage(JText::_('FLEXI_USABILITY_MESSAGES_TURN_OFF'), 'message');
			}
		//}
		
		$this->assignRef('count_filters', $count_filters);
		$this->assignRef('lists',	$lists);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('rows', $rows);
		$this->assignRef('total', $total);
		$this->assignRef('limitstart', $limitstart);
		$this->assignRef('f_active', $f_active);
		
		$this->assignRef('option', $option);
		$this->assignRef('view', $view);
		
		$this->sidebar = FLEXI_J30GE ? JHtmlSidebar::render() : null;
		parent::display($tpl);
	}
	
	
	/**
	 * Method to configure the toolbar for this view.
	 *
	 * @access	public
	 * @return	void
	 */
	function setToolbar() {
		$document = JFactory::getDocument();
		$js = "window.addEvent('domready', function(){";
		$toolbar = JToolBar::getInstance('toolbar');

		$btn_task = '';
		$popup_load_url = JURI::base().'index.php?option=com_flexicontent&view=search&layout=indexer&tmpl=component&indexer=basic';
		if (FLEXI_J30GE || !FLEXI_J16GE) {  // Layout of Popup button broken in J3.1, add in J1.5 it generates duplicate HTML tag id (... just for validation), so add manually
			$js .= "
				jQuery('#toolbar-basicindex a.toolbar, #toolbar-basicindex button')
					.attr('onclick', 'javascript:;')
					.attr('href', '".$popup_load_url."')
					.attr('rel', '{handler: \'iframe\', size: {x: 500, y: 240}, onClose: function() {}}');
			";
			JToolBarHelper::custom( $btn_task, 'basicindex.png', 'basicindex_f2.png', 'FLEXI_INDEX_BASIC_CONTENT_LISTS', false );
			JHtml::_('behavior.modal', '#toolbar-basicindex a.toolbar, #toolbar-basicindex button');
		} else {
			$toolbar->appendButton('Popup', 'basicindex', 'FLEXI_INDEX_BASIC_CONTENT_LISTS', str_replace('&', '&amp;', $popup_load_url), 500, 240);
		}
		
		JToolBarHelper::divider();  JToolBarHelper::spacer();
		
		$btn_task = '';
		$popup_load_url = JURI::base().'index.php?option=com_flexicontent&view=search&layout=indexer&tmpl=component&indexer=advanced';
		if (FLEXI_J30GE || !FLEXI_J16GE) {  // Layout of Popup button broken in J3.1, add in J1.5 it generates duplicate HTML tag id (... just for validation), so add manually
			$js .= "
				jQuery('#toolbar-advindex a.toolbar, #toolbar-advindex button')
					.attr('onclick', 'javascript:;')
					.attr('href', '".$popup_load_url."')
					.attr('rel', '{handler: \'iframe\', size: {x: 500, y: 240}, onClose: function() {}}');
			";
			JToolBarHelper::custom( $btn_task, 'advindex.png', 'advindex_f2.png', 'FLEXI_INDEX_ADVANCED_SEARCH_VIEW', false );
			JHtml::_('behavior.modal', '#toolbar-advindex a.toolbar, #toolbar-advindex button');
		} else {
			$toolbar->appendButton('Popup', 'advindex', 'FLEXI_INDEX_ADVANCED_SEARCH_VIEW', str_replace('&', '&amp;', $popup_load_url), 500, 240);
		}
		
		$btn_task = '';
		$popup_load_url = JURI::base().'index.php?option=com_flexicontent&view=search&layout=indexer&tmpl=component&indexer=advanced&rebuildmode=quick';
		if (FLEXI_J30GE || !FLEXI_J16GE) {  // Layout of Popup button broken in J3.1, add in J1.5 it generates duplicate HTML tag id (... just for validation), so add manually
			$js .= "
				jQuery('#toolbar-advindexdirty a.toolbar, #toolbar-advindexdirty button')
					.attr('onclick', 'javascript:;')
					.attr('href', '".$popup_load_url."')
					.attr('rel', '{handler: \'iframe\', size: {x: 500, y: 240}, onClose: function() {}}');
			";
			JToolBarHelper::custom( $btn_task, 'advindexdirty.png', 'advindexdirty_f2.png', 'FLEXI_INDEX_ADVANCED_SEARCH_VIEW_DIRTY_ONLY', false );
			JHtml::_('behavior.modal', '#toolbar-advindexdirty a.toolbar, #toolbar-advindexdirty button');
		} else {
			$toolbar->appendButton('Popup', 'advindexdirty', 'FLEXI_INDEX_ADVANCED_SEARCH_VIEW_DIRTY_ONLY', str_replace('&', '&amp;', $popup_load_url), 500, 240);
		}
		
		$toolbar->appendButton('Confirm', 'FLEXI_DELETE_INDEX_CONFIRM', 'trash', 'FLEXI_INDEX_ADVANCED_PURGE', FLEXI_J16GE ? 'search.purge' : 'purge', false);
		
		$user  = JFactory::getUser();
		$perms = FlexicontentHelperPerm::getPerm();
		if ($perms->CanConfig) {
			JToolBarHelper::divider(); JToolBarHelper::spacer();
			$session = JFactory::getSession();
			$fc_screen_width = (int) $session->get('fc_screen_width', 0, 'flexicontent');
			$_width  = ($fc_screen_width && $fc_screen_width-84 > 940 ) ? ($fc_screen_width-84 > 1400 ? 1400 : $fc_screen_width-84 ) : 940;
			$fc_screen_height = (int) $session->get('fc_screen_height', 0, 'flexicontent');
			$_height = ($fc_screen_height && $fc_screen_height-128 > 550 ) ? ($fc_screen_height-128 > 1000 ? 1000 : $fc_screen_height-128 ) : 550;
			JToolBarHelper::preferences('com_flexicontent', $_height, $_width, 'Configuration');
		}
		
		$js .= "});";
		$document->addScriptDeclaration($js);
	}
	
	function indexer($tpl)
	{		
		parent::display($tpl);
	}
}
