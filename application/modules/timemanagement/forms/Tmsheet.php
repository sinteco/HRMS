<?php
/********************************************************************************* 
 *  This file is part of Sentrifugo.
 *  Copyright (C) 2014 Sapplica
 *   
 *  Sentrifugo is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  Sentrifugo is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with Sentrifugo.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  Sentrifugo Support <support@sentrifugo.com>
 ********************************************************************************/

class Timemanagement_Form_Tmsheet extends Zend_Form
{
	public function init()
	{
		$this->setMethod('post');
		$this->setAttrib('action',BASE_URL.'timemanagement/tmsheetconfigration/edit');
		$this->setAttrib('id', 'formid');
		$this->setAttrib('name', 'tmsheetconfigration');


        $id = new Zend_Form_Element_Hidden('id');
		
        $from = new Zend_Form_Element_Text('from');
		$from->setOptions(array('class' => 'fromdatePicker'));
        //$date_of_leaving->setAttrib('onchange', 'validatejoiningdate(this)'); 		
		// $from->setAttrib('readonly', 'true');
		// $from->setAttrib('onfocus', 'this.blur()');
		$from->setRequired(true);
		
		$to = new Zend_Form_Element_Text('to');
		$to->setOptions(array('class' => 'todatePicker'));
        //$date_of_leaving->setAttrib('onchange', 'validatejoiningdate(this)'); 		
		// $to->setAttrib('readonly', 'true');
		// $to->setAttrib('onfocus', 'this.blur()');
		$to->setRequired(true);
      
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setAttrib('id', 'submitbutton');
		$submit->setLabel('Save');

		$year = new Zend_Form_Element_Text("year");
		$year->setAttrib('maxLength', 4);
		$year->setOptions(array('class'=>'date-picker-year'));
		$year->addFilter(new Zend_Filter_StringTrim());
		$year->addValidator("regex", false, array("/^([0-9]*\:?[0-9]{1,2})$/","messages"=>"Please enter a valid year."));
		$year->setRequired(true);

		$month = new Zend_Form_Element_Text("month");
		$month->setAttrib('maxLength', 2);
		$month->setOptions(array('class'=>'date-picker'));
		$month->addFilter(new Zend_Filter_StringTrim());
		$month->addValidator("regex", false, array("/^([0-9]*\:?[0-9]{1,2})$/","messages"=>"Please enter a valid month."));
		$month->setRequired(true);

		 $this->addElements(array($id,$from,$to,$submit,$year,$month));
         $this->setElementDecorators(array('ViewHelper')); 
	}
}