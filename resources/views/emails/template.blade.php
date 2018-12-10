<!doctype html>
<html>
	<head>
		<meta charset="utf8" />
	</head>
	<body>
		<table cellspacing="0" cellpadding="0" width="100%">
			<tbody>
				<tr>
					<td style="color" align="center">
						<table cellspacing="0" cellpadding="0" width="100%">
							<tbody>
								<tr>
									<td style="padding:25px 0;text-align:center">
										<a style="font-family:Arial,'Helvetica Neue',Helvetica,sans-serif;font-size:16px;font-weight:bold;color:#2f3133;text-decoration:none" target="_black"  href="{{ URL::to('/') }}">
											Sporty People
										</a>
									</td>
								</tr>
								<tr>
									<td style="width:100%;margin:0;padding:0;border-top:1px solid #edeff2;border-bottom:1px solid #edeff2;background-color:#fff" width="100%">
										<table style="width:auto;max-width:570px;margin:0 auto;padding:0" cellspacing="0" cellpadding="0" align="center" width="570">
											<tbody>
												<tr>
													@yield('content')
												</tr>
											</tbody>
										</table>	
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
	</body>
</html>