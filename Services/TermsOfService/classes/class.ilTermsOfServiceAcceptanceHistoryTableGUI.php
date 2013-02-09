<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceTableGUI.php';
require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceAcceptanceHistoryTableGUI extends ilTermsOfServiceTableGUI
{
	/**
	 * @param ilObjectGUI $a_parent_obj
	 * @param string      $a_parent_cmd
	 */
	public function __construct(ilObjectGUI $a_parent_obj, $a_parent_cmd)
	{
		/**
		 * @var $ilCtrl ilCtrl
		 */
		global $ilCtrl;

		$this->ctrl = $ilCtrl;

		// Call this immediately in constructor
		$this->setId('tos_agreement_by_lng');

		$this->setDefaultOrderDirection('DESC');
		$this->setDefaultOrderField('ts');
		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setTitle($this->lng->txt('tos_acceptance_history'));

		$this->addColumn($this->lng->txt('tos_acceptance_datetime'), 'ts');
		$this->addColumn($this->lng->txt('login'), 'login');
		$this->optionalColumns        = (array)$this->getSelectableColumns();
		$this->visibleOptionalColumns = (array)$this->getSelectedColumns();
		foreach($this->visibleOptionalColumns as $column)
		{
			$this->addColumn($this->optionalColumns[$column]['txt'], $column);
		}
		$this->addColumn($this->lng->txt('language'), 'lng');
		$this->addColumn($this->lng->txt('tos_agreement_file'), 'path');

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, 'applyAcceptanceHistoryFilter'));

		$this->setRowTemplate('tpl.tos_acceptance_history_table_row.html', 'Services/TermsOfService');

		$this->setShowRowsSelector(true);

		$this->initFilter();
		$this->setFilterCommand('applyAcceptanceHistoryFilter');
		$this->setResetCommand('resetAcceptanceHistoryFilter');
	}

	/**
	 * @return array
	 */
	public function getSelectableColumns()
	{
		$cols = array(
			'firstname' => array('txt' => $this->lng->txt('firstname'), 'default' => false),
			'lastname' => array('txt' => $this->lng->txt('lastname'), 'default' => false)
		);

		return $cols;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	protected function prepareData(array &$data)
	{
		foreach($data['items'] as &$row)
		{
			$row['lng'] = $this->lng->txt('meta_l_' . $row['lng']);
		}
	}

	/**
	 * @param array $row
	 * @return array
	 */
	protected function prepareRow(array &$row)
	{
		$row['id']       = md5($row['usr_id'].$row['ts']);
		$row['img_down'] = ilUtil::getImagePath(ilAdvancedSelectionListGUI::DOWN_ARROW_DARK);
	}

	/**
	 * @return array
	 */
	protected function getStaticData()
	{
		return array('ts', 'login', 'lng', 'path', 'text', 'id', 'img_down');
	}

	/**
	 * @param mixed $column
	 * @param array $row
	 * @return mixed
	 */
	protected function formatCellValue($column, array $row)
	{
		if($column == 'ts')
		{
			return ilDatePresentation::formatDate(new ilDateTime($row[$column], IL_CAL_UNIX));
		}

		return $row[$column];
	}

	/**
	 * @param string $column
	 * @return bool
	 */
	public function numericOrdering($column)
	{
		if('ts' == $column)
		{
			return true;
		}

		return false;
	}

	/**
	 * 
	 */
	public function initFilter()
	{
		/**
		 * @var $tpl ilTemplate
		 */
		global $tpl;

		include_once 'Services/Form/classes/class.ilTextInputGUI.php';
		$ul = new ilTextInputGUI($this->lng->txt('login').'/'.$this->lng->txt('email').'/'.$this->lng->txt('name'), 'query');
		$ul->setDataSource($this->ctrl->getLinkTarget($this->getParentObject(), 'addUserAutoComplete', '', true));
		$ul->setSize(20);
		$ul->setSubmitFormOnEnter(true);
		$this->addFilterItem($ul);
		$ul->readFromSession();
		$this->filter['query'] = $ul->getValue();

		include_once 'Services/Form/classes/class.ilSelectInputGUI.php';
		$options = array();
		$languages = ilObject::_getObjectsByType('lng');
		foreach($languages as $lng)
		{
			$options[$lng['title']] = $this->lng->txt('meta_l_' . $lng['title']);
		}
		asort($options);
		
		$options = array('' => $this->lng->txt('any_language')) + $options;
		
		$si = new ilSelectInputGUI($this->lng->txt('language'), 'lng');
		$si->setOptions($options);
		$this->addFilterItem($si);
		$si->readFromSession();
		$this->filter['lng'] = $si->getValue();
		
		include_once 'Services/Form/classes/class.ilDateDurationInputGUI.php';
		$tpl->addJavaScript('./Services/Form/js/date_duration.js');
		$duration = new ilDateDurationInputGUI($this->lng->txt('tos_period'), 'period');
		$duration->setStartText($this->lng->txt('tos_period_from'));
		$duration->setEndText($this->lng->txt('tos_period_until'));
		$duration->setStart(new ilDateTime(time(), IL_CAL_UNIX));
		$duration->setEnd(new ilDateTime(time(), IL_CAL_UNIX));
		$duration->setMinuteStepSize(5);
		$duration->setShowTime(true);
		$duration->setShowDate(true);
		$this->addFilterItem($duration, true);
		$duration->readFromSession();
		$this->optional_filter['period'] = $duration->getValue();
	}
}
