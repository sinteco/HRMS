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
/**
 *
 * @model Reports Model
 * @author sagarsoft
 *
 */
class Timemanagement_Model_Reports extends Zend_Db_Table_Abstract
{
	/**
	 * The default table name
	 */
	protected $_name = 'tm_projects';
	/**
	 * This method is used to fetch the project details based on the user Role.
	 * 
	 * Added by Manju for reports.
	 */
	public function getProjectsListByRole(){
		$storage = new Zend_Auth_Storage_Session();
		$sessionData = $storage->read();
		$result = array();
		$tm_role = Zend_Registry::get('tm_role');
		if($tm_role == "Admin") {
			$select = $this->select()
						   ->setIntegrityCheck(false)
						   ->from(array('p'=>$this->_name),array('p.id','project_name'))
						   ->where('p.is_active = 1 ')
						   ->order('p.project_name asc');
			$result = $this->fetchAll($select)->toArray();
		}else{
			$select = $this->select()
							->setIntegrityCheck(false)
							->from(array('p' => $this->_name),array('id'=>'p.id','project_name' => 'p.project_name',))
							->joinLeft(array('tpe'=>'tm_project_employees'), 'tpe.project_id=p.id AND tpe.is_active=1',array())
							->where('p.is_active=1 AND tpe.emp_id ='.$sessionData['id'])
							->order("p.project_name asc")
							->group('p.id');
			$result = $this->fetchAll($select)->toArray();
		}
		return $result;
		
	}
	
	public function getEmpList()
	{
		$select = $this->select()
					->setIntegrityCheck(false)
					->from(array('e'=>'main_employees_summary'), array('id'=>'e.user_id','text'=>'e.userfullname','pic'=>'e.profileimg'))
					->where("e.isactive = 1 ")
					->order("e.userfullname ASC")
					->distinct('e.id');

		return $this->fetchAll($select)->toArray();
	}
        
	public function getEmployeeReportsbyProjectId($sort, $by, $perPage, $pageNo, $searchData, $call, $dashboardcall, 
			$start_date, $end_date, $projid,$org_start_date,$org_end_date,$param=''){
		
		$searchQuery = '';
		$searchArray = array();
		$data = array();

		if($searchData != '' && $searchData!='undefined')
		{
			$searchValues = json_decode($searchData);
			foreach($searchValues as $key => $val)
			{
				$searchQuery .= " ".$key." like '%".$val."%' AND ";
				$searchArray[$key] = $val;
			}
			$searchQuery = rtrim($searchQuery," AND");
		}
			
		$objName = 'reports';

		//email,phone_no,poc,address,country_id,state_id,created_by
		$tableFields = array(
					//'action'=>'Action',
					'userfullname' => 'Employee',
					// 'project_type' => 'Project Type',
					// 'duration' => 'Hours',
					'task' => 'Task',
					'project_name' => 'Project Name'
		);
//===========================================================================================
//===========================================================================================
//==========Melese's code to call task name (project code to the report)============================

$select = $this->select()
            ->setIntegrityCheck(false)
            ->from(array('e'=>'tm_tasks'), array('id','task'=>'e.task'));
            //->where("e.isactive = 1 ")
           // ->order("e.userfullname ASC")
           // ->distinct('e.id');

        $tasks = $this->fetchAll($select)->toArray();
        //var_dump($tasks);
        $i=1;
       foreach($tasks as $task)
       {
		//   var_dump($task);
		// var_dump($tableFields);
        //    $tableFields['task_id_'.$i]=$task['task'];
           $i++;
       }
      // var_dump($tableFields);
	  $tableFields['duration'] = 'Hours';

//===========================================================================================
//===========================================================================================
//===========================================================================================

		$tablecontent = $this->getEmployeeReportsData($sort, $by, $pageNo, $perPage, $searchQuery, $start_date, $end_date, $projid, $param);
		
		$dataTmp = array(
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
				'call'=>$call,
				'dashboardcall'=>$dashboardcall,
				'menuName' => 'Employee Reports',
				'otheraction' => 'employeereports',
				'projectId' => $projid,
				'start_date' => $org_start_date,
				'end_date' => $org_end_date,
			);
			return $dataTmp;
 	 }
	

	/**
	 * This will fetch all the active client details.
	 *
	 * @param string $sort
	 * @param string $by
	 * @param number $pageNo
	 * @param number $perPage
	 * @param string $searchQuery
	 *
	 * @return array $EmployeeReportsData
	 */
	public function getEmployeeReportsData($sort, $by, $pageNo, $perPage, $searchQuery,$start_date, $end_date, $projid, $param="",$flag="")
	{
		$andwhere = ' AND (1=1)';
		if($start_date != "")
		{
			if($end_date == "")
			{
				//$end_date = date('%Y-%m-%d %H:%i:%s');
				$end_date = date('%Y-%m-%d');
			}
			$start_dates=strtotime($start_date);
			$sd_month=date("m",$start_dates);
			$sd_year=date("Y",$start_dates);
			
			$end_dates=strtotime($end_date);
			$ed_month=date("m",$end_dates);
			$ed_year=date("Y",$end_dates);
			
			$andwhere = " AND et.ts_year >= ".$sd_year." AND et.ts_year <=".$ed_year." AND et.ts_month >= ".$sd_month." AND et.ts_month <= ".$ed_month;
			$duration = "";
			$duration_sort = "";
			if($param=="" || $param=="undefined" || $param=="Last 7 days")
			{
				$duration = "CONCAT(FLOOR(SUM( TIME_TO_SEC( IF(sun_date BETWEEN '".$start_date."' AND '".$end_date."',sun_duration,'00:00')) +
				TIME_TO_SEC( IF(mon_date BETWEEN '".$start_date."' AND '".$end_date."',mon_duration,'00:00')) +
				TIME_TO_SEC( IF(tue_date BETWEEN '".$start_date."' AND '".$end_date."',tue_duration,'00:00')) +
				TIME_TO_SEC( IF(wed_date BETWEEN '".$start_date."' AND '".$end_date."',wed_duration,'00:00')) +
				TIME_TO_SEC( IF(thu_date BETWEEN '".$start_date."' AND '".$end_date."',thu_duration,'00:00')) +
				TIME_TO_SEC( IF(fri_date BETWEEN '".$start_date."' AND '".$end_date."',fri_duration,'00:00')) +
				TIME_TO_SEC( IF(sat_date BETWEEN '".$start_date."' AND '".$end_date."',sat_duration,'00:00')))/3600),':',
				LPAD(FLOOR(SUM( TIME_TO_SEC( IF(sun_date BETWEEN '".$start_date."' AND '".$end_date."',sun_duration,'00:00')) +
				TIME_TO_SEC( IF(mon_date BETWEEN '".$start_date."' AND '".$end_date."',mon_duration,'00:00')) +
				TIME_TO_SEC( IF(tue_date BETWEEN '".$start_date."' AND '".$end_date."',tue_duration,'00:00')) +
				TIME_TO_SEC( IF(wed_date BETWEEN '".$start_date."' AND '".$end_date."',wed_duration,'00:00')) +
				TIME_TO_SEC( IF(thu_date BETWEEN '".$start_date."' AND '".$end_date."',thu_duration,'00:00')) +
				TIME_TO_SEC( IF(fri_date BETWEEN '".$start_date."' AND '".$end_date."',fri_duration,'00:00')) +
				TIME_TO_SEC( IF(sat_date BETWEEN '".$start_date."' AND '".$end_date."',sat_duration,'00:00')))/60)%60,2,'0'))";
				
				$duration_sort = "TIME_TO_SEC( IF(mon_date BETWEEN '".$start_date."' AND '".$end_date."',mon_duration,'00:00')) +
				TIME_TO_SEC( IF(tue_date BETWEEN '".$start_date."' AND '".$end_date."',tue_duration,'00:00')) +
				TIME_TO_SEC( IF(wed_date BETWEEN '".$start_date."' AND '".$end_date."',wed_duration,'00:00')) +
				TIME_TO_SEC( IF(thu_date BETWEEN '".$start_date."' AND '".$end_date."',thu_duration,'00:00')) +
				TIME_TO_SEC( IF(fri_date BETWEEN '".$start_date."' AND '".$end_date."',fri_duration,'00:00')) +
				TIME_TO_SEC( IF(sat_date BETWEEN '".$start_date."' AND '".$end_date."',sat_duration,'00:00'))";
				
				$andwhere =" AND (sun_date BETWEEN '".$start_date."' AND '".$end_date."' OR mon_date BETWEEN '".$start_date."' AND '".$end_date."'
				OR tue_date BETWEEN '".$start_date."' AND '".$end_date."' OR wed_date BETWEEN '".$start_date."' AND '".$end_date."'
				OR thu_date BETWEEN '".$start_date."' AND '".$end_date."' OR fri_date BETWEEN '".$start_date."' AND '".$end_date."'
				OR sat_date BETWEEN '".$start_date."' AND '".$end_date."')";
			}
			else if($param=='Today')
			{
				$duration = "CONCAT(FLOOR(SUM( TIME_TO_SEC( IF(sun_date = '".$start_date."',sun_duration,'00:00')) +
				TIME_TO_SEC( IF(mon_date = '".$start_date."' ,mon_duration,'00:00')) +
				TIME_TO_SEC( IF(tue_date = '".$start_date."' ,tue_duration,'00:00')) +
				TIME_TO_SEC( IF(wed_date = '".$start_date."' ,wed_duration,'00:00')) +
				TIME_TO_SEC( IF(thu_date = '".$start_date."' ,thu_duration,'00:00')) +
				TIME_TO_SEC( IF(fri_date = '".$start_date."' ,fri_duration,'00:00')) +
				TIME_TO_SEC( IF(sat_date = '".$start_date."' ,sat_duration,'00:00')))/3600),':',
				LPAD(FLOOR(SUM( TIME_TO_SEC( IF(sun_date = '".$start_date."' ,sun_duration,'00:00')) +
				TIME_TO_SEC( IF(mon_date = '".$start_date."' ,mon_duration,'00:00')) +
				TIME_TO_SEC( IF(tue_date = '".$start_date."' ,tue_duration,'00:00')) +
				TIME_TO_SEC( IF(wed_date = '".$start_date."' ,wed_duration,'00:00')) +
				TIME_TO_SEC( IF(thu_date = '".$start_date."' ,thu_duration,'00:00')) +
				TIME_TO_SEC( IF(fri_date = '".$start_date."' ,fri_duration,'00:00')) +
				TIME_TO_SEC( IF(sat_date = '".$start_date."' ,sat_duration,'00:00')))/60)%60,2,'0'))";
				
				$duration_sort = "TIME_TO_SEC( IF(mon_date = '".$start_date."' ,mon_duration,'00:00')) +
				TIME_TO_SEC( IF(tue_date = '".$start_date."' ,tue_duration,'00:00')) +
				TIME_TO_SEC( IF(wed_date = '".$start_date."' ,wed_duration,'00:00')) +
				TIME_TO_SEC( IF(thu_date = '".$start_date."' ,thu_duration,'00:00')) +
				TIME_TO_SEC( IF(fri_date = '".$start_date."' ,fri_duration,'00:00')) +
				TIME_TO_SEC( IF(sat_date = '".$start_date."' ,sat_duration,'00:00'))";
				
				$andwhere = " AND (sun_date = '".$start_date."' OR mon_date = '".$start_date."' OR tue_date = '".$start_date."' OR wed_date = '".$start_date."' OR thu_date = '".$start_date."' OR fri_date = '".$start_date."' OR sat_date = '".$start_date."')";
			}
			else
			{
				$duration = "concat(floor(SUM( TIME_TO_SEC( et.week_duration ))/3600),':',lpad(floor(SUM( TIME_TO_SEC( et.week_duration ))/60)%60,2,'0'))";
				$andwhere = " AND et.ts_year >= ".$sd_year." AND et.ts_year <=".$ed_year." AND et.ts_month >= ".$sd_month." AND et.ts_month <= ".$ed_month;
				$duration_sort = "SUM(TIME_TO_SEC(et.week_duration))";
			}
			
			// if($param!="" && $param!="undefined" && $param!="Today" && $param!="Last 7 days")
			// {
				// $andwhere = " AND et.ts_year = ".$sd_year." AND et.ts_month >= ".$sd_month." AND et.ts_month <= ".$sd_month;
			// }
			//	$andwhere = " AND et.created BETWEEN STR_TO_DATE('".$start_date."','%Y-%m-%d %H:%i:%s') AND STR_TO_DATE('".$end_date."','%Y-%m-%d %H:%i:%s')";
		}
		
		if($searchQuery){
			$andwhere .= " AND ".$searchQuery;	
		}
		
		if($projid != ''){
			$andwhere .= " AND p.id = '".$projid."'";
		}
		
		$db = Zend_Db_Table::getDefaultAdapter();
		$select = $this->select()
			   		   ->setIntegrityCheck(false)
					   ->from(array('et' => 'tm_emp_timesheets'),array('t.task','p.project_name','e.userfullname','p.project_type','userId'=>'et.emp_id',
				                                'duration'=>$duration,'duration_sort'=>$duration_sort))  
					   ->joinInner(array('pt'=>'tm_project_tasks'), 'pt.id = et.project_task_id',array())
					   ->joinInner(array('t'=>'tm_tasks'), 't.id = pt.task_id',array())
					   ->joinInner(array('p'=>'tm_projects'), 'p.id = pt.project_id',array())
					   ->joinInner(array('e'=>'main_employees_summary'), 'e.user_id = et.emp_id',array())
					   ->joinLeft(array('pm'=>'tm_project_employees'), 'p.id = pm.project_id and pm.emp_id = et.emp_id ',array())
					   ->where('et.is_active=1 and pt.is_active =1 and p.is_active = 1 and e.isactive = 1'.$andwhere)
					   ->order("$by $sort")
					   ->group('t.id')->group('e.id')->group('p.id')->order('e.firstname')
					   ->limitPage($pageNo, $perPage);
					   //echo $select;

		// $select = $this->select()
		// ->setIntegrityCheck(false)
		// ->from(array('et' => 'tm_emp_timesheets'),array('p.project_name','t.task',
        //                         'duration'=>$duration))  
		// ->joinInner(array('pt'=>'tm_project_tasks'), 'pt.id = et.project_task_id',array())
		// ->joinInner(array('t'=>'tm_tasks'), 't.id = pt.task_id',array())
		// ->joinInner(array('p'=>'tm_projects'), 'p.id = pt.project_id and p.id = et.project_id',array())
		// ->joinInner(array('e'=>'main_employees_summary'), 'e.user_id = et.emp_id',array())
		// ->joinLeft(array('pm'=>'tm_project_employees'), 'p.id = pm.project_id and pm.emp_id = et.emp_id ',array())
		// ->where('et.is_active=1 and p.id='.$project_id.' '.$andwhere)
		// ->group('t.id');
		
		if(!empty($flag))
		{
			return $this->fetchAll($select)->toArray(); 
		}
		return $select;
	}
	public function getLeaveReportsData($sort, $by, $pageNo, $perPage, $searchQuery,$start_date, $end_date, $projid, $param="",$flag="")
	{
		//the below code is used to get data of employees from summary table.
        $employeesData="";                             
        $where = "  e.isactive = 1";  
       
        if($searchQuery != '')
			$where .= " AND ".$searchQuery;
			
		$db = Zend_Db_Table::getDefaultAdapter();
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
                                ->order("$by $sort"); 
                                // ->limitPage($pageNo, $perPage);
								// die($employeesData);
			if(!empty($flag))
			{
				return $this->fetchAll($employeesData)->toArray(); 
			} 
        return $employeesData;
	}
	
	function getProjectReportsbyEmployeeId($sort, $by, $perPage, $pageNo, $searchData, $call, $dashboardcall,
			 $start_date, $end_date, $empid,$org_start_date,$org_end_date,$param=""){

		$searchQuery = '';
		$searchArray = array();
		$data = array();

		if($searchData != '' && $searchData!='undefined')
		{
			$searchValues = json_decode($searchData);
			foreach($searchValues as $key => $val)
			{
				$searchQuery .= " ".$key." like '%".$val."%' AND ";
				$searchArray[$key] = $val;
			}
			$searchQuery = rtrim($searchQuery," AND");
		}

		$objName = 'reports';

		//email,phone_no,poc,address,country_id,state_id,created_by
		$tableFields = array(
		//'action'=>'Action',
					'project_name' => 'Project',
					'project_type' => 'Project Type',
					'duration' => 'Hours',
		);

		$tablecontent = $this->getProjectReportsData($sort, $by, $pageNo, $perPage, $searchQuery, $start_date, $end_date, $empid, $param);

		$dataTmp = array(
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
				'call'=>$call,
				'dashboardcall'=>$dashboardcall,
				'menuName' => 'Project Reports',
				'otheraction' => 'projectsreports',
				'emp_id' => $empid,
				'start_date' => $org_start_date,
				'end_date' => $org_end_date,
		);
		return $dataTmp;
		
	}
	
	function getProjectReportsData($sort, $by, $pageNo, $perPage, $searchQuery, $start_date, $end_date, $empid, $param="",$flag=""){
		
		$andwhere = " AND (1=1)";
		if($start_date != "")
		{
			if($end_date == "")
			{
				//$end_date = date('%Y-%m-%d %H:%i:%s');
				$end_date = date('%Y-%m-%d');
			}
			$start_dates=strtotime($start_date);
			$sd_month=date("m",$start_dates);
			$sd_year=date("Y",$start_dates);
			
			$end_dates=strtotime($end_date);
			$ed_month=date("m",$end_dates);
			$ed_year=date("Y",$end_dates);
			$andwhere = " AND et.ts_year >= ".$sd_year." AND et.ts_year <=".$ed_year." AND et.ts_month >= ".$sd_month." AND et.ts_month <= ".$ed_month;
			
			$duration="";
			$duration_sort ="";
			//$andwhere = " AND et.created BETWEEN STR_TO_DATE('".$start_date."','%Y-%m-%d %H:%i:%s') AND STR_TO_DATE('".$end_date."','%Y-%m-%d %H:%i:%s')";
			if($param=="" || $param=="undefined" || $param=="Last 7 days")
			{
				$duration = "CONCAT(FLOOR(SUM( TIME_TO_SEC( IF(sun_date BETWEEN '".$start_date."' AND '".$end_date."',sun_duration,'00:00')) +
				TIME_TO_SEC( IF(mon_date BETWEEN '".$start_date."' AND '".$end_date."',mon_duration,'00:00')) +
				TIME_TO_SEC( IF(tue_date BETWEEN '".$start_date."' AND '".$end_date."',tue_duration,'00:00')) +
				TIME_TO_SEC( IF(wed_date BETWEEN '".$start_date."' AND '".$end_date."',wed_duration,'00:00')) +
				TIME_TO_SEC( IF(thu_date BETWEEN '".$start_date."' AND '".$end_date."',thu_duration,'00:00')) +
				TIME_TO_SEC( IF(fri_date BETWEEN '".$start_date."' AND '".$end_date."',fri_duration,'00:00')) +
				TIME_TO_SEC( IF(sat_date BETWEEN '".$start_date."' AND '".$end_date."',sat_duration,'00:00')))/3600),':',
				LPAD(FLOOR(SUM( TIME_TO_SEC( IF(sun_date BETWEEN '".$start_date."' AND '".$end_date."',sun_duration,'00:00')) +
				TIME_TO_SEC( IF(mon_date BETWEEN '".$start_date."' AND '".$end_date."',mon_duration,'00:00')) +
				TIME_TO_SEC( IF(tue_date BETWEEN '".$start_date."' AND '".$end_date."',tue_duration,'00:00')) +
				TIME_TO_SEC( IF(wed_date BETWEEN '".$start_date."' AND '".$end_date."',wed_duration,'00:00')) +
				TIME_TO_SEC( IF(thu_date BETWEEN '".$start_date."' AND '".$end_date."',thu_duration,'00:00')) +
				TIME_TO_SEC( IF(fri_date BETWEEN '".$start_date."' AND '".$end_date."',fri_duration,'00:00')) +
				TIME_TO_SEC( IF(sat_date BETWEEN '".$start_date."' AND '".$end_date."',sat_duration,'00:00')))/60)%60,2,'0'))";
				
				$duration_sort = "TIME_TO_SEC( IF(mon_date BETWEEN '".$start_date."' AND '".$end_date."',mon_duration,'00:00')) +
				TIME_TO_SEC( IF(tue_date BETWEEN '".$start_date."' AND '".$end_date."',tue_duration,'00:00')) +
				TIME_TO_SEC( IF(wed_date BETWEEN '".$start_date."' AND '".$end_date."',wed_duration,'00:00')) +
				TIME_TO_SEC( IF(thu_date BETWEEN '".$start_date."' AND '".$end_date."',thu_duration,'00:00')) +
				TIME_TO_SEC( IF(fri_date BETWEEN '".$start_date."' AND '".$end_date."',fri_duration,'00:00')) +
				TIME_TO_SEC( IF(sat_date BETWEEN '".$start_date."' AND '".$end_date."',sat_duration,'00:00'))";
				
				$andwhere =" AND (sun_date BETWEEN '".$start_date."' AND '".$end_date."' OR mon_date BETWEEN '".$start_date."' AND '".$end_date."'
				OR tue_date BETWEEN '".$start_date."' AND '".$end_date."' OR wed_date BETWEEN '".$start_date."' AND '".$end_date."'
				OR thu_date BETWEEN '".$start_date."' AND '".$end_date."' OR fri_date BETWEEN '".$start_date."' AND '".$end_date."'
				OR sat_date BETWEEN '".$start_date."' AND '".$end_date."')";
			}
			else if($param=='Today')
			{
				$duration = "CONCAT(FLOOR(SUM( TIME_TO_SEC( IF(sun_date = '".$start_date."',sun_duration,'00:00')) +
				TIME_TO_SEC( IF(mon_date = '".$start_date."' ,mon_duration,'00:00')) +
				TIME_TO_SEC( IF(tue_date = '".$start_date."' ,tue_duration,'00:00')) +
				TIME_TO_SEC( IF(wed_date = '".$start_date."' ,wed_duration,'00:00')) +
				TIME_TO_SEC( IF(thu_date = '".$start_date."' ,thu_duration,'00:00')) +
				TIME_TO_SEC( IF(fri_date = '".$start_date."' ,fri_duration,'00:00')) +
				TIME_TO_SEC( IF(sat_date = '".$start_date."' ,sat_duration,'00:00')))/3600),':',
				LPAD(FLOOR(SUM( TIME_TO_SEC( IF(sun_date = '".$start_date."' ,sun_duration,'00:00')) +
				TIME_TO_SEC( IF(mon_date = '".$start_date."' ,mon_duration,'00:00')) +
				TIME_TO_SEC( IF(tue_date = '".$start_date."' ,tue_duration,'00:00')) +
				TIME_TO_SEC( IF(wed_date = '".$start_date."' ,wed_duration,'00:00')) +
				TIME_TO_SEC( IF(thu_date = '".$start_date."' ,thu_duration,'00:00')) +
				TIME_TO_SEC( IF(fri_date = '".$start_date."' ,fri_duration,'00:00')) +
				TIME_TO_SEC( IF(sat_date = '".$start_date."' ,sat_duration,'00:00')))/60)%60,2,'0'))";
				
				$duration_sort = "TIME_TO_SEC( IF(mon_date = '".$start_date."' ,mon_duration,'00:00')) +
				TIME_TO_SEC( IF(tue_date = '".$start_date."' ,tue_duration,'00:00')) +
				TIME_TO_SEC( IF(wed_date = '".$start_date."' ,wed_duration,'00:00')) +
				TIME_TO_SEC( IF(thu_date = '".$start_date."' ,thu_duration,'00:00')) +
				TIME_TO_SEC( IF(fri_date = '".$start_date."' ,fri_duration,'00:00')) +
				TIME_TO_SEC( IF(sat_date = '".$start_date."' ,sat_duration,'00:00'))";
				
				$andwhere = " AND (sun_date = '".$start_date."' OR mon_date = '".$start_date."' OR tue_date = '".$start_date."' OR wed_date = '".$start_date."' OR thu_date = '".$start_date."' OR fri_date = '".$start_date."' OR sat_date = '".$start_date."')";
			}
			else
			{
				$duration = "concat(floor(SUM( TIME_TO_SEC( et.week_duration ))/3600),':',lpad(floor(SUM( TIME_TO_SEC( et.week_duration ))/60)%60,2,'0'))";
				$andwhere = " AND et.ts_year >= ".$sd_year." AND et.ts_year <=".$ed_year." AND et.ts_month >= ".$sd_month." AND et.ts_month <= ".$ed_month;
				$duration_sort = "SUM(TIME_TO_SEC(et.week_duration))";
			}
		}
		
		if($searchQuery){
			$andwhere .= " AND ".$searchQuery;	
		}

		if($empid != "")
		{
			$andwhere .= ' AND et.emp_id = '.$empid;
		}
		//'duration'=>'concat(floor(SUM( TIME_TO_SEC( et.week_duration ))/3600),":",lpad(floor(SUM( TIME_TO_SEC( et.week_duration ))/60)%60,2,"0"))'
		//'SUM(TIME_TO_SEC(et.week_duration))'
		$select = $this->select()
		->setIntegrityCheck(false)
		->from(array('et' => 'tm_emp_timesheets'),array('p.project_name','proj_category'=>'p.project_type','p.id','project_type'=>'IF(p.project_type="billable","Billable",IF(p.project_type="non_billable","Non billable","Revenue generation"))',
                                'duration'=>$duration,'duration_sort'=>$duration_sort))  
		->joinInner(array('pt'=>'tm_project_tasks'), 'pt.id = et.project_task_id',array())
		->joinInner(array('p'=>'tm_projects'), 'p.id = pt.project_id and p.id = et.project_id',array())
		->joinInner(array('e'=>'main_employees_summary'), 'e.user_id = et.emp_id',array())
		->joinLeft(array('pm'=>'tm_project_employees'), 'p.id = pm.project_id and pm.emp_id = et.emp_id ',array())
		//->joinLeft(array('pm'=>new Zend_Db_Expr('(SELECT project_id,GROUP_CONCAT(emp_id) as manager_ids FROM tm_project_employees 
		//WHERE is_active=1 and emp_type = \'manager\' GROUP BY project_id)')), 'pm.project_id = pt.project_id',array())
		->where('et.is_active=1 '.$andwhere)
		->order("$by $sort")
		->group('p.id')
		->limitPage($pageNo, $perPage);
		if(!empty($flag))
		{
			return $this->fetchAll($select)->toArray(); 
		}
		//echo $select;//exit;
		return $select;
	}
	public function getEmpProjDuration($empId,$start_date,$end_date,$project_id,$param)
	{
		$andwhere = '';
		if($start_date != "")
		{
			if($end_date == "")
			{
				//$end_date = date('%Y-%m-%d %H:%i:%s');
				$end_date = date('%Y-%m-%d');
			}
			$start_dates=strtotime($start_date);
			$sd_month=date("m",$start_dates);
			$sd_year=date("Y",$start_dates);
			
			$end_dates=strtotime($end_date);
			$ed_month=date("m",$end_dates);
			$ed_year=date("Y",$end_dates);
			
			$duration="";
			$andwhere = " AND et.ts_year >= ".$sd_year." AND et.ts_year <=".$ed_year." AND et.ts_month >= ".$sd_month." AND et.ts_month <= ".$ed_month;
			//$andwhere = " AND et.created BETWEEN STR_TO_DATE('".$start_date."','%Y-%m-%d %H:%i:%s') AND STR_TO_DATE('".$end_date."','%Y-%m-%d %H:%i:%s')";
			
			if($param=="" || $param=="undefined" || $param=="Last 7 days")
			{
				$duration = "CONCAT(FLOOR(SUM( TIME_TO_SEC( IF(sun_date BETWEEN '".$start_date."' AND '".$end_date."',sun_duration,'00:00')) +
				TIME_TO_SEC( IF(mon_date BETWEEN '".$start_date."' AND '".$end_date."',mon_duration,'00:00')) +
				TIME_TO_SEC( IF(tue_date BETWEEN '".$start_date."' AND '".$end_date."',tue_duration,'00:00')) +
				TIME_TO_SEC( IF(wed_date BETWEEN '".$start_date."' AND '".$end_date."',wed_duration,'00:00')) +
				TIME_TO_SEC( IF(thu_date BETWEEN '".$start_date."' AND '".$end_date."',thu_duration,'00:00')) +
				TIME_TO_SEC( IF(fri_date BETWEEN '".$start_date."' AND '".$end_date."',fri_duration,'00:00')) +
				TIME_TO_SEC( IF(sat_date BETWEEN '".$start_date."' AND '".$end_date."',sat_duration,'00:00')))/3600),':',
				LPAD(FLOOR(SUM( TIME_TO_SEC( IF(sun_date BETWEEN '".$start_date."' AND '".$end_date."',sun_duration,'00:00')) +
				TIME_TO_SEC( IF(mon_date BETWEEN '".$start_date."' AND '".$end_date."',mon_duration,'00:00')) +
				TIME_TO_SEC( IF(tue_date BETWEEN '".$start_date."' AND '".$end_date."',tue_duration,'00:00')) +
				TIME_TO_SEC( IF(wed_date BETWEEN '".$start_date."' AND '".$end_date."',wed_duration,'00:00')) +
				TIME_TO_SEC( IF(thu_date BETWEEN '".$start_date."' AND '".$end_date."',thu_duration,'00:00')) +
				TIME_TO_SEC( IF(fri_date BETWEEN '".$start_date."' AND '".$end_date."',fri_duration,'00:00')) +
				TIME_TO_SEC( IF(sat_date BETWEEN '".$start_date."' AND '".$end_date."',sat_duration,'00:00')))/60)%60,2,'0'))";
				
				
				$andwhere =" AND (sun_date BETWEEN '".$start_date."' AND '".$end_date."' OR mon_date BETWEEN '".$start_date."' AND '".$end_date."'
				OR tue_date BETWEEN '".$start_date."' AND '".$end_date."' OR wed_date BETWEEN '".$start_date."' AND '".$end_date."'
				OR thu_date BETWEEN '".$start_date."' AND '".$end_date."' OR fri_date BETWEEN '".$start_date."' AND '".$end_date."'
				OR sat_date BETWEEN '".$start_date."' AND '".$end_date."')";
			}
			else if($param=='Today')
			{
				$duration = "CONCAT(FLOOR(SUM( TIME_TO_SEC( IF(sun_date = '".$start_date."',sun_duration,'00:00')) +
				TIME_TO_SEC( IF(mon_date = '".$start_date."' ,mon_duration,'00:00')) +
				TIME_TO_SEC( IF(tue_date = '".$start_date."' ,tue_duration,'00:00')) +
				TIME_TO_SEC( IF(wed_date = '".$start_date."' ,wed_duration,'00:00')) +
				TIME_TO_SEC( IF(thu_date = '".$start_date."' ,thu_duration,'00:00')) +
				TIME_TO_SEC( IF(fri_date = '".$start_date."' ,fri_duration,'00:00')) +
				TIME_TO_SEC( IF(sat_date = '".$start_date."' ,sat_duration,'00:00')))/3600),':',
				LPAD(FLOOR(SUM( TIME_TO_SEC( IF(sun_date = '".$start_date."' ,sun_duration,'00:00')) +
				TIME_TO_SEC( IF(mon_date = '".$start_date."' ,mon_duration,'00:00')) +
				TIME_TO_SEC( IF(tue_date = '".$start_date."' ,tue_duration,'00:00')) +
				TIME_TO_SEC( IF(wed_date = '".$start_date."' ,wed_duration,'00:00')) +
				TIME_TO_SEC( IF(thu_date = '".$start_date."' ,thu_duration,'00:00')) +
				TIME_TO_SEC( IF(fri_date = '".$start_date."' ,fri_duration,'00:00')) +
				TIME_TO_SEC( IF(sat_date = '".$start_date."' ,sat_duration,'00:00')))/60)%60,2,'0'))";
				
				$andwhere = " AND (sun_date = '".$start_date."' OR mon_date = '".$start_date."' OR tue_date = '".$start_date."' OR wed_date = '".$start_date."' OR thu_date = '".$start_date."' OR fri_date = '".$start_date."' OR sat_date = '".$start_date."')";
			}
			else
			{
				$duration = "concat(floor(SUM( TIME_TO_SEC( et.week_duration ))/3600),':',lpad(floor(SUM( TIME_TO_SEC( et.week_duration ))/60)%60,2,'0'))";
				$andwhere = " AND et.ts_year >= ".$sd_year." AND et.ts_year <=".$ed_year." AND et.ts_month >= ".$sd_month." AND et.ts_month <= ".$ed_month;
			}
		}
		if($project_id != ''){
			$andwhere .= " AND p.id = '".$project_id."'";
		}
		$db = Zend_Db_Table::getDefaultAdapter();
		$select = $this->select()
			   		   ->setIntegrityCheck(false)
					   ->from(array('et' => 'tm_emp_timesheets'),array('p.project_name','et.project_id','userId'=>'et.emp_id','duration'=>$duration))  
					   ->joinInner(array('p'=>'tm_projects'), 'p.id = et.project_id',array())
					   ->where('et.is_active=1  and p.is_active = 1 '.$andwhere.' and et.emp_id = '.$empId)
					   ->group('et.project_id');
					  // echo $select;
		return $this->fetchAll($select)->toArray();
	}
	public function getProjTaskDuration($empId,$start_date,$end_date,$project_id,$param)
	{
		$andwhere = " AND (1=1)";
		if($start_date != "")
		{
			if($end_date == "")
			{
				//$end_date = date('%Y-%m-%d %H:%i:%s');
				$end_date = date('%Y-%m-%d');
			}
			$start_dates=strtotime($start_date);
			$sd_month=date("m",$start_dates);
			$sd_year=date("Y",$start_dates);
			
			$end_dates=strtotime($end_date);
			$ed_month=date("m",$end_dates);
			$ed_year=date("Y",$end_dates);
			
			
			$andwhere = " AND et.ts_year >= ".$sd_year." AND et.ts_year <=".$ed_year." AND et.ts_month >= ".$sd_month." AND et.ts_month <= ".$ed_month;
			//$andwhere = " AND et.created BETWEEN STR_TO_DATE('".$start_date."','%Y-%m-%d %H:%i:%s') AND STR_TO_DATE('".$end_date."','%Y-%m-%d %H:%i:%s')";
			$duration="";
			if($param=="" || $param=="undefined" || $param=="Last 7 days")
			{
				$duration = "CONCAT(FLOOR(SUM( TIME_TO_SEC( IF(sun_date BETWEEN '".$start_date."' AND '".$end_date."',sun_duration,'00:00')) +
				TIME_TO_SEC( IF(mon_date BETWEEN '".$start_date."' AND '".$end_date."',mon_duration,'00:00')) +
				TIME_TO_SEC( IF(tue_date BETWEEN '".$start_date."' AND '".$end_date."',tue_duration,'00:00')) +
				TIME_TO_SEC( IF(wed_date BETWEEN '".$start_date."' AND '".$end_date."',wed_duration,'00:00')) +
				TIME_TO_SEC( IF(thu_date BETWEEN '".$start_date."' AND '".$end_date."',thu_duration,'00:00')) +
				TIME_TO_SEC( IF(fri_date BETWEEN '".$start_date."' AND '".$end_date."',fri_duration,'00:00')) +
				TIME_TO_SEC( IF(sat_date BETWEEN '".$start_date."' AND '".$end_date."',sat_duration,'00:00')))/3600),':',
				LPAD(FLOOR(SUM( TIME_TO_SEC( IF(sun_date BETWEEN '".$start_date."' AND '".$end_date."',sun_duration,'00:00')) +
				TIME_TO_SEC( IF(mon_date BETWEEN '".$start_date."' AND '".$end_date."',mon_duration,'00:00')) +
				TIME_TO_SEC( IF(tue_date BETWEEN '".$start_date."' AND '".$end_date."',tue_duration,'00:00')) +
				TIME_TO_SEC( IF(wed_date BETWEEN '".$start_date."' AND '".$end_date."',wed_duration,'00:00')) +
				TIME_TO_SEC( IF(thu_date BETWEEN '".$start_date."' AND '".$end_date."',thu_duration,'00:00')) +
				TIME_TO_SEC( IF(fri_date BETWEEN '".$start_date."' AND '".$end_date."',fri_duration,'00:00')) +
				TIME_TO_SEC( IF(sat_date BETWEEN '".$start_date."' AND '".$end_date."',sat_duration,'00:00')))/60)%60,2,'0'))";
				
				
				$andwhere =" AND (sun_date BETWEEN '".$start_date."' AND '".$end_date."' OR mon_date BETWEEN '".$start_date."' AND '".$end_date."'
				OR tue_date BETWEEN '".$start_date."' AND '".$end_date."' OR wed_date BETWEEN '".$start_date."' AND '".$end_date."'
				OR thu_date BETWEEN '".$start_date."' AND '".$end_date."' OR fri_date BETWEEN '".$start_date."' AND '".$end_date."'
				OR sat_date BETWEEN '".$start_date."' AND '".$end_date."')";
			}
			else if($param=='Today')
			{
				$duration = "CONCAT(FLOOR(SUM( TIME_TO_SEC( IF(sun_date = '".$start_date."',sun_duration,'00:00')) +
				TIME_TO_SEC( IF(mon_date = '".$start_date."' ,mon_duration,'00:00')) +
				TIME_TO_SEC( IF(tue_date = '".$start_date."' ,tue_duration,'00:00')) +
				TIME_TO_SEC( IF(wed_date = '".$start_date."' ,wed_duration,'00:00')) +
				TIME_TO_SEC( IF(thu_date = '".$start_date."' ,thu_duration,'00:00')) +
				TIME_TO_SEC( IF(fri_date = '".$start_date."' ,fri_duration,'00:00')) +
				TIME_TO_SEC( IF(sat_date = '".$start_date."' ,sat_duration,'00:00')))/3600),':',
				LPAD(FLOOR(SUM( TIME_TO_SEC( IF(sun_date = '".$start_date."' ,sun_duration,'00:00')) +
				TIME_TO_SEC( IF(mon_date = '".$start_date."' ,mon_duration,'00:00')) +
				TIME_TO_SEC( IF(tue_date = '".$start_date."' ,tue_duration,'00:00')) +
				TIME_TO_SEC( IF(wed_date = '".$start_date."' ,wed_duration,'00:00')) +
				TIME_TO_SEC( IF(thu_date = '".$start_date."' ,thu_duration,'00:00')) +
				TIME_TO_SEC( IF(fri_date = '".$start_date."' ,fri_duration,'00:00')) +
				TIME_TO_SEC( IF(sat_date = '".$start_date."' ,sat_duration,'00:00')))/60)%60,2,'0'))";
				
				
				
				$andwhere = " AND (sun_date = '".$start_date."' OR mon_date = '".$start_date."' OR tue_date = '".$start_date."' OR wed_date = '".$start_date."' OR thu_date = '".$start_date."' OR fri_date = '".$start_date."' OR sat_date = '".$start_date."')";
			}
			else
			{
				$duration = "concat(floor(SUM( TIME_TO_SEC( et.week_duration ))/3600),':',lpad(floor(SUM( TIME_TO_SEC( et.week_duration ))/60)%60,2,'0'))";
				$andwhere = " AND et.ts_year >= ".$sd_year." AND et.ts_year <=".$ed_year." AND et.ts_month >= ".$sd_month." AND et.ts_month <= ".$ed_month;
			}
		}

		if($empId != "")
		{
			$andwhere .= ' AND et.emp_id = '.$empId;
		}
		
		$select = $this->select()
		->setIntegrityCheck(false)
		->from(array('et' => 'tm_emp_timesheets'),array('p.project_name','t.task',
                                'duration'=>$duration))  
		->joinInner(array('pt'=>'tm_project_tasks'), 'pt.id = et.project_task_id',array())
		->joinInner(array('t'=>'tm_tasks'), 't.id = pt.task_id',array())
		->joinInner(array('p'=>'tm_projects'), 'p.id = pt.project_id and p.id = et.project_id',array())
		->joinInner(array('e'=>'main_employees_summary'), 'e.user_id = et.emp_id',array())
		->joinLeft(array('pm'=>'tm_project_employees'), 'p.id = pm.project_id and pm.emp_id = et.emp_id ',array())
		->where('et.is_active=1 and p.id='.$project_id.' '.$andwhere)
		->group('t.id');
		//echo $select;//exit;
		return $this->fetchAll($select)->toArray();
	}


//=============================================================================================	
//=============================================================================================	
//==============Add header on the employee timesheet header==============================================	
	/* added or commented by PSI */
  public function employeeReportHeader()
    {
        $cols_param_arr = array('firstname'=>'First Name','lastname'=>'Last Name',
                             'employeeId' =>'Employee ID','emp_leave_limit'=>'leave entitlement per year','serviceyear'=>'Service Year','leaveX'=>'Accrued Leave as of today','transfer'=>'BBF (Balance Brought Forward)','totalavailableleave'=>'Balance as of today',
                             'used_leaves'=>'Used Leaves','remainingleaves'=>'Leave Balance','alloted_year'=>'Date of joining','annual_paid_leave'=>'Annual paid leave / Vaccation','unpaid_annual_leave'=>'Unpaid Annual leave','sick_leave'=>'Sick leave','maternity_leave'=>'Maternity leave','compensatory_leave'=>'Compensatory leave','accident_leave'=>'Accident leave','miscarriage_leave'=>'Miscarriage leave','miscarriagepaternity_leave'=>'Miscarriage Paternity leave','paternity_leave'=>'Paternity leave','childnursing_leave'=>'Child Nursing leave','pernatalrelated_leave'=>'Per Natal related leave','compassionateNIR_leave'=>'Compassionate leave NIR','CompassionateIR_leave'=>'Compassionate leave IR','variableleavefamily'=>'Variable Leave Family','examinationandstudy_leave'=>'Examination and Study Leave','court_leave'=>'Court Leave','emergencyunpaid_leave'=>'Emergency Unpaid Leave');
		// var_dump($cols_param_arr);
		// die('hay hay');
                      return $cols_param_arr;
    }
    /* psi modification ends here */
//=============================================================================================	
//=============================================================================================	
//=============================================================================================	

}
