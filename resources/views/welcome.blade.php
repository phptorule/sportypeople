@extends('layouts.app')

@section('content')
<div id="modalLogin" data-backdrop="static" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close close-light" data-dismiss="modal">&times;</button>
                <h4 class="modal-title text-uppercase" id="loginFormPopupTitle">@lang('_.Login to your account')</h4>
            </div>
				<div class="modal-body pdg-30  del-botttom-pdg">
					<div class="row del-mrg md-pdg-bottom-30">

                    <div class="col-md-12 del-pdg ">
                        <form class="form-horizontal del-pdg" id="formLogin">
                            {{ csrf_field() }}
                            <div class="form-group del-top-mrg">
                                <label class="control-label col-sm-4 del-left-pdg">@lang('_.Email')</label>
                                <div class="col-sm-8 del-right-pdg">
                                    <input name="username" type="email" class="form-control" required placeholder="@lang('_.Your email')">
                                </div>
                            </div>
                            <div class="form-group">
								<label class="control-label col-sm-4 del-left-pdg">@lang('_.Password')</label>
                                <div class="col-sm-8 del-right-pdg">
                                    <input name="password" type="password" class="form-control" required placeholder="@lang('_.Your password')">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-4 col-sm-offset-4 del-right-pdg">
                                    <input name="remember_me" type="checkbox" id="remember_me" />
									<label id="remember"  for="remember_me">@lang('_.Remember me')</label>
                                </div>
                            </div>
							<div class="form-group del-mrg xs-media">
								<div class="col-sm-12 text-center del-pdg del-mrg">
									 <button class="btn btn-warning btn-lg text-uppercase del-mrg" id="btnLogin">@lang('_.login')</button>
								</div>
							</div>
                            <!--<p class="text-center">
                                <button class="btn btn-warning btn-lg text-uppercase" id="btnLogin">login</button>
                            </p>-->
							
							<div class="center-block text-center form-group del-mrg-all">
                                <a href="javascript: void(0);" id="btnOpenResetPassword" class="formgot-text">
                                     @lang('_.Forgot your password?')
                                </a>
                            </div>
							
                        </form>
                        <form class="form-horizontal del-pdg" id="formResetPassword" style="display: none;">
                            {{ csrf_field() }}
                            <div class="form-group del-top-mrg">
                                <label class="control-label col-sm-4 del-left-pdg">@lang('_.Email')</label>
                                <div class="col-sm-8 del-pdg">
                                    <input name="email" type="email" class="form-control" required placeholder="@lang('_.Write the email you use for login')">
                                </div>
                            </div>
							
							<div class="center-block text-center form-group del-pdg del-mrg xs-media">
								<button class="btn btn-warning btn-lg" id="btnResetPassword">@lang('_.SEND')</button>
							</div>
							
							<div class="center-block text-center form-group del-mrg-all">
                                <a href='javascript: void(0);' id="btnOpenLoginForm" class="formgot-text">
                                    @lang('_.Go back to login')
                                </a>
                            </div>
							
							
							
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="terms_cond_modal" data-backdrop="static" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
               <a href="javascript:void" class="text-back pull-left" data-dismiss="modal">
					<i class="fa fa-angle-left"></i>
				</a>
				<h4 class="modal-title text-uppercase" id="loginFormPopupTitle">@lang('_.Terms and Conditions')</h4>
            </div>
			<div class="modal-body pdg-30  del-botttom-pdg">
				<div class="row del-mrg md-pdg-bottom-30">
					@lang('_.SportyPeople is not a dating site. Our service is meant to meet new people who are interested in sport.')
					<br />
					<br />
					@lang('_.To register takes only a few seconds, is free of charge and within the minute you know who can join you to your next sport activity!')
					<br />
					<br />					
					@lang('_.Our app is 100% free of charge!')

					<div onclick="$('#more_info_modal').modal('hide'); $('.btn-register').removeClass('disabled'); $('#terms_cond').attr('checked', 'checked');" data-dismiss="modal" class="center-block form-group text-center btn-register">
						<button class="btn btn-warning btn-lg del-mrg" id="btnRegister">@lang('_.Agree')</button>
					</div>

				</div>
			</div>
        </div>
    </div>
</div>

<div id="more_info_modal" data-backdrop="static" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close close-light" data-dismiss="modal">&times;</button>
                <h4 class="modal-title text-uppercase title-more-info" id="loginFormPopupTitle">@lang('_.meet new people and boost your excercise motivatoin')</h4>
            </div>
			<div class="modal-body pdg-30  del-botttom-pdg">
				<div class="row del-mrg md-pdg-bottom-30">
					<p>
						@lang('_.The scientifically proven Köchler effect shows that a workout buddy will boost your exercise motivation with over 100%.')
					</p>
					<p>
						@lang('_.That´s why we started Sporty People; don´t train alone, use the opportunity to meet new people, expand your network, be inspired and have fun!')
					</p>
					<p>
						@lang('_.Expand your network - Be inspired - Have fun - Stay motivated!')
					</p>
					@lang('_.Our app is 100% free of charge!')
					<div onclick="$('#more_info_modal').modal('hide');" data-target="#modalRegister" data-toggle="modal" class="center-block form-group text-center btn-register">
						<button class="btn btn-warning btn-lg del-mrg" id="btnRegister">@lang('_.REGISTER')</button>
					</div>

				</div>
			</div>
        </div>
    </div>
</div>

<div id="modalRegister" data-backdrop="static" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close close-light" data-dismiss="modal">&times;</button>
        <h4 class="modal-title text-uppercase title-register">@lang('_.Register to meet new people')</h4>
      </div>
		<div class="modal-body pdg-30  del-botttom-pdg">
			<div class="row del-mrg md-pdg-bottom-30">
			<div class="col-md-12 del-pdg">
				<form class="form-horizontal del-pdg" id="formRegister">
					<input name="address" type="hidden" value="" />
					{{ csrf_field() }}

					<!-- FIRST NAME -->
					<div class="form-group del-top-mrg">
						<label class="control-label col-sm-4 del-left-pdg">@lang('_.First name')</label>
						<div class="col-sm-8 del-right-pdg">
							<input name="first_name" type="text" class="form-control" required placeholder="@lang('_.Write your first name')">
						</div>
					</div>

					<!-- LAST NAME -->
					<div class="form-group">
						<label class="control-label col-sm-4 del-left-pdg">Last name</label>
						<div class="col-sm-8 del-right-pdg">
							<input name="last_name" type="text" class="form-control" required placeholder="@lang('_.Write your last name')">
						</div>
					</div>

					<!-- EMAIL -->
					<div class="form-group">
						<label class="control-label col-sm-4 del-left-pdg">@lang('_.Email')</label>
						<div class="col-sm-8 del-right-pdg">
							<input name="email" type="email" class="form-control" required placeholder="@lang('_.Eg. kund.munich@mail.com')">
						</div>
					</div>

					<!-- PASSWORD -->
					<div class="form-group">
						<label class="control-label col-sm-4 del-left-pdg">@lang('_.Password')</label>
						<div class="col-sm-8 del-right-pdg">
							<input name="password" type="password" class="form-control" required minlength="8" placeholder="@lang('_.Minimum 8 characters long')">
						</div>
					</div>

					<!-- REPEAT PASS. -->
					<div class="form-group">
						<label class="control-label col-sm-4 del-left-pdg">@lang('_.Retype password')</label>
						<div class="col-sm-8 del-right-pdg">
							<input name="password_confirmation" type="password" class="form-control" required placeholder="@lang('_.Retype your password')">
						</div>
					</div>

					<div class="form-group">
						<div class="col-sm-4 del-left-pdg"></div>
						<div class="col-sm-8 del-right-pdg">
							<input name="terms_cond" type="checkbox" id="terms_cond" />
							<label id="terms"  for="terms_cond">@lang('_.I agree to the')</label> <label><a data-target="#terms_cond_modal" data-toggle="modal" onclick="$('#modalRegister').modal('hide')" href="javascript:void(0);">@lang('_.terms and conditions')</a></label>
						</div>
					</div>
					
					<div class="center-block form-group text-center del-pdg del-mrg ">
						<button class="btn btn-warning btn-lg del-mrg btn-register disabled" disabled id="btnRegister">@lang('_.REGISTER')</button>
					</div>
					
					<div class="center-block form-group text-center del-mrg  del-pdg mrg-top-mobi del-bottom-mrg">
						<a href="javascript: void(0);" class="formgot-text" id="btnOpenLoginForm" data-dismiss="modal" data-toggle="modal" data-target="#modalLogin">
							@lang('_.Already have an account')
						</a>
					</div>
				</form>
			</div>
		  </div>
		 </div>
	  </div>
	</div>
</div>
@endsection

@push("app-scripts")
	<script>
		$(function(){
			var show_login;
			show_login = {{ ! empty($show_login) ? $show_login : 'null' }};
			if (show_login)
			{
				$("#modalLogin").modal('show');
			}
			
			$(".nicescroll").niceScroll({'cursorcolor':"#b0afae", 'railpadding' : {'bottom' : 3, 'top' : 3, 'left' : 5}});	 		
		
			var show_reset;
			show_reset = {{ ! empty($show_reset) ? $show_reset : 'null' }};
			if (show_reset)
			{
				showNotification("@lang('_.A reset link is sent. Please check your email').");
			}
		});
	</script>
@endpush
 