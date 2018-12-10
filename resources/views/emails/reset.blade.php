@extends('emails.template')
@section('content')
		<td style="font-family:Arial,'Helvetica Neue',Helvetica,sans-serif;padding:35px">
		<h1 style="margin-top:0;color:#2f3133;font-size:19px;font-weight:bold;text-align:left">
			Hello {{ @$first_name }}!
		</h1>
		<p style="margin-top:0;color:#74787e;font-size:16px;line-height:1.5em">
			You are receiving this email because we received a password reset request for your account.
		</p>
		<table>
			<tbody>
				<tr>
					<td>
							<strong>New password:</strong> {{ @$new_pass }}
					</td>
				</tr>
			</tbody>
		</table>
		<p style="margin-top:0;color:#74787e;font-size:16px;line-height:1.5em">
			Regards,<br>Sporty People
		</p>
	</td>
@stop