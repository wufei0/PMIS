<?php

require_once $_SESSION['path'].'/lib/classes/Authentication.php';
	
class DTR{
	var $PayPeriod; 
	public function showEmpDTR($EmpID,$Year,$Month,$PayPeriod){
		$this->PayPeriod=$PayPeriod;
		
		$MySQLi=new MySQLClass();
		
		$Authentication=new Authentication();
		$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD017'));
		for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
		
		$dtr_header="";
		$emp=$MySQLi->GetArray("SELECT CONCAT_WS(',',`EmpLName`,CONCAT_WS(' ',`EmpFName`,CONCAT_WS('.',SUBSTRING(`EmpMName`,1,1),''))) AS EmpName FROM `tblemppersonalinfo` WHERE `EmpID`='".$EmpID."';");
		$dtr_header=$emp['EmpName']."($EmpID)<br/>".date('F Y',mktime(0,0,0,$Month,1,$Year));
		unset($emp);
			
		$content ="";
		$content .="<table width='650px' class='i_table' cellspacing='0px' borderspacing='0px'>";
		$content .="<tr>";
		$content .="<td class='i_table_header' width='30px' rowspan='2'>Date</td>";
		$content .="<td class='i_table_header' width='35px' rowspan='2'>Day</td>";
		$content .="<td class='i_table_header' colspan='2'>AM</td>";
		$content .="<td class='i_table_header' colspan='2'>PM</td>";
		$content .="<td class='i_table_header' colspan='2'>OT</td>";
		$content .="<td class='i_table_header' width='55px' rowspan='2'>Lates/<br/>Undertime</td>";
		$content .="<td class='i_table_header' width='60px' rowspan='2'>HrsWrk</td>";
		$content .="<td class='i_table_header' width='60px' rowspan='2'>OTHrs</td>";
		$content .="</tr>";
		$content .="<tr>";
		$content .="<td class='i_table_header' width='60px'>Time In1</td>";
		$content .="<td class='i_table_header' width='60px'>Time Out</td>";
		$content .="<td class='i_table_header' width='60px'>Time In</td>";
		$content .="<td class='i_table_header' width='60px'>Time Out</td>";
		$content .="<td class='i_table_header' width='60px'>Time In</td>";
		$content .="<td class='i_table_header' width='60px'>Time Out</td>";
		$content .="</tr>";
		echo $content;
		
		/* Fix Month */
		$Month=($Month>9)?$Month:'0'.$Month;
		$DTRstartDate=1;
		$DaysOfMonth=cal_days_in_month(CAL_GREGORIAN,$Month,$Year);
		switch($PayPeriod){
			case 1	:	$DTRstartDate=1;
						$DaysOfMonth=15;
						break;
			case 2	:	$DTRstartDate=16;
						$DaysOfMonth=cal_days_in_month(CAL_GREGORIAN,$Month,$Year);
						break;
			default	:	$DTRstartDate=1;
						$DaysOfMonth=cal_days_in_month(CAL_GREGORIAN,$Month,$Year);
						break;
		}
		$DD=$DaysOfMonth;
		//if($Month<date('m')){$DD=$DaysOfMonth;}
		//else{$DD=(date('j')>=$DaysOfMonth)?$DaysOfMonth:date('j');}
		$TotalAbsents=$TotalTardiness=$TotalLSec=$TotalHrsWk=$TotalOtHrs=0;
		$TotalLates="00m 00s";
		for($date=$DTRstartDate;$date<=$DD;$date+=1){
			$LogTime=Array('AMIN' => "-", 'AMOUT' => "-", 'PMIN' => "-", 'PMOUT' => "-", 'OTIN' => "-", 'OTOUT' => "-", 'Lates' => '', 'HrsWrk' => '', 'OTHrs' => '');
			$Date=$date>9?$date:"0".$date;
			$Day=date("D", mktime(0, 0, 0, $Month, $Date, $Year));
			$thisDay=$MySQLi->GetArray("SELECT * FROM `tblempdtr` WHERE `DTRID`='DTR".$Year.$Month.$Date.$EmpID."';");
			
			/* CHECK HOLIDAYS */
			if($isHoliday=$MySQLi->GetArray("SELECT `HoliDescription` FROM `tblholidays` WHERE `HoliDate`='1970-$Month-$Date';")){$LogTime['AMIN']=$LogTime['PMIN']="<font color='#AA4433'><b>".$isHoliday['HoliDescription']."</b></font>";}
			elseif($isHoliday=$MySQLi->GetArray("SELECT `HoliDescription` FROM `tblholidays` WHERE `HoliDate`='$Year-$Month-$Date';")){$LogTime['AMIN']=$LogTime['PMIN']="<font color='#339966'><b>".$isHoliday['HoliDescription']."</b></font>";}
			
			else{
				/* AM */
				if(substr($thisDay['DTRIN01'],0,4)=="1980"){ /* 1980 - if Leave */
					if(!($Day=='Sat'||$Day=='Sun')){
						$LeaveDesc=$MySQLi->GetArray("SELECT * FROM `tbldtrdaystatus` WHERE `DayStatusID`='".$thisDay['DayStatusID']."';");
						$LogTime['AMIN']="<font color='#3399FF'><b>".$LeaveDesc['DayStatusDesc']."</b></font>";$LogTime['AMOUT']="";
					}
				}
				elseif(substr($thisDay['DTRIN01'],0,4)=="1990"){ /* 1990 - if TO */
					$LogTime['AMIN']="ON TRAVEL";$LogTime['AMOUT']="";
				}
				else{
					$LogTime['AMIN']=($thisDay['DTRIN01']!="")?(($thisDay['DTRIN01']!="1970-01-01 00:00:01")?substr($thisDay['DTRIN01'],-8):"-"):"-";
					$LogTime['AMOUT']=($thisDay['DTROUT01']!="")?(($thisDay['DTROUT01']!="1970-01-01 00:00:01")?substr($thisDay['DTROUT01'],-8):"-"):"-";
					if(($LogTime['AMIN']>"08:10:59")&&($LogTime['AMIN']!="-")){$LogTime['AMIN'].="*";$TotalTardiness+=1;}
					//if(($LogTime['AMOUT']<"12:00:00")&&($LogTime['AMOUT']!="-")){$LogTime['AMOUT'].="*";$TotalTardiness+=1;}
					if(($thisDay['DTRIN01']=="1970-01-01 00:00:01")||($thisDay['DTROUT01']=="1970-01-01 00:00:01")){
						if(!($Day=='Sat'||$Day=='Sun'||$DD==date('j'))){$TotalAbsents+=0.5;}
					}
				}
				
				/* PM */
				if(substr($thisDay['DTRIN02'],0,4)=="1980"){ /* 1980 - if Leave */
					if(!($Day=='Sat'||$Day=='Sun')){
						$LeaveDesc=$MySQLi->GetArray("SELECT * FROM `tbldtrdaystatus` WHERE `DayStatusID`='".$thisDay['DayStatusID']."';");
						$LogTime['PMIN']="<font color='#3399FF'><b>".$LeaveDesc['DayStatusDesc']."</b></font>";$LogTime['PMOUT']="";
					}
				}
				elseif(substr($thisDay['DTRIN02'],0,4)=="1990"){ /* 1990 - if TO */
					$LogTime['AMIN']="ON TRAVEL";$LogTime['AMOUT']="";
				}
				else{
					$LogTime['PMIN']=($thisDay['DTRIN02']!="")?(($thisDay['DTRIN02']!="1970-01-01 00:00:01")?substr($thisDay['DTRIN02'],-8):"-"):"-";
					$LogTime['PMOUT']=($thisDay['DTROUT02']!="")?(($thisDay['DTROUT02']!="1970-01-01 00:00:01")?substr($thisDay['DTROUT02'],-8):"-"):"-";
					if(($LogTime['PMIN']>"13:10:59")&&($LogTime['PMIN']!="-")){$LogTime['PMIN'].="*";$TotalTardiness+=1;}
					//if(($LogTime['PMOUT']<"17:00:00")&&($LogTime['PMOUT']!="-")){$LogTime['PMOUT'].="*";$TotalTardiness+=1;}
					if(($thisDay['DTRIN02']=="1970-01-01 00:00:01")||($thisDay['DTROUT02']=="1970-01-01 00:00:01")){if(!($Day=='Sat'||$Day=='Sun'||$DD==date('j'))){$TotalAbsents+=0.5;}}
				}
				
				/* OT */
				$LogTime['OTIN']=($thisDay['DTRIN03']!="")?(($thisDay['DTRIN03']!="1970-01-01 00:00:01")?substr($thisDay['DTRIN03'],-8):"-"):"-";
				// $LogTime['OTIN']=($thisDay['DTRIN03']!="1970-01-01 00:00:01")?substr($thisDay['DTRIN03'],-8):"-";
				$LogTime['OTOUT']=($thisDay['DTROUT03']!="")?(($thisDay['DTROUT03']!="1970-01-01 00:00:01")?substr($thisDay['DTROUT03'],-8):"-"):"-";
				// $LogTime['OTOUT']=($thisDay['DTROUT03']!="1970-01-01 00:00:01")?substr($thisDay['DTROUT03'],-8):"-";
			}
			
			
			
			
			$LogTime['Lates']=$thisDay['DTRLates'];
			$Lates=explode(" ",$thisDay['DTRLates']);
			$Lm=intval($Lates[0]);
			$Lates[1]=isset($Lates[1])?$Lates[1]:0;
			$TotalLSec+=intval($Lates[1])+($Lm*60);
			$TotalLates=((floor($TotalLSec/60))<9?"0".(floor($TotalLSec/60)):(floor($TotalLSec/60)))."m ".(($TotalLSec%60)<9?"0".($TotalLSec%60):($TotalLSec%60))."s";
			$LogTime['HrsWrk']=$thisDay['DTRHrsWeek'];$TotalHrsWk+=$thisDay['DTRHrsWeek'];
			$LogTime['OTHrs']=$thisDay['DTROverTime'];$TotalOtHrs+=$thisDay['DTROverTime'];

			/* TD Saturday and Sunday class */
			if($Day=='Sat'){$field_we="class='dtr_field_sat'"; $td_class_we="class='dtr_log_field_sat'"; $td_class_e_we="class='dtr_log_field_to_e_sat'"; }
			else if($Day=='Sun'){$field_we="class='dtr_field_sun'"; $td_class_we="class='dtr_log_field_sun'"; $td_class_e_we="class='dtr_log_field_to_e_sun'"; }
			else{$field_we="class='dtr_field'"; $td_class_we="class='dtr_log_field'"; $td_class_e_we="class='dtr_log_field_to_e'";}
			/* TD class */
			$td_class_am1=$td_class_am2=$td_class_pm1=$td_class_pm2=$td_class_ot1=$td_class_ot2="";
			$td_class_am1=$td_class_we;
			$td_class_am2=$td_class_we;
			$td_class_pm1=$td_class_we;
			$td_class_pm2=$td_class_we;
			$td_class_ot1=$td_class_we;
			$td_class_ot2=$td_class_we;
			/*
			if(strlen($LogTime['AMIN'])!=8){$td_class_am1=$td_class_e_we;}else{$td_class_am1=$td_class_we;}
			if(strlen($LogTime['AMOUT'])!=8){$td_class_am2=$td_class_e_we;}else{$td_class_am2=$td_class_we;}
			if(strlen($LogTime['PMIN'])!=8){$td_class_pm1=$td_class_e_we;}else{$td_class_pm1=$td_class_we;}
			if(strlen($LogTime['PMOUT'])!=8){$td_class_pm2=$td_class_e_we;}else{$td_class_pm2=$td_class_we;}
			if(strlen($LogTime['OTIN'])!=8){$td_class_ot1=$td_class_e_we;}else{$td_class_ot1=$td_class_we;}
			if(strlen($LogTime['OTOUT'])!=8){$td_class_ot2=$td_class_e_we;}else{$td_class_ot2=$td_class_we;}
			*/
			echo "<tr>";
			echo "<td $field_we align='center'>$date</td>";
			echo "<td $field_we align='center'>".strtoupper($Day)."</td>";
			
			if((strlen($LogTime['AMIN'])>9)&&(strlen($LogTime['PMIN'])>8)){ /* Whole day LEAVE or TO */
				echo "<td align='center' $td_class_am1 colspan='6'>".$LogTime['AMIN']."</td>";
			}
			else{
				if(strlen($LogTime['AMIN'])>9){ /* Half day LEAVE or TO */
					echo "<td align='center' $td_class_am1 colspan='2'>".$LogTime['AMIN']."</td>";
				}
				else{
					if(($LogTime['AMIN']=="-") && $Authorization[2] && $Authorization[3]){
						echo "<td align='center' $td_class_am1 onclick='alterThis(this,&#39;".$Date."-A1&#39;);'>-</td>";
					}
					else{
						echo "<td align='center' $td_class_am1>".$LogTime['AMIN']."</td>";
					}
					if(($LogTime['AMOUT']=="-") && $Authorization[2] && $Authorization[3]){
						echo "<td align='center' $td_class_am1 onclick='alterThis(this,&#39;".$Date."-A2&#39;);'>-</td>";
					}
					else{
						echo "<td align='center' $td_class_am1>".$LogTime['AMOUT']."</td>";
					}
				}
				if(strlen($LogTime['PMIN'])>9){ /* Half day LEAVE or TO */
					echo "<td align='center' $td_class_am1 colspan='2'>".$LogTime['PMIN']."</td>";
				}
				else{
					if(($LogTime['PMIN']=="-") && $Authorization[2] && $Authorization[3]){
						echo "<td align='center' $td_class_am1 onclick='alterThis(this,&#39;".$Date."-P1&#39;);'>-</td>";
					}
					else{
						echo "<td align='center' $td_class_am1>".$LogTime['PMIN']."</td>";
					}
					if(($LogTime['PMOUT']=="-") && $Authorization[2] && $Authorization[3]){
						echo "<td align='center' $td_class_am1 onclick='alterThis(this,&#39;".$Date."-P2&#39;);'>-</td>";
					}
					else{
						echo "<td align='center' $td_class_am1>".$LogTime['PMOUT']."</td>";
					}
				}
				if(($LogTime['OTIN']=="-") && $Authorization[2] && $Authorization[3]){
					echo "<td align='center' $td_class_am1 onclick='alterThis(this,&#39;".$Date."-O1&#39;);'>-</td>";
				}
				else{
					echo "<td align='center' $td_class_am1>".$LogTime['OTIN']."</td>";	/*IN OT */
				}
				if(($LogTime['OTOUT']=="-") && $Authorization[2] && $Authorization[3]){
					echo "<td align='center' $td_class_am1 onclick='alterThis(this,&#39;".$Date."-O2&#39;);'>-</td>";
				}
				else{
					echo "<td align='center' $td_class_am1>".$LogTime['OTOUT']."</td>";	/*OUT OT */
				}
				
			}
			echo "<td $field_we align='center'>".$LogTime['Lates']."</td>";	/*Lates */
			echo "<td $field_we align='center'>".$LogTime['HrsWrk']."</td>";	/*HrsWrk */
			echo "<td $field_we align='center'>".$LogTime['OTHrs']."</td>";	/*OTHrs */
			echo "</tr>";
		}
		
		/* Add more rows to fill remaining days of the month */
		while($date <= $DaysOfMonth){
			$Day=date("D", mktime(0, 0, 0, $Month, $date, $Year));
			if($Day=='Sat'){$field_we="class='dtr_field_sat'"; $log_we="class='dtr_log_field_sat'"; }
			else if($Day=='Sun'){$field_we="class='dtr_field_sun'"; $log_we="class='dtr_log_field_sun'"; }
			else{$field_we="class='dtr_field'"; $log_we="class='dtr_log_field'"; }
			
			echo "<tr>";
			echo "<td $field_we align='center'>$date</td>";
			echo "<td $field_we align='center'>".strtoupper($Day)."</td>";
			echo "<td $log_we align='center'></td>";			/* IN AM */
			echo "<td $log_we align='center'></td>";			/* OUT AM */
			echo "<td $log_we align='center'></td>";			/* IN PM */
			echo "<td $log_we align='center'></td>";			/* OUT PM */
			echo "<td $log_we align='center'></td>";			/* IN OT */
			echo "<td $log_we align='center'></td>";			/* OUT OT */
			echo "<td $field_we align='center'></td>";	/* Lates */
			echo "<td $field_we align='center'></td>";	/* HrsWrk */
			echo "<td $field_we align='center'></td>";	/* OTHrs */
			echo "</tr>";
			/*Increment to next date */
			$date +=1;
		}
		
		echo "</table>";
	
		echo "<table class='ui-widget-content ui-corner-all' style='width:650px;padding:0px;border-spacing:1px;margin:7px 10px 7px 10px;'>";
		echo "<tr>";
		echo "<td rowspan='2' class='form_label' style='width:60px;padding-right:3px;'><label>TARDINESS</label></td>";
		echo "<td rowspan='2' class='pds_form_input'><div name='VL_credit' id='VL_credit' class='text_input ui-widget-content ui-corner-all' style='width:30px;text-align:center;font-size:1.7em;color:red;padding:3px 3px 3px 3px;text-shadow:1px 1px 0 #AAA;'>".number_format($TotalTardiness,0)."</div></td>";
		echo "<td rowspan='2' class='form_label' style='width:60px;padding-right:3px;'><label>MINUTES<br/>LATE</label></td>";
		echo "<td rowspan='2' class='pds_form_input'><div name='SL_credit' id='SL_credit' class='text_input ui-widget-content ui-corner-all' style='width:60px;text-align:right;font-size:1.7em;padding:3px 3px 3px 3px;text-shadow:1px 1px 0 #AAA;'>".number_format(floatval($TotalLates),2)."</div></td>";
		echo "<td rowspan='2' class='form_label' style='width:60px;padding-right:3px;'><label>DAYS<br/>ABSENT</label></td>";
		echo "<td rowspan='2' class='pds_form_input'><div name='SL_credit' id='SL_credit' class='text_input ui-widget-content ui-corner-all' style='width:60px;text-align:right;font-size:1.7em;padding:3px 3px 3px 3px;text-shadow:1px 1px 0 #AAA;'>".number_format($TotalAbsents,2)."</div></td>";
		echo "<td rowspan='2' class='form_label' style='width:60px;padding-right:3px;'><label>HOURS<br/>WEEK</label></td>";
		echo "<td rowspan='2' class='pds_form_input'><div name='SL_credit' id='SL_credit' class='text_input ui-widget-content ui-corner-all' style='width:60px;text-align:right;font-size:1.7em;padding:3px 3px 3px 3px;text-shadow:1px 1px 0 #AAA;'>".number_format($TotalHrsWk,2)."</div></td>";
		echo "<td rowspan='2' class='form_label' style='width:60px;padding-right:3px;'><label>OVERTIME<br/>HOURS</label></td>";
		echo "<td rowspan='2' class='pds_form_input'><div name='SL_credit' id='SL_credit' class='text_input ui-widget-content ui-corner-all' style='width:60px;text-align:right;font-size:1.7em;padding:3px 3px 3px 3px;text-shadow:1px 1px 0 #AAA;'>".number_format($TotalOtHrs,2)."</div></td>";
		echo "</tr>";
		echo "</table>";
		
	}
	
	public function processEmpDTR($EmpID,$Year,$Month){
		$MySQLi=new MySQLClass();
		
		/* Fix Month */
		$Month=($Month>9)?$Month:'0'.$Month;
		$DTRstartDate=1;
		
		$DD=cal_days_in_month(CAL_GREGORIAN,$Month,$Year);
		for($date=$DTRstartDate;$date<=$DD;$date+=1){
			$Date=$date>9?$date:"0".$date;
			$DTRID="DTR".$Year.$Month.$Date.$EmpID;
			$DTRVerCode=md5($_SESSION['user']."-".$EmpID."-".$Month.$Year);
			
			$sql ="INSERT INTO `tblempdtr`(`DTRID`, `EmpID`, `DayStatusID`, `DTRIN01`, `DTROUT01`, `DTRIN02`, `DTROUT02`, `DTRIN03`, `DTROUT03`, `DTRIN04`, `DTROUT04`, `DTRLates`, `DTROverTime`, `DTRHrsWeek`, `DTRVerCode`, `DTRRemarks`, `RECORD_TIME`) VALUES('$DTRID', '$EmpID', '', '1970-01-01 00:00:01', '1970-01-01 00:00:01', '1970-01-01 00:00:01', '1970-01-01 00:00:01', '1970-01-01 00:00:01', '1970-01-01 00:00:01', '1970-01-01 00:00:01', '1970-01-01 00:00:01', '', '', '', '', '', NOW());";
			$MySQLi->sqlQuery($sql,false);
			
			/* Check if ON LEAVE */
		
			$LogTime=Array('AMIN'=>'','AMOUT'=>'','PMIN'=>'','PMOUT'=>'','OTIN'=>'','OTOUT'=>'','HrsWrk'=>'','OTHrs'=>'');
			$computeLateUnderTime=false;
			$LatesMM=$LatesSS=$LateAM=$UnderAM=$LatePM=$UnderPM=0;
			/* YYYY-MM-DD HH:MM:SS --->  $Year-$Month-$date $Hour:$Minutes:$Second */
			
			$thisDay=$MySQLi->sqlQuery("SELECT `BioLogTime` FROM `tblbiometrics` WHERE(`BioLogTime` LIKE '".$Year."-".$Month."-".($date>9?$date:"0".$date)."%'  AND `EmpID`='".$EmpID."') ORDER BY `BioLogTime` ASC;");
			while($logs=mysqli_fetch_array($thisDay, MYSQLI_BOTH)){
				$log_date_time=explode(" ",$logs['BioLogTime']);
				$log_date=explode("-",$log_date_time[0]);
				$Y=$log_date[0]; $n=$log_date[1]; $j=$log_date[2];
				$log_time=explode(":",$log_date_time[1]);
				$H=$log_time[0]; $i=$log_time[1]; $s=$log_time[2];
				
				$BioLogTime=mktime($H,$i,$s,intval($n),intval($j),intval($Y));
				
				/* AMIN */
				if(($BioLogTime >=mktime(0,0,0,intval($n),intval($j),intval($Y)))&&($BioLogTime <=mktime(10,59,59,intval($n),intval($j),intval($Y)))){
					$LogTime['AMIN']=date('Y-m-d h:i:s',$BioLogTime);
					$LateAM=$BioLogTime - mktime(8,0,0,intval($n),intval($j),intval($Y));
					$LateAM=($LateAM<0)?0:$LateAM;
					$computeLateUnderTime=true;
				}
				/* AMOUT */
				else if(($BioLogTime >=mktime(11,0,0,intval($n),intval($j),intval($Y)))&&($BioLogTime <=mktime(12,29,59,intval($n),intval($j),intval($Y)))){
					$LogTime['AMOUT']=date('Y-m-d h:i:s',$BioLogTime);
					$UnderAM=mktime(12,0,0,intval($n),intval($j),intval($Y)) - $BioLogTime;
					$UnderAM=($UnderAM<0)?0:$UnderAM;
					$computeLateUnderTime=true;
				}
				/* PMIN */
				else if(($BioLogTime >=mktime(12,30,0,intval($n),intval($j),intval($Y)))&&($BioLogTime <=mktime(15,59,59,intval($n),intval($j),intval($Y)))){
					$LogTime['PMIN']=date('Y-m-d h:i:s',$BioLogTime);
					$LatePM=$BioLogTime - mktime(13,0,0,intval($n),intval($j),intval($Y));
					$LatePM=($LatePM<0)?0:$LatePM;
					$computeLateUnderTime=true;
				}
				/* PMOUT */
				else if(($BioLogTime >=mktime(16,0,0,intval($n),intval($j),intval($Y)))&&($BioLogTime <=mktime(23,59,59,intval($n),intval($j),intval($Y)))){
					if($LogTime['PMOUT']==''){
						$LogTime['PMOUT']=date('Y-m-d h:i:s',$BioLogTime);
						$UnderPM=mktime(17,0,0,intval($n),intval($j),intval($Y)) - $BioLogTime;
						$UnderPM=($UnderPM<0)?0:$UnderPM;
						$computeLateUnderTime=true;
					}
					/* OTIN */
					else if($LogTime['OTIN']==''){$LogTime['OTIN']=date('Y-m-d h:i:s',$BioLogTime);}
					/* OTOUT */
					else{$LogTime['OTOUT']=date('Y-m-d h:i:s',$BioLogTime);} 
				}
			}
			
			/* If AMIN or AMOUT is Blank check LEAVE, TRAVEL ORDER and CTO */
			if(($LogTime['AMIN']=='')||($LogTime['AMOUT']=='')){
				$sql="SELECT `tblleavetypes`.`LeaveTypeCode` FROM `tblempleaveapplications` JOIN `tblleavetypes` ON `tblempleaveapplications`.`LeaveTypeID` = `tblleavetypes`.`LeaveTypeID` WHERE(DATE_FORMAT(`tblempleaveapplications`.`LivAppIncDateFrom`, '%Y-%m-%d %H:%i:%s') <= '".$Year."-".$Month."-".($date>9?$date:"0".$date)." 09:00:00') AND (DATE_FORMAT(`tblempleaveapplications`.`LivAppIncDateTo`, '%Y-%m-%d %H:%i:%s') >= '".$Year."-".$Month."-".($date>9?$date:"0".$date)." 09:00:00') AND `tblempleaveapplications`.`EmpID`='".$EmpID."' AND `LivAppStatus` = 4;";
				$isLeave=$MySQLi->sqlQuery($sql);
				if($LeaveInfo=mysqli_fetch_array($isLeave, MYSQLI_BOTH)){
					$LeaveType=$MySQLi->GetArray("SELECT `DayStatusID` FROM tbldtrdaystatus WHERE `DayStatusDesc` LIKE '% ".$LeaveInfo['LeaveTypeCode']."' LIMIT 1;");
					$LogTime['AMIN']=$LogTime['AMOUT']=$LeaveType['DayStatusID'];
				}
				else{
					$LogTime['AMIN']=$LogTime['AMOUT']="1970-01-01 00:00:01";
				}
			} unset($isLeave);unset($LeaveType);
			
			/* If PMIN or PMOUT is Blank check LEAVE, TRAVEL ORDER and CTO */
			if(($LogTime['PMIN']=='')||($LogTime['PMOUT']=='')){
				$sql="SELECT `tblleavetypes`.`LeaveTypeCode` FROM `tblempleaveapplications` JOIN `tblleavetypes` ON `tblempleaveapplications`.`LeaveTypeID` = `tblleavetypes`.`LeaveTypeID` WHERE(DATE_FORMAT(`tblempleaveapplications`.`LivAppIncDateFrom`, '%Y-%m-%d %H:%i:%s') <= '".$Year."-".$Month."-".($date>9?$date:"0".$date)." 15:00:00') AND(DATE_FORMAT(`tblempleaveapplications`.`LivAppIncDateTo`, '%Y-%m-%d %H:%i:%s') >= '".$Year."-".$Month."-".($date>9?$date:"0".$date)." 15:00:00') AND `tblempleaveapplications`.`EmpID`='".$EmpID."' AND `LivAppStatus` = 4;";
				$isLeave=$MySQLi->sqlQuery($sql);
				if($LeaveInfo=mysqli_fetch_array($isLeave, MYSQLI_BOTH)){
					$LeaveType=$MySQLi->GetArray("SELECT `DayStatusID` FROM tbldtrdaystatus WHERE `DayStatusDesc` LIKE '%".$LeaveInfo['LeaveTypeCode']."' LIMIT 1;");
					$LogTime['PMIN']=$LogTime['PMIN']=$LeaveType['DayStatusID'];
				}
				else{
					$LogTime['PMIN']=$LogTime['PMOUT']="1970-01-01 00:00:01";
				}
			} unset($isLeave);unset($LeaveType);
			
			/* If OTIN or OTOUT is Blank */
			$LogTime['OTIN']=($LogTime['OTIN']=='')?"1970-01-01 00:00:01":$LogTime['OTIN'];
			$LogTime['OTOUT']=($LogTime['OTOUT']=='')?"1970-01-01 00:00:01":$LogTime['OTOUT'];
			
			/* Late and Under Time Computation */
			$LatesMM="";
			if($computeLateUnderTime){
				$LatesSS=$LateAM + $UnderAM + $LatePM + $UnderPM;
				$LatesMM=floor($LatesSS/60);
				$sWrk=28800-$LatesSS;
				$SecWrk=$sWrk % 60;
				$SecWrk=$SecWrk>9?$SecWrk:"0".$SecWrk;
				$MinWrk=floor($sWrk / 60) % 60;
				$MinWrk=$MinWrk>9?$MinWrk:"0".$MinWrk;
				$HrsWrk=floor($sWrk / 3600);
				$HrsWrk=$HrsWrk>9?$HrsWrk:"0".$HrsWrk;
				$LogTime['HrsWrk']=$HrsWrk.":".$MinWrk.":".$SecWrk;
			}
			else{$LogTime['HrsWrk']="";}
			
			/* Codes for updating tblempdtr(verified dtr) */
			$DayStatusID="";
			if(strlen($LogTime['AMIN'])==5){
				$DayStatusID=$LogTime['AMIN'];
				$LogTime['AMIN']=$LogTime['AMOUT']="1980-01-01 00:00:01";
			}
			if(strlen($LogTime['PMIN'])==5){
				$DayStatusID=$LogTime['PMIN'];
				$LogTime['PMIN']=$LogTime['PMOUT']="1980-01-01 00:00:01";
			}
			$DTRLates=$LatesSS;
			$DTROverTime="";
			$DTRHrsWeek=$LogTime['HrsWrk'];
			
			$sql="UPDATE `tblempdtr` SET `DayStatusID`='".$DayStatusID."',`DTRIN01`='".$LogTime['AMIN']."',`DTROUT01`='".$LogTime['AMOUT']."',`DTRIN02`='".$LogTime['PMIN']."',`DTROUT02`='".$LogTime['PMOUT']."',`DTRIN03`='".$LogTime['OTIN']."',`DTROUT03`='".$LogTime['OTOUT']."',`DTRLates`='$DTRLates',`DTROverTime`='$DTROverTime',`DTRHrsWeek`='$DTRHrsWeek',`DTRVerCode`='$DTRVerCode',`DTRRemarks`='',`RECORD_TIME`=NOW() WHERE `DTRID`='$DTRID' LIMIT 1;";
			$MySQLi->sqlQuery($sql,false);
		}
	}

	function showOffDTR($SubOffID,$ApptStID,$Year,$Month,$PayPeriod){/* $ApptStID=Job Order/Casual/Permanent */
		$MySQLi=new MySQLClass();
		
		$result=$MySQLi->sqlQuery("SELECT `EmpID` FROM(`tblempservicerecords` JOIN `tblapptstatus` ON `tblempservicerecords`.`ApptStID`=`tblapptstatus`.`ApptStID`) WHERE(`AssignedOfficeID`='".$SubOffID."' AND `tblapptstatus`.`ApptStID`='".$ApptStID."') ORDER BY `EmpID`;");
		
		if(mysql_num_rows($result)>0){
			while($emp=mysqli_fetch_array($result, MYSQLI_BOTH)){
				$this->showEmpDTR($emp['EmpID'],$Year,$Month,$PayPeriod);
			}
		}
		else{
			$content ="";
			$content .="<div class='form_list_'>";
			$content .="<span id='disp_month_year' class='dtr_header'><i>No records found.</i><br/><small>$SubOffID$ApptStID$Year$Month$PayPeriod</small></span>";
			$content .="</div>";
			echo $content;
		} unset($result);
	}
}
?>