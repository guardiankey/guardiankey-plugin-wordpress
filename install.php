<?php

$mailtexts = new \stdClass;
$mailtexts->bodymail = '<div style="background-color:#f9f9f9; height:100%; margin-bottom:0px; margin-left:0px; margin-right:0px; margin-top:0px; padding:0; width:100%">
<table align="center" border="0" cellpadding="0" cellspacing="0" style="background-color:#f9f9f9; border-collapse:collapse; height:100%; margin:0; padding:0; width:100%">
	<tbody>
		<tr>
			<td style="height:100%; vertical-align:top; width:100%">
			<table border="1" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-collapse:collapse; border:1px solid #d9d9d9; width:600px">
				<tbody>
					<tr>
						<td style="vertical-align:top">
						<table align="center" border="0" cellpadding="0" cellspacing="0" style="border-collapse:collapse; width:100%">
							<tbody>
								<tr>
									<td style="background-color:#691b1b">
									<div style="color:white; font-size:35px">
									<h2 style="text-align:center">Security Alert!</h2>
									</div>
									</td>
								</tr>
							</tbody>
						</table>
						</td>
					</tr>
					<tr>
						<td style="background-color:#ffffff">
						<div style="color:black; font-family:Helvetica,Arial,sans-serif; line-height:160%; padding-bottom:32px; text-align:center">
						<p>We recently detected a new access to your account at YOUR_SYSTEM_URL.</p>

						<table border="0" style="width:100%">
							<tbody>
								<tr>
									<td style="text-align:right">Username:</td>
									<td style="text-align:left">&nbsp;[USERNAME]</td>
								</tr>
								<tr>
									<td style="text-align:right">Date/time:</td>
									<td style="text-align:left">&nbsp;[DATETIME]</td>
								</tr>
								<tr>
									<td style="text-align:right">System:</td>
									<td style="text-align:left">&nbsp;[SYSTEM]</td>
								</tr>
								<tr>
									<td style="text-align:right">Location:</td>
									<td style="text-align:left">&nbsp;[LOCATION]</td>
								</tr>
								<tr>
									<td style="text-align:right">IP address:</td>
									<td style="text-align:left">&nbsp;[IPADDRESS]</td>
								</tr>
							</tbody>
						</table>

						<p>Please, open our access verification page below to confirm or not if it was you! Our system learns with your responses to provide an intelligent security.</p>
						<strong>Access verification page:</strong> <a href="[CHECKURL]" target="_blank">[CHECKURL]</a></div>
						</td>
					</tr>
				</tbody>
			</table>

			<table cellspacing="0" style="border-collapse:collapse; width:600px">
				<tbody>
					<tr>
						<td style="background-color:#f7f7f7; text-align:center">&nbsp;
						<p style="margin-left:0px; margin-right:0px">GuardianKey</p>
						</td>
					</tr>
				</tbody>
			</table>
			</td>
		</tr>
	</tbody>
</table>
</div>';

$mailtexts->subjectmail = 'Alert Security - Access in YOUR_SYSTEM_URL';
