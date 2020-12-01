<?php
/********************************************************************************* 
 *  This file is part of Sentrifugo.
 *  Copyright (C) 2015 Sapplica
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

class Default_Model_Addemployeeleaves extends Zend_Db_Table_Abstract
{
    protected $_name = 'main_employeeleaves';
    protected $_primary = 'id';		

	/*
	   I. This query fetches employees data based on roles.
	*/
    public function getEmployeesData($sort,$by,$pageNo,$perPage,$searchQuery,$managerid='',$loginUserId)
    {
        //the below code is used to get data of employees from summary table.
        $employeesData="";                             
        $where = "  e.isactive = 1 AND e.user_id != ".$loginUserId." 
        			and r.group_id NOT IN (".MANAGEMENT_GROUP.",".USERS_GROUP.")
        			";  
       
        if($searchQuery != '')
            $where .= " AND ".$searchQuery;

        $employeesData = $this->select()
                                ->setIntegrityCheck(false)	                                
                                ->from(array('e' => 'main_employees_summary'),array('id'=>'e.user_id','e.firstname','e.lastname','e.employeeId'))
                                ->joinLeft(array('r'=>'main_roles'), 'e.emprole=r.id',array())  
                                ->joinLeft(array('el'=>'main_employeeleaves'), 'el.user_id=e.user_id',array('COALESCE(SUM(IF(lr.leavestatus="Approved" and lr.leavetypeid=15 and lr.from_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE) and lr.to_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE),lr.appliedleavescount,0)),0) as used_leaves','20+(ROUND((DATEDIFF(now(),e.date_of_joining)/365.25),2))-1 as emp_leave_limit','ROUND((DATEDIFF(now(),e.date_of_joining)/365.25),2) as serviceyear','ROUND(IF(e.date_of_joining <= concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "),DATEDIFF(now(),concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "))/365.25, DATEDIFF(now(),e.date_of_joining)/365.25)*(20+(ROUND((DATEDIFF(now(),e.date_of_joining)/365.25),2))-1),2) as leaveX','e.date_of_joining as alloted_year','IF((DATEDIFF(concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "),e.date_of_joining)/30)>=12,IF(MONTH(now())>3,0,IF(((DATEDIFF(now(),concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "))/365.25)*(20+(ROUND((DATEDIFF(concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "),e.date_of_joining)/365.25),2))-1))-el.used_leaves<10,10,((DATEDIFF(now(),concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "))/365.25)*(20+(ROUND((DATEDIFF(concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "),e.date_of_joining)/365.25),2))-1))-el.used_leaves)),
                                IF((DATEDIFF(now(),e.date_of_joining)/30)>=12&&MONTH(now())>3,0,IF((ROUND((DATEDIFF(concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "),e.date_of_joining)/365.25)*(20+(ROUND((DATEDIFF(concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "),e.date_of_joining)/365.25),2))-1),2)-el.used_leaves)>0,(ROUND((DATEDIFF(concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "),e.date_of_joining)/365.25)*(20+(ROUND((DATEDIFF(concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "),e.date_of_joining)/365.25),2))-1),2)-el.used_leaves),0))
                                ) as transfer','el.createddate','el.isleavetrasnferset','ROUND(ROUND(IF(e.date_of_joining <= concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "),DATEDIFF(now(),concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "))/365.25, DATEDIFF(now(),e.date_of_joining)/365.25)*(20+(ROUND((DATEDIFF(now(),e.date_of_joining)/365.25),2))-1),2) + ROUND(IF((DATEDIFF(concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "),e.date_of_joining)/30)>=12,IF(MONTH(now())>3,0,IF(((DATEDIFF(now(),concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "))/365.25)*(20+(ROUND((DATEDIFF(concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "),e.date_of_joining)/365.25),2))-1))-el.used_leaves<10,10,((DATEDIFF(now(),concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "))/365.25)*(20+(ROUND((DATEDIFF(concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "),e.date_of_joining)/365.25),2))-1))-el.used_leaves)),IF((DATEDIFF(now(),e.date_of_joining)/30)>=12&&MONTH(now())>3,0,IF((ROUND((DATEDIFF(concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "),e.date_of_joining)/365.25)*(20+(ROUND((DATEDIFF(concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "),e.date_of_joining)/365.25),2))-1),2)-el.used_leaves)>0,(ROUND((DATEDIFF(concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "),e.date_of_joining)/365.25)*(20+(ROUND((DATEDIFF(concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "),e.date_of_joining)/365.25),2))-1),2)-el.used_leaves),0))),2),2) as totalavailableleave','remainingleaves'=>new Zend_Db_Expr('ROUND(ROUND(IF(e.date_of_joining <= concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "),DATEDIFF(now(),concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "))/365.25, DATEDIFF(now(),e.date_of_joining)/365.25)*(20+(ROUND((DATEDIFF(now(),e.date_of_joining)/365.25),2))-1),2) + IF((DATEDIFF(concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "),e.date_of_joining)/30)>=12,IF(MONTH(now())>3,0,IF(((DATEDIFF(now(),concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "))/365.25)*(20+(ROUND((DATEDIFF(concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "),e.date_of_joining)/365.25),2))-1))-el.used_leaves<10,10,((DATEDIFF(now(),concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "))/365.25)*(20+(ROUND((DATEDIFF(concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "),e.date_of_joining)/365.25),2))-1))-el.used_leaves)),IF((DATEDIFF(now(),e.date_of_joining)/30)>=12&&MONTH(now())>3,0,IF((ROUND((DATEDIFF(concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "),e.date_of_joining)/365.25)*(20+(ROUND((DATEDIFF(concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "),e.date_of_joining)/365.25),2))-1),2)-el.used_leaves)>0,(ROUND((DATEDIFF(concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "),e.date_of_joining)/365.25)*(20+(ROUND((DATEDIFF(concat(year(now() - INTERVAL 1 YEAR), "-12-31 03:30:48 "),e.date_of_joining)/365.25),2))-1),2)-el.used_leaves),0))) - COALESCE(SUM(IF(lr.leavestatus="Approved" and lr.leavetypeid=15 and lr.from_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE) and lr.to_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE),lr.appliedleavescount,0)),0),2)'),'COALESCE(SUM(IF(lr.leavestatus="Approved" and lr.leavetypeid=15 and lr.from_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE) and lr.to_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE),lr.appliedleavescount,0)),0) as annual_paid_leave','COALESCE(SUM(IF(lr.leavestatus="Approved" and lr.leavetypeid=16 and lr.from_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE) and lr.to_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE),lr.appliedleavescount,0)),0) as unpaid_annual_leave','COALESCE(SUM(IF(lr.leavestatus="Approved" and lr.leavetypeid=1 and lr.from_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE) and lr.to_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE),lr.appliedleavescount,0)),0) as sick_leave','COALESCE(SUM(IF(lr.leavestatus="Approved" and lr.leavetypeid=2 and lr.from_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE) and lr.to_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE),lr.appliedleavescount,0)),0) as maternity_leave','COALESCE(SUM(IF(lr.leavestatus="Approved" and lr.leavetypeid=19 and lr.from_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE) and lr.to_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE),lr.appliedleavescount,0)),0) as compensatory_leave','COALESCE(SUM(IF(lr.leavestatus="Approved" and lr.leavetypeid=30 and lr.from_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE) and lr.to_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE),lr.appliedleavescount,0)),0) as accident_leave','COALESCE(SUM(IF(lr.leavestatus="Approved" and lr.leavetypeid=20 and lr.from_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE) and lr.to_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE),lr.appliedleavescount,0)),0) as miscarriage_leave','COALESCE(SUM(IF(lr.leavestatus="Approved" and lr.leavetypeid=21 and lr.from_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE) and lr.to_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE),lr.appliedleavescount,0)),0) as miscarriagepaternity_leave','COALESCE(SUM(IF(lr.leavestatus="Approved" and lr.leavetypeid=13 and lr.from_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE) and lr.to_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE),lr.appliedleavescount,0)),0) as paternity_leave','COALESCE(SUM(IF(lr.leavestatus="Approved" and lr.leavetypeid=22 and lr.from_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE) and lr.to_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE),lr.appliedleavescount,0)),0) as childnursing_leave','COALESCE(SUM(IF(lr.leavestatus="Approved" and lr.leavetypeid=23 and lr.from_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE) and lr.to_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE),lr.appliedleavescount,0)),0) as pernatalrelated_leave','COALESCE(SUM(IF(lr.leavestatus="Approved" and lr.leavetypeid=24 and lr.from_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE) and lr.to_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE),lr.appliedleavescount,0)),0) as compassionateNIR_leave','COALESCE(SUM(IF(lr.leavestatus="Approved" and lr.leavetypeid=25 and lr.from_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE) and lr.to_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE),lr.appliedleavescount,0)),0) as CompassionateIR_leave','COALESCE(SUM(IF(lr.leavestatus="Approved" and lr.leavetypeid=27 and lr.from_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE) and lr.to_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE),lr.appliedleavescount,0)),0) as variableleavefamily','COALESCE(SUM(IF(lr.leavestatus="Approved" and lr.leavetypeid=14 and lr.from_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE) and lr.to_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE),lr.appliedleavescount,0)),0) as examinationandstudy_leave','COALESCE(SUM(IF(lr.leavestatus="Approved" and lr.leavetypeid=18 and lr.from_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE) and lr.to_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE),lr.appliedleavescount,0)),0) as court_leave','COALESCE(SUM(IF(lr.leavestatus="Approved" and lr.leavetypeid=29 and lr.from_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE) and lr.to_date BETWEEN CAST(CONCAT(YEAR(now()),"-01-01") AS DATE) AND CAST(CONCAT(YEAR(now()),"-12-31") AS DATE),lr.appliedleavescount,0)),0) as emergencyunpaid_leave'))                                        
                                ->joinLeft(array('lr'=>'main_leaverequest'), 'lr.user_id=e.user_id')
                                ->where($where)
                                // ->where('lr.leavestatus="Approved"')
                                ->group(array ("e.user_id"))
                                ->order("$by $sort") 
                                ->limitPage($pageNo, $perPage);
                                // die($employeesData); 
        return $employeesData;       		
    }
	
    public function getGrid($sort,$by,$perPage,$pageNo,$searchData,$call,$dashboardcall,$exParam1='',$exParam2='',$exParam3='',$exParam4='')
    {		
        $searchQuery = '';
        $tablecontent = '';
        $emptyroles=0;
        $empstatus_opt = array();
        $searchArray = array();
        $data = array();
        $id='';
        $dataTmp = array();
		
        if($searchData != '' && $searchData!='undefined')
        {
            $searchValues = json_decode($searchData);
			
            foreach($searchValues as $key => $val)
            {				
                $searchQuery .= $key." like '%".$val."%' AND ";				
                $searchArray[$key] = $val;
            }
            $searchQuery = rtrim($searchQuery," AND");					
        }
        $objName = 'addemployeeleaves';
				        
			
        $tableFields = array('action'=>'Action','firstname'=>'First Name','lastname'=>'Last Name',
                             'employeeId' =>'Employee ID','emp_leave_limit'=>'leave entitlement per year','serviceyear'=>'Service Year','leaveX'=>'Accrued Leave as of today','transfer'=>'BBF (Balance Brought Forward)','totalavailableleave'=>'Balance as of today',
                             'used_leaves'=>'Used Leaves','remainingleaves'=>'Leave Balance','alloted_year'=>'Date of joining','annual_paid_leave'=>'Annual paid leave / Vaccation','unpaid_annual_leave'=>'Unpaid Annual leave','sick_leave'=>'Sick leave','maternity_leave'=>'Maternity leave','compensatory_leave'=>'Compensatory leave','accident_leave'=>'Accident leave','miscarriage_leave'=>'Miscarriage leave','miscarriagepaternity_leave'=>'Miscarriage Paternity leave','paternity_leave'=>'Paternity leave','childnursing_leave'=>'Child Nursing leave','pernatalrelated_leave'=>'Per Natal related leave','compassionateNIR_leave'=>'Compassionate leave NIR','CompassionateIR_leave'=>'Compassionate leave IR','variableleavefamily'=>'Variable Leave Family','examinationandstudy_leave'=>'Examination and Study Leave','court_leave'=>'Court Leave','emergencyunpaid_leave'=>'Emergency Unpaid Leave');
		   
        $tablecontent = $this->getEmployeesData($sort,$by,$pageNo,$perPage,$searchQuery,'',$exParam1);  
			
        if($tablecontent == "emptyroles")
        {
            $emptyroles=1;
        }
		
        $dataTmp = array(
                        'userid'=>$id,
                        'sort' => $sort,
                        'by' => $by,
                        'pageNo' => $pageNo,
                        'perPage' => $perPage,				
                        'tablecontent' => $tablecontent,
                        'objectname' => $objName,
                        'extra' => array(),
                        'tableheader' => $tableFields,
                        'jsGridFnName' => 'getAjaxgridData',                        
                        'jsFillFnName' => '',
                        'searchArray' => $searchArray,
                        'menuName' => 'Employees',
                        'dashboardcall'=>$dashboardcall,
                        'add'=>'add',
                        'call'=>$call,
                        'emptyroles'=>$emptyroles
                    );	
				
        return $dataTmp;
    }
    
	public function getMultipleEmployees($dept_id)
	{
		$auth = Zend_Auth::getInstance();
		if($auth->hasIdentity()){
			$loginUserId = $auth->getStorage()->read()->id;
		}
            if($dept_id != '' && $loginUserId!='')
            {
                $select = $this->select()
                            ->setIntegrityCheck(false)
                            ->from(array('e' => 'main_employees_summary'),array('e.id','e.user_id','e.userfullname','e.firstname','e.lastname','e.employeeId','e.department_id'))
                            ->joinLeft(array('r'=>'main_roles'), 'e.emprole=r.id')
                            ->joinLeft(array('el'=>'main_employeeleaves'), 'el.user_id=e.user_id',array('el.emp_leave_limit','el.used_leaves','el.alloted_year','el.createddate','el.isleavetrasnferset'))
                            ->where('e.isactive = 1 and e.department_id in ('.$dept_id.') and e.user_id!='.$loginUserId.' and r.group_id NOT IN ('.MANAGEMENT_GROUP.','.USERS_GROUP.')')
							->group('e.user_id')
                            ->order('e.userfullname');

                return $this->fetchAll($select)->toArray();	
            }
            else 
                return array();
	}
	
}
?>
