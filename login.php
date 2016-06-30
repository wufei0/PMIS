<?php
	ob_start();
	define('ROOT_PATH', dirname(__FILE__));
	
	session_start();
	$_SESSION['path']=ROOT_PATH;
	$_SESSION['theme']="blue";
	
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';
		
	function getMacAddress($RemoteIP){
		$macAddr=false;$arp=`arp -a $RemoteIP`;$lines=explode("\n", $arp);foreach($lines as $line){$cols=preg_split('/\s+/', trim($line));if ($cols[0]==$RemoteIP){return $cols[1];}}
		// return "XX:XX:XX:XX:XX:XX";
	}
	
	$Authentication=new Authentication();
	
	
	$UserID=isset($_POST['UserID'])?strtoupper(strip_tags(trim($_POST['UserID']))):'';
	$Password=isset($_POST['Password'])?trim(strip_tags($_POST['Password'])):"";
		
	$RemoteIP=$_SERVER['REMOTE_ADDR'];
	$RemoteHostName=gethostbyaddr($RemoteIP);
	$UserAgent=$_SERVER['HTTP_USER_AGENT'];
	$RemoteMACAdd=getMacAddress($RemoteIP);
	$fPrintTime=time();
	
	if($UserID!=""){$Authenticate=true;}
	
	if(isset($_SESSION['fingerprint'])&&isset($_SESSION['fprinttime'])){
		if($_SESSION['fingerprint']==md5($UserID.$RemoteIP.$RemoteHostName.$fPrintTime)){
			header('Location: index.php');
		}
	}
	else{
		$Authenticate=false;
		$LoginMsg="";
		
		if($UserID!=""){$Authenticate=true;}
		if($Authenticate){
			$FingerPrint=md5($UserID.$RemoteIP.$UserAgent.$fPrintTime);//$FingerPrint=md5($UserID." ".$UserAgent." ".$RemoteIP." ".$fPrintTime);
			
			$isAuthorized=$Authentication->Authenticate($UserID,$Password,$RemoteIP,$RemoteHostName,$RemoteMACAdd,$FingerPrint);
			$AuthStatus=explode("|",$isAuthorized);
			
			if($AuthStatus[0]==1){
				$MySQLi=new MySQLClass();
				$UserInfo=$MySQLi->GetArray("SELECT `tblemppersonalinfo`.`UserGroupID`, `tblsystemusergroups`.`UserGroupCode`, `tblemppersonalinfo`.`EmpFName`, CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName FROM `tblemppersonalinfo` JOIN `tblsystemusergroups` ON `tblemppersonalinfo`.`UserGroupID` = `tblsystemusergroups`.`UserGroupID` WHERE `EmpID`='$UserID';");
				
				session_start();
				$_SESSION['fingerprint']=$FingerPrint;
				$_SESSION['fprinttime']=$fPrintTime;
				$_SESSION['user']=$UserID;
				$_SESSION['username']=$UserInfo['EmpFName'];
				$_SESSION['usergroup']=$UserInfo['UserGroupID'];
				$_SESSION['usergcode']=$UserInfo['UserGroupCode'];
				$TimeExpire=time()+60*15;
				setcookie("SESSION_FP", $FingerPrint, $TimeExpire);
				setcookie("SESSION_FT", $fPrintTime, $TimeExpire);
				if(substr($UserID,0,3)=="DTR"){header('Location: dtr.php');}
				else{header('Location: index.php');}
			}
			else{$LoginMsg=$AuthStatus[1];}
		}

?>

		<!DOCTYPE html PUBLIC "-/*W3C/*DTD XHTML 1.0 Transitional/*EN" "http:/*www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http:/*www.w3.org/1999/xhtml">
			<head>
				<meta http-equiv="content-type" content="text/html;charset=utf-8"/>
				<title>PMIS - (Local)</title>
				<link type="text/css" href="css/<?php echo $_SESSION['theme']; ?>/jquery-ui-1.8.15.custom.css" rel="stylesheet"/>
				<link type="text/css" href="css/<?php echo $_SESSION['theme']; ?>/common.css" rel="stylesheet"/>

				<script type="text/javascript" src="js/jquery-1.6.2.min.js"></script>
				<script type="text/javascript" src="js/jquery-ui-1.8.15.custom.min.js"></script>
				<script type="text/javascript" src="js/jscripts.js"></script>
				<script type="text/javascript">
				$(document).ready(function(){
  			$("#UserID,#Password").keyup(function(){
					if(($("#UserID").val()!=="")||($("#Password").val()!=="")){$("#sign_in_reset").button({disabled:false});$("#sign_in_submit").button({disabled:false});}
					else{$("#sign_in_reset").button({disabled:true});$("#sign_in_submit").button({disabled:true});}
					});
					$("#sign_in_reset").click(function(){document.getElementById('form_login').reset();});
					$("#sign_in_submit").click(function(){$("#form_login").submit();return false;});
				});
				</script>
			</head>

			<body>
			<div id="main_box" style="height:650px;display:block;" class="ui-dialog-content ui-widget-content ui-corner-all">
				<div id="win_sign_in" title="Sign in" style="width:auto;height:auto;padding:5px 5px 0px 5px;" class="windows ui-dialog ui-widget ui-widget-content ui-corner-all">
					<form id="form_login" action="login.php" method="post">
					<div style="background:#E6EFFF;width:auto;margin-bottom:4px;" id="user_div" class="ui-widget ui-widget-content ui-corner-all">
						<table style="border-spacing:0px;border:0px solid #6D84B4;width:225px;">
							<tr>
								<td class="form_label" style="border-left:0px solid #6D84B4;vertical-align:middle;width:75px"><label>USER ID:</label></td>
								<td style="padding:5px 3px 5px 3px;"><input name="UserID" id="UserID" class="text_input" style="width:150px" type="text" value="<?php echo $UserID; ?>"/></td>
							</tr>
							<tr>
								<td class="form_label" style="border-left:0px solid #6D84B4;vertical-align:middle;width:65px"><label>PASSWORD:</label></td>	
								<td style="padding:0px 3px 5px 3px;"><input name="Password" id="Password" class="text_input" style="width:150px" type="password" value="<?php echo $Password; ?>"/></td>
							</tr>
						</table>
					</div>
						<table style="border-spacing:0px;border:0px solid #6D84B4;width:230px;">
							<tr>
								<td style="text-align:left;padding:0px 5px 5px 0px;"><button id="sign_in_help">Help</button></td>
								<td style="text-align:right;padding:0px 0px 5px 3px;">
									<button id="sign_in_reset">Reset</button>
									<button id="sign_in_submit">Sign in</button>
								</td>
							</tr>
						</table>
					</form>
					
				</div>
			</div>
			<div id="d_message" title="PMIS Message"></div>
			<script type="text/javascript">
				$(document).ready(function(){
					
					$("#d_message").dialog({modal:true,autoOpen:false,resizable:false,width:375,buttons:{"OK":function(){$(this).dialog("close");}}});
					$("#win_sign_in").position({my:"center",at:"center",collision:"fit",of:"#main_box"});
					$("#sign_in_help").button({icons:{primary:'ui-icon-help'}});
					$("#sign_in_reset").button({disabled:true,icons:{primary:'ui-icon-arrowreturnthick-1-w'}});
					$("#sign_in_submit").button({disabled:true,icons:{primary:'ui-icon-key'}});
					if(($("#UserID").val()!=="")||($("#Password").val()!=="")){$("#sign_in_reset").button({disabled:false});$("#sign_in_submit").button({disabled:false});}
					else{$("#sign_in_reset").button({disabled:true});$("#sign_in_submit").button({disabled:true});}
					$("#UserID").focus();
				
				});
				<?php if($Authenticate==true){echo "showMessage(\"$LoginMsg\");";} ?>
			</script>
			</body>
		</html>

<?php
	}
?>