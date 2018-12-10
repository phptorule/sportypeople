@extends('layouts.app')

@section('content')
	
	<div id="modalInvintationSend" data-backdrop="static" class="modal fade modal-profile invite_details" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<a href="javascript: void(0);" onclick="$('#modalSearchResult').modal('show');" class="text-back pull-left" data-dismiss="modal" >
						<i class="fa fa-angle-left"></i>
					</a>
                	<h4 class="modal-title text-uppercase pull-left" id="loginFormPopupTitle">
						@lang('_.send invitation')
					</h4>
				</div>
				<div class="modal-body pdg-30 del-botttom-pdg">
					<div class="row del-mrg md-pdg-bottom-30">
						<div class="col-md-12 del-pdg">
							<div class="form-group del-mrg del-bottom-mrg">
								<h5 class="del-top-mrg">
									<b>
										@lang('_.User details')
									</b>
								</h5>
								<div class="media">
									<div class="media-left">
										<a href="javascript:void(0);" onclick="show_img()">
											<img class="events-imgs pull-left img-circle img-invite img-invitation" src="" alt="@lang('_.User face')" />
										</a>
									</div>
									<div class="media-body media-user-info">
									</div>
								</div>
							</div>
							<div id="nav_btn_invite"  class="form-group invitation-nav clearfix del-bottom-mrg send-msg-block">
								<div class="col-xs-12">
									<h5 class='del-top-mgr'>
										<b>
											@lang('_.Message')
										</b>
									</h5>
								</div>
								<div class="col-xs-9 del-left-pdg">
									<input class="form-control input-invitation " id="message" placeholder="@lang('_.Say a few words')"  />
								</div>
								<div class="col-xs-3 pdg-left-10">
									<button class="btn btn btn-default btn-sent btn-invite text-uppercase btn-invitation" onclick="send()" >@lang('_.send')</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="modalSearchResult" data-backdrop="static" class="modal fade modal-profile" role="dialog">
		<div class="modal-dialog ">
			<div class="modal-content">
				<div class="modal-header">
					<a href="/meeting/create" class="text-back pull-left">
						<i class="fa fa-angle-left"></i>
					</a>
					<h4 class="modal-title text-uppercase pull-left" id="loginFormPopupTitle">
						@lang('_.search results')
					</h4>
				</div>
				<div class="modal-body pdg-30 pdg-15-top del-botttom-pdg">
						<div class="row del-mrg ">
						
							<div class="col-md-12 del-pdg">
								<div class="form-group clearfix ">
									<b class="pull-left mrg-top refine-serach">
										@lang('_.Refine search')
									</b>
									<div class="col-xs-4 col-sm-3 pdg-left-del">
										<div class="wrapper-select">
											<span class="wrapper-header">Gender</span>
											<div class="mjs-select">
												<input type="hidden" value="all" data-cellback="filter_gender" id="gender" class="mjs-value" />
												<div class="mjs-option selected" data-option='all'>
													@lang('_.All')
												</div>
												<div class="mjs-option" data-option='F'>
													@lang('_.Female')
												</div>
												<div class="mjs-option" data-option='M'>
													@lang('_.Male')
												</div>
											</div> 
										</div>
									</div>
									
									<div class="col-xs-3 col-sm-3 pdg-left-del del-right-pdg">
										<div class="wrapper-select">
											<span class="wrapper-header">@lang('_.Age')</span>
											<div class="mjs-select max-height-140">
												<input type="hidden" value="all" data-cellback="filter_age" id="age" class="mjs-value"/>
												<div class="mjs-option selected" data-option="all">
													@lang('_.All')
												</div>
												@foreach ($range_age as $row)
													<div class="mjs-option" data-option="{{ $row[0] }}-{{ $row[1] }}">
														{{ $row[0] }}  -  {{ $row[1] }}
													</div>
												@endforeach
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row md-pdg-bottom-30 md-pdg-top-10">
							<div class="col-md-12">
								<div class="list_result_user">
									@foreach ($list as $user)
										<div class="form-group {{ $user == end($list) ? 'del-bottom-mrg' : '' }}">
											<div class="media">
												<div class="media-left">
													<a href="javascript:void(0);" onclick='save_invite({{ $user->id }}, {{ $meeting_id }})'>
														<!--img class="events-imgs pull-left img-circle img-invite"  src="/{{ ! empty( $user->file ) ? $user->file : ( ! empty($user->gender) ? ( $user->gender == 'M' ? 'images/man.jpg' : ($user->gender == 'F' ? 'images/woman.png' : '')) : '') }}" /-->
														<img class="events-imgs pull-left img-circle img-invite"  src="/{{ ! empty( $user->file ) ? $user->file : 'images/def_avatar.png' }}" />
													</a>
												</div>
												<div class="media-body">
													 <a style="text-decoration: none;" href="javascript:void(0);" onclick='save_invite({{ $user->id }}, {{ $meeting_id }})'>
													 <h5 class="media-heading">{{ $user->first_name }} {{ $user->last_name }}</h5>
													 <small class="text-muted {{ $user == end($list) ? 'del-bottom-mrg' : '' }}">@lang('_.Age'): {{ $user->age }}, {{ $user->name_addr }}<br /> </small>
													 </a>
												</div>
												<div class="media-right">
													<button  class="btn btn-default btn-sent ft-200 <?php echo ( ! $user->invite ) ? "btn-invite" : ""; ?>" <?php echo ( ! $user->invite ) ? "data-dismiss='modal'" : ""; ?> onclick='save_invite({{ $user->id }}, {{ $meeting_id }})' >{{ ! $user->invite ? 'Invite' : 'Sent' }}</button>
												</div>
											</div>
										</div>
										
									@endforeach
									@if ($list)
										<div class="media">
											<div class="media-left">
												<a href="javascript:void(0);">
													<img class="events-imgs pull-left img-circle img-invite" src="{{ url('images/share.jpg') }}"/>
												</a>
											</div>
											<div class="media-body">
												<h5>Share your experience</h5>
												<small class="text-muted">Share your experience Expand your network - Find new training partn...</small>
											</div>
											<div class="media-right">
												<button class="btn btn-default btn-sent ft-200 btn-invite" onclick="fb_share('https://www.facebook.com/sharer/sharer.php?u={{ url('/') }}&amp;src=sdkpreparse')">Share</button>
											</div>
										</div>
									@endif
									@if ( ! $list)
										<div class="form-group text-center">
											<h4><strong>@lang('_.Congrats!')</strong></h4>
											<p>@lang('_.You are one of the first out! Make sure you will not see this screen again and share our free app without adds...please:&#41; It will work great on your timeline')!</p>
											<div class="panel panel-default">
												<div class="panel-heading text-left sporty-header">
													&nbsp;
												</div>
												<div class="panel-body text-left">
													<div class="media">
														<div class="media-left">
															<a href="javascript:void(0);">
																<img class="events-imgs pull-left " src="{{ url('images/share.jpg') }}"/>
															</a>
														</div>
														<div class="media-body text-left">
															<h5>Sportypeople</h5>
															<small class="text-muted">Sponsored</small>
														</div>
														<div class="media-right">
															<button class="btn btn-default btn-sent ft-200 btn-invite share" onclick="fb_share('https://www.facebook.com/sharer/sharer.php?u={{ url('/') }}&amp;src=sdkpreparse')">Share</button>
														</div>
													</div>
													<small>@lang('_.Meet sporty people with our FREE app. You can not make it easier.')</small>
													<div class="text-center">
														<img src="{{ url('images/share-img.jpg') }}" />
													</div>
													<h4>@lang('_.Meet new sporty people with our FREE app!')</h4>
													<small>@lang('_.This is how it works: Select a sport, date, gender, age and find people in your area who would love to join you. Click on a person to see their profile and send them a message to invite them. It is really that simple!')</small>
													<div class="text-right">
														<button class="btn btn-default btn-sent ft-200 btn-invite share">Install</button>
													</div>
												</div>
											</div>
										</div>
									@endif
									
								</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="modal_face">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title" id="user_name"></h4>
				</div>
				<div class="modal-body text-center" >
					<img src="" alt="@lang('_.Big face')"  class="img-responsive img-face-user" id="big_face" />
				</div>
			</div>
		</div>
	</div>

@endsection
@push('app-scripts')
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-filestyle/1.2.1/bootstrap-filestyle.js"></script>
	<script>
		{!! "var _list = " . json_encode($list) . ";" !!}
		{!! "var _meeting = " . json_encode($meeting) . ";" !!}
		{!! "var _meeting_id_ = " . $meeting_id . ";" !!}
	
		$(document).ready(function(){
			$("#modalSearchResult").modal('show');
			
			$('#modalInvintationSend').on('hidden.bs.modal', function (e) {
			})

			$(".nicescroll").niceScroll({'cursorcolor':"#b0afae", 'railpadding' : {'bottom' : 3, 'top' : 3, 'left' : 5}});
		});

		function fb_share(url)
		{
			window.open(url, "", "width=600,height=500");
		}
		
		function filter_gender(data)
		{
			$.ajax({
					'url' : '/search',
					'data' : {
								'_token' : $("meta[name='csrf-token']").attr('content'),
								'meeting_id' : segment(2),
								'gender' :  data,
								'age' :  $.trim($("#age").val())
							},
					'type' : 'post',
					'complete' : function(data)
					{
						print(data.responseJSON);
					}	
				});
		}
		
		function filter_age(data)
		{
			$.ajax({
					'url' : '/search',
					'data' : {
								'_token' : $("meta[name='csrf-token']").attr('content'),
								'meeting_id' : segment(2),
								'age' : data,
								'gender' : $.trim($("#gender").val())
							},
					'type' : 'post',
					'complete' : function(data)
					{
						print(data.responseJSON);
					}	
			});
		}
		
		var meeting_id = null;
		var user_id = null;
		var user_name = null;
		var face_user = null;

		function save_invite(_user_id, _meeting_id)
		{
			$("#modalSearchResult").modal('hide');
			
			var user = _list[_user_id];
			if (user)
			{
				meeting_id = _meeting_id;
				user_id = _user_id;
				console.log(meeting_id, user_id);
				
				user_name = user.first_name + ' ' + user.last_name;
				
				
				src = "/";
				if (user.file)
				{
					src += user.file;
				}
                else
                {
                    src += 'images/def_avatar.png';
                }

				face_user = src;

				$(".img-invitation").attr('src',  src);
				$(".media-user-info").empty();
				$(".after").empty();
				
				var context = "";
				context += "<h5>" + user.first_name + " " + user.last_name + "</h5>";
				context += "<h5>Age: " + user.age + "</h5>";
				context += "<h5 class='del-bottom-mrg'>@lang('_.About'): " + user.about_me + "</h5>";
				
				after = "<div class='after'>";
				after += "<div class='sep_20_info'></div>";
				
				after += "<div class='location_details'>";
				after += "<h5 class='del-top-mgr'><b>@lang('_.Location details')</b></h5>";
				after += "<p class='del-bottom-mrg'>" + user.full_address + "</p>";
				after += "</div>";
				
				
				after += "<div class='sep_20_info'></div>";
				
				after += "<div class='date'>";
				after += "<h5 class='del-top-mgr'><b>@lang('_.Date')</b></h5>";
				after += "<p class='del-bottom-mrg'>" + _meeting.meeting_date + "</p>";
				after += "</div>";
				
				after += "<div class='sep_20_info'></div></div>";
							
				$(".media-user-info").append(context).after(after);
				
				$("#nav_btn_invite").css("display", "block");
					
				for(var i in _list)
				{
					if (_list[i].invite && i == _user_id)
					{
						$("#nav_btn_invite").css("display", "none");
					}
				}

			}
			
			$("#modalInvintationSend").modal("show");
		}
		
		function show_img()
		{
			$("#user_name").text(user_name);
			$("#big_face").attr("src", face_user.replace("min_", ""));

			$("#modal_face").modal("show");
			$("#modalInvintationSend").modal('hide');
			$('#modal_face').on('hidden.bs.modal', function (e) {
		 		$("#modalInvintationSend").modal('show');
			});
		}

		function send()
		{
			$.ajax({
					'url' : '/save_invite',
					'data' : {
								'_token' : $("meta[name='csrf-token']").attr('content'),
								'user_id' : user_id,
								'meeting_id' : meeting_id,
								'message' : $("#message").val()
							},
					'type' : 'post',
					'complete' : function(data)
					{
						showNotification("@lang('_.Your invite has been send. You will be redirected in a few seconds')...");
						setTimeout(function(){
							window.location.reload();
						}, 2000);
						
					}	
				});
		}
		
		function print(data)
		{
			$(".list_result_user").empty();
			var content = "";
			for(var i in data.list)
			{
				user = data.list[i];
				content += "<div class='media'>";
				
				content += "<div class='media-left'>";
				
				src = "/";
				if (user.file)
				{
					src += user.file;
				}
                else
                {
                    src += 'images/def_avatar.png';
                }
                /*
				else if (user.gender)
				{
					if(user.gender == 'M')
					{
						src += 'images/man.jpg';
					}
					else if (user.gender == 'F')
					{
						src += 'images/woman.png';
					}
				}
                */
				
				content += "<img class='events-imgs pull-left img-circle img-invite' src='" + src + "' alt='User face' />";
				content += "</div>";
				
				content += "<div class='media-body'>";
				content += "<h6 class='media-heading'>" + user.first_name + " " + user.first_name +  "</h6>";
				content += "<p class='text-muted'>" + "Age: " + user.age + ", " + user.name_addr + "</p>";
				content += "</div>";
				
				content += "<div class='media-right'>";
				content += "<button onclick='save_invite(" + user.id + ", " + _meeting_id_ + ")' class='btn btn-default btn-sent ft-200 pdg-lr-20 btn-invite' >Invite</button>";
				content += "</div>";
				
				content += "</div>";
			}
			
			if ( ! content)
			{
				content += "<p class='wrapper-empty'>There are no results!</p>";
			}
			
			$(".list_result_user").append(content);
		}
		
		function segment(index)
		{
			var segmetns = window.location.href.split('/');
			for(var i in [0, 1, 2])
			{
				segmetns.shift();
			}
			return segmetns[index];
		}
	</script>
@endpush