<!DOCTYPE html>
<html lang="en" >
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<meta name="csrf-token" content="{{ csrf_token() }}">
		<title>@lang('_.SportyPeople')</title>
		
		<meta property="og:url"           content="{{ url('/') }}" />
		<meta property="og:type"          content="{{ env('APP_NAME') }}" />
		<meta property="og:title"         content="@lang('_.fbShareTitle')" />
		<meta property="og:description"   content="@lang('_.fbShareDescrition')" />
		<meta property="og:image"         content="{{ url('/images/share-img.jpg') }}" />

		<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" />
		<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet /">	
		<link href="https://fonts.googleapis.com/css?family=Open+Sans+Condensed:300" rel="stylesheet" /> 
		<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700" rel="stylesheet" />
		<link href="/css/all.css" rel="stylesheet" />
		<link href="/css/md.css" rel="stylesheet" />

		<script>
			window.Laravel = <?php echo json_encode([
				'csrfToken' => csrf_token(),
			]); ?>
		</script>
	</head>
	<body>
		<div id="app">
			<nav class="navbar navbar-default navbar-static-top">
				<div class="container">
					<div class="navbar-header">

						<!-- Branding Image -->
						<a class="navbar-brand" href="{{ url('/meeting/create') }}">
							<span class="word-1">SPORTY</span><span class="word-2">PEOPLE</b>
						</a>
					</div>

					<div class="_collapse _navbar-collapse" __id="app-navbar-collapse">
						<!-- Left Side Of Navbar -->
						<!-- <ul class="nav navbar-nav">
							&nbsp;
						</ul> -->

						<!-- Right Side Of Navbar -->
						<ul class="nav navbar-nav navbar-right">
							@if(! Auth::guest())
							<li class="hidden-md hidden-lg">
								<a href="{{ url('/meeting/create') }}">
									<i class="glyphicon glyphicon-search"> </i>
								</a>
							</li>
							@else
							<li class="visible-lg visible-md" data-target="#more_info_modal" data-toggle="modal">	
								<a href="javascript:void(0);">
									@lang('_.More info')
								</a>
							</li>
							@endif
							
							<li class="dropdown lang-dropdown-parent <?=(Auth::guest() ? '' : ' hidden-sm hidden-xs ')?>">
								<a href="#" class="lang-btn dropdown-toggle hidden-sm hidden-xs" data-toggle="dropdown" role="button" aria-expanded="false">{{App::getLocale()}}</a>
								<ul class="dropdown-menu lang-menu" role="menu">
									<li>
																	@foreach(config('app.locales') as $key=>$name)
																		<a href="{{url('/locale/'.$key)}}" class="lng-item" >{{$name}}</a>
																	@endforeach
									</li>
								</ul>
							</li>
							@if(Auth::user())
								<li class="hidden-sm hidden-xs count-bell">
									<a href="/meeting/history" id="first-menu-icon">
										<i class="glyphicon glyphicon-bell"> </i>
										
										<span id="bell"></span>
									</a>
								</li>
								<li class="hidden-sm hidden-xs">
									<a href="{{ url('/meeting/create') }}">
										<i class="glyphicon glyphicon-search"> </i>
									</a>
								</li>
							@endif
							<li class="dropdown">
								<a href="#" class="dropdown-toggle <?=Auth::guest() ? ' hidden-md hidden-lg ' : ''?>" data-toggle="dropdown" role="button" aria-expanded="false">
									<i class="glyphicon glyphicon-menu-hamburger"> </i>
								</a>

								<ul class="dropdown-menu user-menu" role="menu">
									@if( ! Auth::guest())
										<?php $user = Auth::user(); ?>
										<li class="hidden-lg hidden-md">
											<a href="javascript: void(0);" class="clearfix user-profile">
												<!--img  class="pull-left img-circle" src="/{{ ! empty( $user->file ) ? $user->file : ( ! empty($user->gender) ? ( $user->gender == 'M' ? 'images/man.jpg' : ($user->gender == 'F' ? 'images/woman.png' : '')) : '') }}"-->
												<img  class="pull-left img-circle" src="/{{ ! empty( $user->file ) ? $user->file : 'images/def_avatar.png' }}">
												<div class="user-name text-left pull-left">
													{{ Auth::user()->first_name }}
													<br />
													{{ strtoupper(Auth::user()->last_name) }}											
												</div>
											</a>
										</li>
										<li>
											<a href="{{ url('/meeting/create') }}">
												<i class="glyphicon glyphicon-search"> </i>
												@lang('_.Search')
											</a>
										</li>
										<li>
											<a href="{{ url('/profile') }}">
												<i class="glyphicon glyphicon-user"> </i>
												@lang('_.Profile')
											</a>
										</li>
										<li>
											<a href="{{ url('/meeting/history')}}">
												<i class="glyphicon glyphicon-time"> </i>
												@lang('_.Dates')
												@if(Auth::user())
													<span id="mobile_bell"></span>
												@endif
											</a>
										</li>
										<li>
											<a href="javascript:;"
												onclick="logout()">
												<i class="glyphicon glyphicon-remove-circle"> </i>
												@lang('_.Logout')
											</a>
										</li>
									@endif

									<li class="lang-mobile hidden-lg hidden-md">
										@if (!Auth::check())
										<a href="javascript:void(0);" data-target="#more_info_modal" data-toggle="modal">
											@lang('_.More info')
										</a>
										@endif
										<a href="#" onclick="event.stopPropagation(); $('#mobile-lang-menu').slideToggle(); return false;" data-toggle="collapse" data-target="#mobile-lang-menu" >
											<span class="current-lang lang-btn">{{App::getLocale()}}</span>
											@lang('_.Language')
										</a>
										<ul id="mobile-lang-menu" class="dropdown-menu">
											@foreach(config('app.locales') as $key=>$name)
												<li><a href="{{url('/locale/'.$key)}}">{{$name}}</a></li>
											@endforeach
										</ul>
									</li>
									<li class="more-mobile hidden-lg hidden-md" data-target="#more_info_modal" data-toggle="modal">	
						
									</li>
										<form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
											{{ csrf_field() }}
										</form>
									</li>
								</ul>
							</li>
						</ul>
					</div>
				</div>
			</nav>

			<section id="home-slider">
				<div class="container">
					<div class="row">
						<div class="col-md-6 col-md-offset-6">
							@if (Auth::guest())
									<a class="btn btn-lg btn-default" data-toggle="modal" data-target="#modalLogin">
										@lang('_.LOGIN')
									</a>
									
									<a class="btn btn-lg btn-warning" data-toggle="modal" data-target="#modalRegister">
										@lang('_.register')
									</a>
								<p class="text-uppercase small-text">@lang('_.100% free of charge')</p>
							@endif
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<h1>@lang('_.MEET PEOPLE AND BOOST YOUR EXCERCISE MOTIVATION')</h1>
						</div>
					</div>
				</div>

			</section>

			@yield('content')
		</div>

		<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.nicescroll/3.6.8-fix/jquery.nicescroll.min.js"></script>
		<script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js"></script>
		<script src="/js/all.js"></script>
		
		@stack("app-scripts")
		<script>
			var oneSignal = function(){
				var oneSignalHendler = function(){
					var OneSignal = window.OneSignal || [];
					OneSignal.init({
						appId : "{{ config('onesignal.app_id') }}",
						autoRegister : false,
						notifyButton : {
							enable : true
						}
					});
				
					OneSignal.on('subscriptionChange', function (isSubscribed) {
						if (isSubscribed)
						{
							OneSignal.push(function() {
								OneSignal.getUserId(function(userId) {
									$.ajax({
										url : "{{ url('/subscriptionOneSignal') }}",
										method : "post",
										data : {
											_token : Laravel.csrfToken,
											onesignal : userId
										}
									});
								});
								
							});
						}
						else 
						{
							OneSignal.push(function() {
								OneSignal.getUserId(function(userId) {
									$.ajax({
										url : "{{ url('/unsubscriptionOneSignal') }}",
										method : "post",
										data : {
											_token : Laravel.csrfToken,
											onesignal : userId
										}
									});
								});
							});

							OneSignal.push(function() {
								@if (Auth::check())
									OneSignal.deleteTag("{{ Auth::user()->id }}");
								@endif
							});
						}
					});

				}

				return {
					init : function(){
						oneSignalHendler();
					}
				}
			}();

			$(document).ready(function(){
				@if(Auth::check())
					oneSignal.init();
				@endif
				
				watchInvite();
				setInterval(function(){
					watchInvite();
				}, 3000);

			});

			function watchInvite(meeting_id, users_id)
			{
				if (/\/view/i.test(window.location.pathname))
				{
					return false;
				}
				
				@if(Auth::check())
					$.ajax({
						url: '/watch_invite',
						type: 'POST',
						data: {
							'_token': $('meta[name="csrf-token"]').attr('content'),
						},
						dataType: 'JSON',
						success: function (data) {
							if(data.invites || data.messages || data.statuses.length)
							{
								$("#bell").addClass("bell");
						$("#mobile_bell").addClass("mobile_bell");

						$("#bell").text(data.invites * 1 + data.messages * 1 + data.statuses.length * 1);
								$("#mobile_bell").text(data.invites * 1 + data.messages * 1 + data.statuses.length * 1);
							}
						}
					});
				@endif
			}

			function logout()
			{
				var OneSignal = window.OneSignal || [];
				OneSignal.setSubscription(false);
				$.ajax({
					method : "post",
					url : "{{ url('logout') }}",
					data : {
						_token : Laravel.csrfToken
					},
					success : function(){
						setTimeout(function(){
							window.location.href = "/";
						}, 1000);
					}
				});
			}
		</script>
	</body>
</html>
