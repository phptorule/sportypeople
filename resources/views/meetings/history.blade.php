@extends('layouts.app')

@section('content')
	<div id="modalEventResult" data-backdrop="static" class="modal fade modal-profile" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<a href="/meeting/create" class="text-back pull-left">
						<i class="fa fa-angle-left"></i>
					</a>
					<h4 class="modal-title text-uppercase pull-left" id="loginFormPopupTitle">
						@lang('_.Dates') 
					</h4>
				</div>
				<div class="modal-body pdg-30 del-botttom-pdg pdg-top-20">
					<div class="row del-mrg md-pdg-bottom-30">

						<div class="col-md-12 del-pdg">
							<div class="form-group clearfix btn-nav mrg-bottom-30">
								<div class="col-xs-4">
									<button type="button" class="btn btn-nav_ active upconming-btn del-top-mrg">
										@lang('_.Upcoming')
									</button>
								</div>
								<div class="col-xs-4">
									<button type="button" class="btn btn-nav_ upconfirmed-btn del-top-mrg">
										@lang('_.Unconfirmed')
									</button>
								</div>
								<div class="col-xs-4">
									<button type="button" class="btn btn-default btn-nav_  past-btn del-top-mrg">
										@lang('_.Past')
									</button>
								</div>
							</div>

							
							<!-- upcoming -->
							<div data-tabe="upconming-btn" class="form-group event-list del-bottom-mrg tabe active">
								
								@foreach ($upcoming as $row)
									
										<div class="media">
											<div class="media-left wrap-point {{ $row->msg_new ? 'red-point' : '' }}">
												<a href="/meeting/view/{{$row->hash}}/more/2">
												<!--img class="events-imgs pull-left img-circle img-invite" src="/{{ ! empty( $row->user->file ) ? $row->user->file : ( ! empty($row->user->gender) ? ( $row->user->gender == 'M' ? 'images/man.jpg' : ($row->user->gender == 'F' ? 'images/woman.png' : '')) : '') }}" /-->
												<img class="events-imgs pull-left img-circle img-invite" src="/{{ ! empty( $row->user->file ) ? $row->user->file : 'images/def_avatar.png' }}" />
												</a>
											</div>
											<div class="media-body">
												 <div class="space_media">
												 </div>
												 <h6 class="media-heading">{{ $row->user->first_name }} {{ $row->user->last_name }} <br /><span class="hidden-lg hidden-md hidden-sm date-xs">({{ date('d-m-Y', strtotime($row->created_at))  }})</span></h6>
												 <p class="text-muted">
													{{ $row->user->full_address }}
													
												 </p>
												 
											</div>
											<div class="media-right vertical">
												<a href="/meeting/view/{{$row->hash}}/more/2" class="btn btn-default <?php echo ! empty($row->sent) ? 'btn-sent' : 'btn-custom'; ?> ft-normal pdg-lr-20 ">Info</a>
											</div>
										</div>
									
								@endforeach
								
								@if ( ! $upcoming)
									<h6>@lang('_.Empty list')!</h6>
								@endif
								
							</div>

							<!-- upconfirmed -->
							<div data-tabe="upconfirmed-btn" class="form-group event-list del-bottom-mrg tabe">
							
								@if ( ! empty($upconfirmed))
									@foreach ($upconfirmed as $row)
										<div class="media">
											<div class="media-left wrap-point {{ $row->msg_new || $row->status ? 'red-point' : '' }}">
												<!--img class="events-imgs pull-left img-circle img-invite" src="/{{ ! empty( $row->user->file ) ? $row->user->file : ( ! empty($row->user->gender) ? ( $row->user->gender == 'M' ? 'images/man.jpg' : ($row->user->gender == 'F' ? 'images/woman.png' : '')) : '') }}" /-->
												<img class="events-imgs pull-left img-circle img-invite" src="/{{ ! empty( $row->user->file ) ? $row->user->file : 'images/def_avatar.png' }}" />
											</div>
											<div class="media-body">
												 <div class="space_media">
												 </div>
												 <h6 class="media-heading">{{ $row->user->first_name }} {{ $row->user->last_name }} <br /><span class="hidden-lg hidden-md hidden-sm date-xs">({{ date('d-m-Y', strtotime($row->created_at))  }})</span></h6>
												 <p class="text-muted">
													{{ $row->user->full_address }}
												</p>
											</div>
											<div class="media-right">
												<a href="/meeting/view/{{$row->hash}}/more/2" class="btn btn-default btn-custom ft-normal pdg-lr-20">Info</a>
											</div>
										</div>
									@endforeach
								@else
									<h6>
										@lang('_.Empty list')!
									</h6>
								@endif
							</div>

							<!-- past -->
							<div data-tabe="past-btn" class="form-group event-list del-bottom-mrg tabe ">
								@if ( ! empty($past))
									@foreach ($past as $row)
										<div class="media">
											<div class="media-left wrap-point {{ $row->msg_new || $row->status ? 'red-point' : '' }}">
												<img class="events-imgs pull-left img-circle img-invite" src="/{{ ! empty( $row->user->file ) ? $row->user->file : 'images/def_avatar.png' }}" />
											</div>
											<div class="media-body">
												 <div class="space_media">
												 </div>
												 <h6 class="media-heading">{{ $row->user->first_name }} {{ $row->user->last_name }} <br /><span class="hidden-lg hidden-md hidden-sm date-xs">({{ date('d-m-Y', strtotime($row->created_at))  }})</span></h6>
												 <p class="text-muted">
													{{ $row->user->full_address }}
												</p>
											</div>
											<div class="media-right">
												<a href="/meeting/view/{{$row->hash}}/more/2" class="btn btn-default btn-sent ft-normal pdg-lr-20">Info</a>
											</div>
										</div>
									@endforeach
								@else
									<h6>
										@lang('_.Empty list')!
									</h6>
								@endif
							</div>
							
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>			
@endsection

@push('app-scripts')
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-filestyle/1.2.1/bootstrap-filestyle.js"></script>
	<script>
		 $(function(){
			$("#modalEventResult").modal('show');

			$(".upconming-btn").click(function(){
				$(".btn-nav_").removeClass("active");
				$(this).addClass("active");
				$(".tabe").removeClass("active");
				$("[data-tabe=upconming-btn]").addClass('active');
			});
			
			$(".upconfirmed-btn").click(function(){
				$(".btn-nav_").removeClass("active");
				$(this).addClass("active");
				$(".tabe").removeClass("active");
				$("[data-tabe=upconfirmed-btn]").addClass('active');
			});

			$(".past-btn").click(function(){
				$(".btn-nav_").removeClass("active");
				$(this).addClass("active");
				$(".tabe").removeClass("active");
				$("[data-tabe=past-btn]").addClass('active');
			});
			
			 
			 $(".nicescroll").niceScroll({'cursorcolor':"#b0afae", 'railpadding' : {'bottom' : 3, 'top' : 3, 'left' : 5}});
	
			
		});
	</script>
@endpush