@extends('layouts.app')

@section('content')
	<div id="modal404" data-backdrop="static" class="modal fade modal-profile" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<a href="/meeting/create" class="text-back pull-left">
						<i class="fa fa-angle-left"></i>
					</a>
					<h4 class="modal-title text-uppercase ">error!</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-8 col-md-offset-2 text-center page_404">
							<h1>404</h1>
							<h2>Oops! We can't find this page.</h2>
							<p>
								You can go back to what you were doing, <ahref="javascript: void(0);">click here.</a><br />
								If you believe this is an error please send us an e-mail
								<a href="javascript: void(0);">contact@museumdate.com</a>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@push('app-scripts')
	<script>
		$(document).ready(function(){
			$("#modal404").modal('show');
		});
	</script>
@endpush