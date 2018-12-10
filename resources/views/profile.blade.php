@extends('layouts.app')

@section('content')	
	<div id="modalProfile" data-backdrop="static" class="modal fade modal-profile" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close hidden-xs hidden-sm" data-dismiss="modal">&times;</button>
					<h4 class="modal-title text-uppercase " id="loginFormPopupTitle">@lang('_.my profile')</h4>
				</div>
				
				<div class="modal-body pdg-30  del-botttom-pdg">
					<div class="row del-mrg md-pdg-bottom-30">

					
						<div class="col-md-12 del-pdg">
							<form class="form-horizontal del-pdg" enctype="multipart/form-data"  method="post" action="save_profile">
								<input type="hidden" name="latitude" value="{{ $user->latitude }}">
								<input type="hidden" name="longitude" value="{{ $user->longitude }}">
								<input type="hidden" name="name_addr" value="{{ $user->name_addr }}">
								
								<input type="hidden" name="id" value="{{ $user->id }}">
								<input type="hidden" name="_token" value="{{ csrf_token() }}">
								<div class="form-group del-top-mrg">
									<div class="row">
										<div class="col-sm-4">
											<label class="control-label md-hidden-top-pdg">@lang('_.First name')</label>
										</div>
										<div class="col-sm-8">
											<input class="form-control" name="first_name" placeholder="@lang('_.Your first name')" minlength="4" value="{{ $user->first_name }}" />
										</div>
									</div>
								</div>
								<div class="form-group">
									<div class="row">
										<div class="col-sm-4">
											<label class="control-label">@lang('_.Last name')</label>
										</div>
										<div class="col-sm-8">
											<input class="form-control" name="last_name" placeholder="@lang('_.Your last name')" value="{{ $user->last_name }}" />
										</div>
									</div>
								</div>
								<div class="form-group">
									<div class="row">
										<div class="col-sm-4">
											<label class="control-label">@lang('_.Date of birth')</label>
										</div>
										<div class="col-sm-8 clearfix">
											<div class="row navigation-date">
												<div class="col-xs-4">
													<div class="btn-group date-item">
			 											<input type="hidden" value="{{ $user->birth_year }}" name="birth_year" />
														<button type="button" class="btn  dropdown-toggle" data-toggle="dropdown">
															{{ $user->birth_year ? $user->birth_year : trans('_.Year') }} 
														</button>
														<ul class="dropdown-menu" role="menu">
															@for ($i = 1900; $i <= (date("Y") - 18); $i++)
															<li class="date-year" data-value="{{ $i }}">
																{{ $i }}
															</li>
															@endfor
														</ul>
													</div>
												</div>
												<div class="col-xs-4">
													<div class="btn-group date-item">
														<input type="hidden" value="{{ $user->birth_month }}" name="birth_month" />
														<button type="button" class="btn  dropdown-toggle" data-toggle="dropdown">
															{{ $user->birth_month ? date('M', mktime(0, 0, 0, $user->birth_month, 10)) : trans('_.Month') }}
														</button>
														<ul class="dropdown-menu" role="menu">
															@for($month = 1; $month <= 12; $month++)
																<li class="date-month" data-value="{{ date('M', mktime(0, 0, 0, $month, 10)) }}">
																	{{ date('M', mktime(0, 0, 0, $month, 10)) }}
																</li>
															@endfor
														</ul>
													</div>
												</div>
												<div class="col-xs-4">
													<div class="btn-group date-item">
														<input type="hidden" value="{{ $user->birth_day }}" name="birth_day" />
														<button type="button" class="btn  dropdown-toggle" data-toggle="dropdown">
															{{ $user->birth_day ? $user->birth_day : trans('_.Day') }}
														</button>
														<ul class="dropdown-menu" role="menu">
															@for ($i = 1; $i <= 31; $i++)
																<li class="date-day" data-value="{{ $i }}">{{ $i }}</li>
															@endfor
														</ul>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="form-group">
									<div class="row">
										<div class="col-sm-4">
											<label class="control-label">@lang('_.City')</label>
										</div>
										<div class="col-sm-8">
											<input class="form-control" id="address" value="{{ $user->full_address }}" name="full_address" placeholder="@lang('_.City, Address')" />
										</div>
									</div>
								</div>
								<div class="form-group">
									<div class="row">
										<div class="col-sm-4">
											<label class="control-label">@lang('_.Gender')</label>
										</div>
										<div class="col-sm-8 ">
											
											<div class="wrapper-select select-date max-width-180  z_index_1">
												<span class="wrapper-header">{{ $user->gender  ? (($user->gender == 'M') ? 'Male' : (($user->gender == 'F') ? 'Female' : ''))  : trans('_.Choose') }}</span>
												<div class="mjs-select max-height">
													<input type="hidden" data-cellback="test" name="gender"  value="{{ $user->gender  ? $user->gender : '' }}" class="mjs-value" />
													<div class="mjs-option {{ ! in_array($user->gender, ['M', 'F']) ? 'selected' : '' }}" data-option="0">
			 											@lang('_.Choose')
													</div>
													<div class="mjs-option {{ $user->gender == 'M' ? 'selected' : '' }}" data-option='M'>
														@lang('_.Male')
													</div>
													
													<div class="mjs-option {{ $user->gender == 'F' ? 'selected' : '' }}" data-option='F'>
														@lang('_.Female')
													</div>
												</div>
											</div>
											
										</div>
									</div>
								</div>

								<div class="form-group">
									<div class="row">
										<div class="col-sm-4">
											<label class="control-label">@lang('_.Change Password')</label>
										</div>
										<div class="col-xs-8">
											<label onclick="$('#modalProfile').modal('hide');" data-target="#modal-chg-password" data-toggle="modal" class="btn btn-default text-uppercase btn-chg-psw">@lang('_.update')</label>
										</div>
									</div>
								</div>
				
								<div class="form-group">
									<div class="row">
										<div class="col-sm-4">
											<label >@lang('_.Able to meet in *km from the city')</label>
										</div>
										<div class="col-sm-8 test pdg-top-10">
											<p class="range_km" data-content='1000 @lang('_.km')'>
												<b id="labelAgeMin"></b>
												<input  id="ageSlider"
														type="text"
														class="span2"
														name="able_length"
														data-slider-min="0"
														data-slider-max="1000"
														data-slider-step="1"
														data-slider-tooltip="hide"
														data-slider-value="{{ $user->able_max ? $user->able_max : 100 }}"
														rangeHighlights="[{'start' : 0, 'end' : 1000}]" />
											</p>
											<input type="hidden" name="able_min" id="ageMin" value="0" />
											<input type="hidden" name="able_max" id="ageMax" value="{{ $user->able_max ? $user->able_max : 100 }}" />
											<input type="hidden" name="rotate_img" value="0" />
										</div>
									</div>
								</div>
								<div class="form-group">
									<div class="row">
										<div class="col-sm-4">
											<label class="control-label">@lang('_.Availability')</label>
										</div>
										<div class="col-sm-8 ">
											
											<div class="wrapper-select select-date max-width-180  z_index_1">
												<span class="wrapper-header">{{ isset($user->availability)  ? ($user->availability  == 1 ? trans('_.Not available') : ($user->availability  == 0 ? trans('_.Public') : trans('_.Availability'))) : 'Availability' }}</span>
												<div class="mjs-select max-height">
													<input type="hidden" name="availability" value="{{ $user->availability  ? ($user->availability == 1 ? trans('_.Public') : trans('_.Not available')) : 0 }}" class="mjs-value" />
													<div class="mjs-option {{ $user->availability == 0 ? 'selected' : '' }}" data-option='0'>
														@lang('_.Public')
													</div>
													
													<div class="mjs-option {{ $user->availability == 1 ? 'selected' : '' }}" data-option='1'>
														@lang('_.Not available')
													</div>
												</div>
											</div>
										
										</div>
									</div>
								</div>
								
								<div class="form-group">
									<div class="row">
										<div class="col-sm-4">
											<label class="control-label">Receive notification emails from SportyPeople</label>
										</div>
										<div class="col-sm-8 padding-top-receive">
											<div class="wrapper-select select-date max-width-180  z_index_1">
												<span class="wrapper-header">
												{{ isset($user->invite_sent)  ? ($user->invite_sent  == 1 ? 'Yes' : ($user->invite_sent  == 0 ? 'No' : 'No')) : 'Yes' }}
												</span>
												<div class="mjs-select max-height">
													<input type="hidden" name="invite_sent" value="{{ $user->invite_sent  ? ($user->invite_sent == 1 ? 'Yes' : 'No') : 0 }}" class="mjs-value" />
													<div class="mjs-option {{ $user->invite_sent == 1 ? 'selected' : '' }}" data-option='1'>
														@lang('_.Yes')
													</div>
													
													<div class="mjs-option {{ $user->invite_sent == 0 ? 'selected' : '' }}" data-option='0'>
														@lang('_.No')
													</div>
												</div>
											</div>
									</div>
								</div>

								<div class="form-group">
									<div class="row">
										<div class="col-sm-4">
											<label class="">@lang('_.Upload recent photo not older than 3 months')</label>
										</div>
										<div class="col-sm-8">
											<input type="file" class="filestyle pull-left"  name="avatar" data-icon="false" data-buttonText="{{! empty( $user->file ) ? trans('_.change') : trans('_.browse')}}" data-input="false"/>

											<div class="wrapper-img-">
												<!--div id="preview_img" style="background-image: url({{ ! empty( $user->file ) ? $user->file : ( ! empty($user->gender) ? ( $user->gender == 'M' ? 'images/man.jpg' : ($user->gender == 'F' ? 'images/woman.png' : '')) : '') }})"-->
												<div id="preview_img" style="background-image: url({{ ! empty( $user->file ) ? $user->file : 'images/def_avatar.png' }})">
													<!--<img width="64" height="64" class=" img-circle img-profile" src="" alt="" />-->
													
												</div>
												<span style="{{ empty($user->file) ? 'display: none;' : '' }}" onclick="change_rotate()" class="chnage_rotation">
													<i class="fa fa-refresh"></i>
												</span>

											</div>
										</div>
									</div>
								</div>

								<div class="form-group">
									<div class="row">
										<div class="col-sm-4">
											<label class="control-label">@lang('_.About me')</label>
										</div>
										<div class="col-sm-8 padding-top-receive">
											<textarea class="custom-textarea" name="about_me">{{ $user->about_me }}</textarea>
										</div>
									</div>
								</div>

								<div class="form-group del-mrg-all">
									<div class="row">
										<div class="col-sm-12 text-center">
											<button class="btn btn-lg btn-warning text-uppercase del-mrg-all">@lang('_.update')</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

	<div class="modal fade" id="modal-chg-password">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<a href="javascript:void" class="text-back pull-left" data-dismiss="modal">
						<i class="fa fa-angle-left"></i>
					</a>
					<h4 class="modal-title text-uppercase">@lang('_.Change Password')</h4>
				</div>
				<div class="modal-body pdg-30  del-botttom-pdg">
					<div class="form-group del-top-mrg">
						<div class="row form-group">
							<div class="col-sm-4">
								<label class="control-label md-hidden-top-pdg">@lang('_.New Password')</label>
							</div>
							<div class="col-sm-8">
								<input class="form-control" type="password" id="new_pass" placeholder="@lang('_.New Password')" minlength="4" />
							</div>
						</div>
						<div class="row form-group">
							<div class="col-sm-4">
								<label class="control-label md-hidden-top-pdg">@lang('_.Confirm Password')</label>
							</div>
							<div class="col-sm-8">
								<input class="form-control" type="password" id="confirm_pass" placeholder="@lang('_.Confirm Password')" minlength="4" />
							</div>
						</div>
						<div class="row">
							<div class="col-sm-12">
								<p class="sub-title-change-password">
									You password must contain the following:
									<ul class="constom-list">
										<li>
											1. @lang('_.At least 8 characters in length (a strong password has at least 14 characters)')
										</li>
										<li>
											2. @lang('_.At least 1 letter and at least 1 number or symbol')
										</li>
									</ul>
								</p>
							</div>
						</div>
						
						<div class="row">
							<div class="col-sm-6 col-sm-offset-2">
								<button id="change_password" class="btn btn-lg btn-warning text-uppercase del-mrg-all">@lang('_.update')</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@push("app-scripts")
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-filestyle/1.2.1/bootstrap-filestyle.js"></script>
	<script type="text/javascript" 
        src="https://maps.googleapis.com/maps/api/js?key={{ Config::get('services.google_maps.api_key') }}&libraries=places&callback=init"
        async defer></script> 
	
	<script>
		var deg = 0,
			count_rotate = 0,
			_errors,
			_errors_list;
		
		@if (count($errors))
			{{ "_errors = " . json_encode($errors->all()) . ";" }}
			{{ "_errors_list = {}" }}
		@endif
		
		@if ( ! empty($user->rotate))
			{{ "deg = " . $user->rotate . "; " }}
		@endif
		
		function change_rotate()
		{
			count_rotate ++;
			deg += 90;
			deg = deg >= 360 ? 0 : deg;
			console.log(deg);
			$("input[name=rotate_img]").val(count_rotate);
			$("#preview_img").css("transform", "rotate(" + deg + "deg)");
		}

		$(function(){
			if ( _errors && _errors.length )
			{
				var item;
				for(var i in  _errors)
				{
					item = _errors[i];
					showNotification(item, {color: 'red'});
				}
			}

			$('#modal-chg-password').on('hide.bs.modal', function () {
				$("#modalProfile").modal("show");
			});
			 
			$(".nicescroll").niceScroll({'cursorcolor':"#b0afae", 'railpadding' : {'bottom' : 3, 'top' : 3, 'left' : 5}});
			$("#modalProfile").modal('show');
			$("#ageSlider").slider({});
			
			$("#ageSlider").change(function(){
				$(".age_min").html($(this).val() + ' km');
			});

			var left = 0;
			
			$(".age_min").text($("#ageMax").val() + ' km');
			
			$("#ageSlider").on("slide", function(slideEvt) {
				$(".age_min").html(slideEvt.value + ' km');
				if (slideEvt.value > 920)
				{
					$(".age_min").css({'left' : '-44px'});
				}
				else if (slideEvt.value < 400)
				{
					$(".age_min").css({'left' : '0px'});
				}
				else
				{
					$(".age_min").css({'left' : '-20px'});
				}
				
				$("#ageMin").val(0);
				$("#ageMax").val(slideEvt.value);
			});
            
			$(".filestyle").change(function(e){
				deg = 0;
				$("input[name=rotate_img]").val("0");
                var fileType = e.target.files[0].type;
                var fr = new FileReader();
                if (fileType.match(/^image\/*/)){
                    fr.onload = function (e) 
					{
						$("#preview_img").attr('style', 'background-image : url('  + fr.result + ')');
						$(".chnage_rotation").attr("style", "");
					}
					fr.readAsDataURL($(this)[0].files[0]);
                }
                else{
                    $(this).val('');
                    showNotification("@lang('_.File format not valid')", {color: 'red'}); 
                }
			});

			$(".date-year").click(function(){
				var value = $(this).data("value");
				$(this).parent().parent().find(".dropdown-toggle").text(value);
				$(this).parent().parent().find("input[name=birth_year]").val(value);
			});

			$(".date-month").click(function(){
				var value = $(this).data("value");
				$(this).parent().parent().find(".dropdown-toggle").text(value);
				$(this).parent().parent().find("input[name=birth_month]").val(value);
			});

			$(".date-day").click(function(){
				var value = $(this).data("value");
				$(this).parent().parent().find(".dropdown-toggle").text(value);
				$(this).parent().parent().find("input[name=birth_day]").val(value);
			});

			@if ($user->able_max)
				$("#labelAgeMax").html( '{{$user->able_max }} km');	
			@endif


			$("#change_password").click(function(){
				var new_pass = $("#new_pass").val();
				var confirm_pass = $("#confirm_pass").val();
				var errors = 0;

				if ( new_pass.length < 8 )
				{
					showNotification("@lang('_.At least 8 characters')", {'color' : 'red'});
					return;
				}

				if ( ! /[a-z]/i.test(new_pass))
				{
					showNotification("@lang('_.At least 1 letter')", {'color' : 'red'});
					return;
				}

				if ( ! /[0-9]/i.test(new_pass))
				{
					showNotification("@lang('_.At least 1 number')", {'color' : 'red'});
					return;
				}

				if (new_pass != confirm_pass)
				{
					showNotification("@lang('_.Passwords do not match')", {'color' : 'red'});
					return;
				}
				
				if ( ! errors)
				{
					$.ajax({
						type : "POST",
						url : '/api/user/change_password',
						data : {'_token' : $('meta[name="csrf-token"]').attr('content'), 'new_pass' : new_pass},
						success : function(res){
							showNotification("Password has been updated successfully");
							$("#modal-chg-password").modal('hide');
							$("#modalProfile").modal('show');
						}
					});
				}
			});	
		});
		
		function init() 
		{
			var service = new google.maps.places.SearchBox(document.getElementById('address'));
			service.addListener('places_changed', function() {
				var items = service.getPlaces();
				$("[name='latitude']").val(items[0].geometry.location.lat());
				$("[name='longitude']").val(items[0].geometry.location.lng());
				
				 for(var i in items[0].address_components)
				{
				  var _item = items[0].address_components[i];
				  if (_item.types.indexOf('locality') + 1)
					{
					  $("[name='name_addr']").val(_item.long_name);
					}
				}
				
				
			});	
		}
		
		function change_manth(index)
		{
			
			var meetingYear = new Date().getYear();

			var currentMonth = (new Date().getMonth()) + 1;

			if(index > currentMonth)
			{
				meetingYear++;
			}

			var tmpDaysInMonth = daysInMonth(index, meetingYear);

			var html = '<input  type="hidden" name="birth_day" value="' + $("input[name='birth_day']").val() + '" class="mjs-value" />';
			for(let i = 1; i <= tmpDaysInMonth; i++)
			{
				html += "<div class='mjs-option'  onclick='change_day(" + i + ")'>" + i + "</div>";
			}
			$("#meeting_day").html(html);
			$("input[name=birth_month]").val(index);
		}
		
		function change_day(index)
		{
			$("input[name='birth_day']").parent().parent().find(".wrapper-header").text(index);
			$("input[name='birth_day']").val(index);
		}
		
		function daysInMonth(month,year) 
		{
			return new Date(year, month, 0).getDate();
		}
		
		function test(gender)
		{
            $(".img-profile").attr('src', 'images/def_avatar.png');			
        }
	
	</script>
@endpush