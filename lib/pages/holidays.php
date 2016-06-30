						<div>
							<table class="" style="width:520px;border-spacing:0px;border:1px solid #6D84B4;" cellspacing="0">
								<tr>
									<td class="i_table_header_1st" style="padding:0px 3px 0px 3px;" width="50px">Date</td>
									<td class="i_table_header" style="padding:0px 3px 0px 3px;" width="100px">Holiday Type</td>
									<td class="i_table_header" style="padding:0px 3px 0px 3px;" width="50px">Non<br>Working</td>
									<td class="i_table_header" style="padding:0px 3px 0px 3px;" width="50px">Paid</td>
									<td class="i_table_header">Description</td>
								</tr>
							</table>
							<div style="height:200px;width:517px;border:1px dotted #6D84B4;padding:0px;overflow-x:hidden;overflow-y:scroll;">
								<table style="width:501px;" cellspacing="0">
								<?php
								$result=$MySQLi->sqlQuery("SELECT DATE_FORMAT(`HoliDate`, '%b %d') AS HoliDate, `HoliType`, `HoliIsNonWorking`, `HoliIsPaid`, `HoliDescription`, DATE_FORMAT(`HoliDate`, '%m%d') AS `hDate` FROM `tblholidays` WHERE DATE_FORMAT(`HoliDate`, '%Y') = '1970' OR DATE_FORMAT(`HoliDate`, '%Y') = '2014' ORDER BY `hDate` ASC;"); //".date('Y')."
								$n=1;
								while($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
									echo "<tr class='i_table_row_".(($n%2==0)?"0":"1")."'>";
									echo "<td class='' style='padding:0px 3px 0px 3px;width:51px;text-align:center;' valign='center'>".strtoupper($records[0])."</td>";
									echo "<td class='' style='padding:0px 3px 0px 3px;width:101px;text-align:center;' valign='center'>".$records[1]."</td>";
									echo "<td class='' style='padding:0px 3px 0px 3px;width:51px;text-align:center;' valign='center'>".$records[2]."</td>";
									echo "<td class='' style='padding:0px 3px 0px 3px;width:51px;text-align:center;' valign='center'>".$records[3]."</td>";
									echo "<td class='' style='padding:0px 3px 0px 3px;' valign='center'>".$records[4]."</td>";
									if(4==4){
										echo "<td style='width:20px;text-align:center;padding:2px 0px 1px 3px;' valign='center'><ul class='ui-widget ui-helper-clearfix ul-icons'><li id=''class='ui-state-default ui-corner-all' title='Edit' onClick=''><span class='ui-icon ui-icon-pencil'></span></li></ul></td>";
										echo "<td style='width:20px;text-align:center;padding:2px 0px 1px 0px;' valign='center'><ul class='ui-widget ui-helper-clearfix ul-icons'><li id=''class='ui-state-default ui-corner-all' title='Delete' onClick=''><span class='ui-icon ui-icon-trash'></span></li></ul></td>";
									}
									else{
										echo "<td style='width:20px;text-align:center;padding:2px 3px 1px 0px;' valign='center'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-disabled ui-corner-all' title='Edit' onClick=''><span class='ui-icon ui-icon-pencil'></span></li></ul></td>";
										echo "<td style='width:20px;text-align:center;padding:2px 3px 1px 0px;' valign='center'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-disabled ui-corner-all' title='Delete' onClick=''><span class='ui-icon ui-icon-trash'></span></li></ul></td>";
									}
									echo "</tr>";
									$n++;
								}
								?>
								</table>
							</div>
							<div class="ui-widget ui-widget-content ui-corner-all" style="height:150px;width:517px;">
							
							</div>
						</div>