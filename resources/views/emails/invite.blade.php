@extends('emails.template')
@section('content')
	<td style="font-family:Arial,'Helvetica Neue',Helvetica,sans-serif;padding:35px">
		<h1 style="margin-top:0;color:#2f3133;font-size:19px;font-weight:bold;text-align:left">
			Hello {{ @$first_name }}!
		</h1>
		<p style="margin-top:0;color:#74787e;font-size:16px;line-height:1.5em">
			You are receiving this email because you signup at <strong><a style="color: #e74c00; text-decoration: none; " href="{{ URL::to('/') }}">Sporty People</a></strong>.
		</p>


		<p>Meeting info</p>
		<ul>		
			<li>Date {{  @$meeting->meeting_date }}</li>
			<li>Address {{ @$meeting->full_address }}</li>
		</ul>
		<p style="margin-top:0;color:#74787e;font-size:16px;line-height:1.5em">
			Regards,<br>Sporty People
		</p>
	</td>
@stop