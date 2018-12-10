@extends('layouts.app')
@section('content')
	<div id="modalView" data-backdrop="static" class="modal fade modal-profile invite_details" role="dialog">
		<div class="modal-dialog modal-profile">
			<div class="modal-content">
				<div class="modal-header">
					<a href="/meeting/history" class="text-back pull-left">
						<i class="fa fa-angle-left"></i>
					</a>
					<h4 class="modal-title text-uppercase pull-left" id="loginFormPopupTitle">
						@lang('_.invitation details') 
					</h4>
				</div>
				
				<div class="modal-body pdg-30 del-botttom-pdg pdg-top-20">
					<div class="row del-mrg md-pdg-bottom-30">
				
				<!--<div class="modal-body pdg-30">-->
					@if ( ! empty($info['messages']))
					<div class="row del-mrg">
						<div class="col-md-12 del-pdg">
							<h5 class="md-hidden-top-pdg del-mrg-all">
								<b>
									@lang('_.Message') 
								</b>
							</h5>
							<div class="text-center">
								<p class="gray_line" id="load_more">
									@lang('_.load more')
								</p>
							</div>
						</div>
					</div>
					@endif
					<!--<div class="row">-->
						<div class="col-sm-12 msg-list">
							@foreach($info['messages'] as $msg)
								@if ($msg['user']->id == Auth::user()->id)
									<div class="form-group">
										<div class="media">
											<div class="media-left vertical-top">
												<!--img class="pull-left img-circle img-msg"  src="/{{ ! empty( $msg['user']->file ) ? $msg['user']->file : ( ! empty($msg['user']->gender) ? ( $msg['user']->gender == 'M' ? 'images/man.jpg' : ($msg['user']->gender == 'F' ? 'images/woman.png' : '')) : '') }}" alt="face user"/-->
												<img class="pull-left img-circle img-msg"  src="/{{ ! empty( $msg['user']->file ) ? $msg['user']->file : 'images/def_avatar.png' }}" alt="face user"/>
											</div>
											<div class="media-body text-left">
												
												<p class="msg_body">
													@foreach(explode('</br>', $msg['message_body']) as $row)
														{{ $row }}
														</br>
													@endforeach
													
												</p>
											</div>
										</div>
									</div>			
								@else

									<div class="form-group">
										<div class="media">
											<div class="media-body text-right">
												<p class="msg_body">
													@foreach(explode('</br>', $msg['message_body']) as $row)
														{{ $row }}
														</br>
													@endforeach
													
												</p>
											</div>
											<div class="media-right vertical-top">
												<!--img class="pull-left img-circle img-msg "  src="/{{ ! empty( $msg['user']->file ) ? $msg['user']->file : ( ! empty($msg['user']->gender) ? ( $msg['user']->gender == 'M' ? 'images/man.jpg' : ($msg['user']->gender == 'F' ? 'images/woman.png' : '')) : '') }}" alt="face user"/-->
												<img class="pull-left img-circle img-msg "  src="/{{ ! empty( $msg['user']->file ) ? $msg['user']->file : 'images/def_avatar.png' }}" alt="face user"/>
											</div>
										</div>
									</div>			
								@endif
											
							@endforeach	
						</div>
					</div>
					<div class="row send-msg-block">
						<div class="col-xs-9">
							<textarea data-defaultheight="50" style="height: 45px;" class="form-control msg-input" placeholder="@lang('_.Send a replay')"></textarea>
						</div>
						<div class="col-xs-3 pdg-left-del">
							<button class="btn btn-invite btn-send-msg text-uppercase ">@lang('_.send')</button>
						</div>
						<div class="sep_20_info">
						</div>
					</div>
					<div class="row mrg-30 md-mrg-30">
						<div class="col-md-12 invite-info pdg-bottom-10 "> 
							<h5>@lang('_.User details')</h5>
							<div class="media">
								<div class="media-left">
									<!--a href="javascript:void(0);" onclick="show_img_user('{{ ! empty( $info['user']->file ) ? $info['user']->file : ( ! empty($info['user']->gender) ? ( $info['user']->gender == 'M' ? 'images/man.jpg' : ($info['user']->gender == 'F' ? 'images/woman.png' : '')) : '') }}')">
									<img src="/{{ ! empty( $info['user']->file ) ? $info['user']->file : ( ! empty($info['user']->gender) ? ( $info['user']->gender == 'M' ? 'images/man.jpg' : ($info['user']->gender == 'F' ? 'images/woman.png' : '')) : '') }}" class="fixed-img pull-left img-circle img-invite " alt="face" />
									</a-->
                                    <a href="javascript:void(0);" onclick="show_img_user('{{ ! empty( $info['user']->file ) ? $info['user']->file : 'images/def_avatar.png' }}')">
									<img src="/{{ ! empty( $info['user']->file ) ? $info['user']->file : 'images/def_avatar.png' }}" class="fixed-img pull-left img-circle img-invite " alt="face" />
									</a>
								</div>
								<div class="media-body">
									<h5>{{$info['user']->first_name}} {{$info['user']->last_name}}</h5>
									<h5>@lang('_.Age'): {{ $info['user']->age }}</h5>
									<h5 class="del-mrg">@lang('_.About'): {{ $info['user']->about_me }}</h5>
								</div>
							</div>
							
							<div class="sep_20_info">
							</div>
							
							<h5 class="del-top-mgr">@lang('_.Location details')</h5>
								<p class="del-mrg">
									{{$info['meeting']->full_address}}
								</p>
							<div>
							</div>
							
							<div class="sep_20_info">
							</div>
							
							<h5 class="del-top-mgr">@lang('_.Date')</h5>
								<p class="del-mrg">
									{{ $info['meeting']->meeting_date }}
									{{  $info['meeting']->flexible_days ? '(flexible for -/+3 days)' : '' }}
								</p>
							
							<div class="sep_20_info">
							</div>
							
							<h5 class="del-top-mgr">@lang('_.Map')</h5>
							<div id="map">
							</div>
						</div>
						<div class="form-group clearfix del-bottom-mrg">
						</div>
						@if (Auth::user()->id != $info['meeting']->user_id && ($info['invite']->status != 'rejected' && $info['invite']->status != 'accepted'))
							<br />
							<div class="col-xs-6 del-right-pdg right-pdg-5">
								<button class="btn btn-lg text-uppercase btn-reject del-mrg">@lang('_.reject')</button>
							</div>
							<div class="col-xs-6 del-left-pdg left-pdg-5">
								<button class="btn btn-lg  btn-invite text-uppercase btn-accept del-mrg">@lang('_.accept')</button>
							</div>
						@elseif ($info['invite']->status == 'rejected' || $info['invite']->status == 'accepted' )
							<br />
							<div class="row del-mrg del-pdg">
								<div class="col-sm-12 invite-info">
									<div class="alert ft-bold del-mrg-all text-uppercase text-center {{ $info['invite']->status == 'rejected' ? 'alert-danger' : '' }} {{ $info['invite']->status == 'accepted' ? 'alert-success' : '' }}">
										{{ $info['invite']->status }}
									</div>							
								</div>
							</div>
						@endif
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key={{ Config::get('services.google_maps.api_key') }}&libraries=placest"></script>
	<script>
		function initMap() 
		{
			var uluru = {lat: {{$info['meeting']->latitude}}, lng: {{$info['meeting']->longitude}}};
			var map = new google.maps.Map(document.getElementById('map'), {
			  zoom: 15,
			  center: uluru
			});
			var marker = new google.maps.Marker({
			  position: uluru,
			  map: map
			});
		}
		setTimeout(function(){			
			initMap();
		}, 2000);
	</script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-filestyle/1.2.1/bootstrap-filestyle.js"></script>
	<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css" rel="stylesheet" type="text/css" />



<div class="modal fade" id="modal_messages">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<a href="javascript:void" class="text-back pull-left" data-dismiss="modal">
					<i class="fa fa-angle-left"></i>
				</a>
				<h4 class="modal-title">@lang('_.Messages')</h4>
			</div>
			<div class="modal-body modal-mesages text-center" >
				<div class="col-sm-12 media-msg-body">
							

				</div>
			</div>
			<div class="modal-footer">
				<div class="row send-msg-block">
					<div class="col-xs-9">
						<textarea style="height: 50px;" data-defaultheight="50" class="form-control msg-page-input" placeholder="@lang('_.Send a replay')"></textarea>
					</div>
					<div class="col-xs-3 pdg-left-del">
						<button class="btn btn-invite btn-send-page-msg text-uppercase ">@lang('_.send')</button>
					</div>
					<div class="sep_20_info">
					</div>
				</div>
			</div>
		</div>

	</div>
</div>
@endsection

@push("app-scripts")
<script>
		var auser_id = {{ Auth::user()->id }};
	</script>

	<script>
		
		var load_more = 2;
		var msg_timer = null;
		var view_timer = null;
		
		 $(function(){
			 $(".nicescroll").niceScroll({'cursorcolor':"#b0afae", 'railpadding' : {'bottom' : 3, 'top' : 3, 'left' : 5}});
			
			 $('#load_more').click(function(){
			 	ajax_msg();
			 	//clearInterval(view_timer);
			 	
			 	
		 		$("#modalView").modal("hide");
		 		$("#modal_messages").modal("show");
			 	

			 	//msg_timer = setInterval(ajax_msg, 5000);
		 		
			 	
				$("#modal_messages").on("hide.bs.modal", function () {
					$("#modalView").modal("show");
					ajax_view();
					//view_timer = setInterval(ajax_view, 5000);
					//clearInterval(msg_timer);
				});

				 /*var href = window.location.pathname.replace('more/' + segment(4), 'more/' + (segment(4) * 1 + 2));
				 window.history.pushState('page', 'Title', href);
				 load_more += 2;
				 ajax_msg();*/
			 });
			 
			

			$("#modalView").modal('show');
			
			$(".btn-accept").click(function(){
				$.ajax({
					'url' : '/invite_accept',
					'type' : 'post',
					'data' : {
						'_token' : $("meta[name='csrf-token']").attr('content'),
						'invite_id' : segment(2)
					},
					'complete' : function(data){
						showNotification("@lang('_.Invite accepted')");
						setTimeout(function(){
							to_history();							
						}, 2000);
					}
				});
			});
					
			$(".btn-reject").click(function(){
				$.ajax({
					'url' : '/invite_reject',
					'type' : 'post',
					'data' : {
						'_token' : $("meta[name='csrf-token']").attr('content'),
						'invite_id' : segment(2)
					},
					'complete' : function(data){
						showNotification("@lang('_.Invite rejected')");
						setTimeout(function(){
							to_history();							
						}, 2000);
					}
				});
			});
			
			$(".btn-send-msg").click(function(){
				
				if ( ! $('.msg-input').val())
				{
					$(".msg-input").addClass('input-invalid');
					showNotification("@lang('_.The field can not be empty')!");
					return false;
				}

				$.ajax({
					'url' : '/send_msg_invite',
					'type' : 'post',
					'data' : {
						'_token' : $("meta[name='csrf-token']").attr('content'),
						'invite_id' : segment(2),
						'msg_send' : $('.msg-input').val()
					},
					'complete' : function(data){
						$('.msg-input').val('');
						ajax_view();
						showNotification("@lang('_.Send messsages')");
					}
				});
			});

			$(".btn-send-page-msg").click(function(){
				
				if ( ! $('.msg-page-input').val())
				{
					$(".msg-page-input").addClass('input-invalid');
					showNotification("@lang('_.The field can not be empty')!");
					return false;
				}
				
				$.ajax({
					'url' : '/send_msg_invite',
					'type' : 'post',
					'data' : {
						'_token' : $("meta[name='csrf-token']").attr('content'),
						'invite_id' : segment(2),
						'msg_send' : $('.msg-page-input').val()
					},
					'complete' : function(data){
						$('.msg-page-input').val('');
						ajax_msg();
						showNotification("@lang('_.Send messsages')");
					}
				});
			});
			
			//view_timer = setInterval(ajax_view, 5000);
			
		});
		
		 function show_img_user(file) 
		{
			$("#big_face").attr("src", "/" + String(file).replace("min_" , ''));
			$("#modalView").modal("hide");
			$("#modal_face").modal('show');
		}

		$('#modal_face').on('hidden.bs.modal', function (e) {
			$("#modalView").modal("show");
		})

		function ajax_view()
		{
			$.ajax({
				'url' : '/get_msg_view',
				'type' : 'post',
				'data' : {
					'_token' : $("meta[name='csrf-token']").attr('content'),
					'hash' : segment(2)
				},
				'complete' : view_print
			});
		}

		function ajax_msg()
		{
			$.ajax({
				'url' : '/get_msg',
				'type' : 'post',
				'data' : {
					'_token' : $("meta[name='csrf-token']").attr('content'),
					'hash' : segment(2),
					'more' : segment(4)
				},
				'complete' : msg_print
			});
		}
		
		var _messages = [];
		
		function view_print(data)
		{
			if (data)
			{
				var data = $.parseJSON(data.responseText);
				_messages = data;
			}
			
			$(".msg-list").empty();
			var content = "",
				item;
			
			
			
			for(var i in data)
			{
				item = data[i];
				
				src = "/";
				if (item.user.file)
				{
					src += item.user.file;
				}
                else
                {
                    src += 'images/def_avatar.png';
                }
				/*
                else if (item.user.gender)
				{
					if(item.user.gender == 'M')
					{
						src += 'images/man.jpg';
					}
					else if (item.user.gender == 'F')
					{
						src += 'images/woman.png';
					}
				}
                */
				
				if (item.user.id == auser_id)
				{
					content += "<div class='form-group'>";
					content += "<div class='media'>";
					
					content += "<div class='media-left vertical-top'>";
					content += "<img class='pull-left img-circle img-msg'  src='" + src + "' alt='face user'/>";
					content += "</div>";
					
					content += "<div class='media-body text-left'>";
					content += "<span class='point-left'></span>";
					content += "<p class='msg_body'>" + item.message_body + "</p>";
					content += "</div>";
					
					content += "</div>";					
					content += "</div>";					
				}
				else
				{
					content += "<div class='form-group'>";
					content += "<div class='media'>";
					
					content += "<div class='media-body text-right bg-orange'>";
					content += "<span class='point-right'></span>";
					content += "<p class='msg_body'>" + item.message_body + "</p>";
					content += "</div>";
					
					content += "<div class='media-right vertical-top'>";
					content += "<img class='pull-left img-circle img-msg'  src='" + src + "' alt='face user'/>";
					content += "</div>";
					
					content += "</div>";	
					content += "</div>";
				}
				
			}
			
			$(".msg-list").append(content);
		}

		function msg_print(data)
		{
			if (data)
			{
				var data = $.parseJSON(data.responseText);
				_messages = data;
			}
			
			$(".media-msg-body").empty();
			var content = "",
				item;
			
			for(var i in data)
			{
				item = data[i];
				
				src = "/";
				if (item.user.file)
				{
					src += item.user.file;
				}
                else
                {
                    src += 'images/def_avatar.png';
                }
                /*
				else if (item.user.gender)
				{
					if(item.user.gender == 'M')
					{
						src += 'images/man.jpg';
					}
					else if (item.user.gender == 'F')
					{
						src += 'images/woman.png';
					}
				}
                */

				if (item.user.id != auser_id)
				{
					content += "<div class='form-group clearfix " + (item.first ? 'first-msg' : '') + "'>";
					content += "<div class='media msg-media'>";
					
					content += "<div class='media-left vertical-top'>";
					if (item.first)
					{
						content += "<img class='pull-left img-circle img-msg'  src='" + src + "' alt='face user'/>";
					}
					else
					{
						content += "<div style='width: 35px;'></div>";
					}
					content += "</div>";
					
					content += "<div class='media-body text-left left_time'>";

					content += "<div class='box-left-msg'>";
					if (item.new)
					{
						content += "<span class='new_msg'></span>";
					}

					if (item.first)
					{
						content += "<span class='time'>" + item.time + "</span>";
						content += "<span class='point-left'></span>";
					}
					content += "<p class='msg_body'>" + item.message_body + "</p>";
					content += "</div>";
					content += "</div>";

					content += "</div>";					
					content += "</div>";					
				}
				else
				{
					content += "<div class='form-group clearfix " + (item.first ? 'first-msg' : '') + "'>";
					content += "<div class='media msg-media'>";
					
					content += "<div class='media-right vertical-top'>";
					content += "<div style='width: 35px;'></div>";
					content += "</div>";

					content += "<div class='media-body text-right  right_time'>";
					
					
					
					content += "<div class='box-right-msg bg-orange'>";
					if (item.first)
					{
						content += "<span class='time'>" + item.time + "</span>";
						content += "<span class='point-right'></span>";
					}
					content += "<p class='msg_body'>" + item.message_body + "</p>";
					content += "</div>";



					content += "</div>";
					
					
					
					content += "</div>";	
					content += "</div>";
				}
				
			}
			
			$(".media-msg-body").append(content);
		}

		function to_history()
		{
			window.location.href = "/meeting/history";
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


		$(function(){
			$(".msg-page-input").focus(function () {
				$(this).attr('data-defaultheight', $(this).height());

				$(this).animate({
					height: 100
				}, 'slow');

				$(".btn-send-page-msg").animate({
					marginTop: 100 - 50
				}, 'slow');
      	  }).blur(function () {
			var h = $(this).attr('data-defaultheight');

			$(this).animate({
				height: h
				}, 'slow');

      	 	 $(".btn-send-page-msg").animate({
				marginTop: 0
				}, 'slow');
			});


		});


		$(function(){
			$(".msg-input").focus(function () {
				$(this).attr('data-defaultheight', $(this).height());

				$(this).animate({
					height: 100
				}, 'slow');

				$(".btn-send-msg").animate({
					marginTop: 100 - 50
				}, 'slow');
      	  }).blur(function () {
			var h = $(this).attr('data-defaultheight');

			$(this).animate({
				height: h
				}, 'slow');

      	 	 $(".btn-send-msg").animate({
				marginTop: 0
				}, 'slow');
			});


		});
	</script>
@endpush

<div class="modal fade" id="modal_face">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">{{$info['user']->first_name}} {{$info['user']->last_name}}</h4>
			</div>
			<div class="modal-body text-center" >
				<img src="" alt="Big face"  class="img-responsive img-face-user" id="big_face" />
			</div>
		</div>
	</div>
</div>

