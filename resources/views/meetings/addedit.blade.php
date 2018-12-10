@extends('layouts.app')

@section('content')
<style>
	.age_max {
		width: auto !important;
	}
</style>
<div id="modalSearch" data-backdrop="static" class="modal fade modal-search" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close hidden-xs hidden-sm" data-dismiss="modal">&times;</button>
                <h4 class="modal-title text-uppercase" id="loginFormPopupTitle">@lang('_.Search for people')</h4>
            </div>
				<div class="modal-body pdg-30  del-botttom-pdg">
					<div class="row del-mrg md-pdg-bottom-30">
                    <div class="col-md-12 del-pdg">

                        <form id="formMeeting" method="post"  class="form-horizontal del-pdg">

                            {{ csrf_field() }}

                            <div class="form-group del-top-mrg">
                                <label class="control-label col-sm-4 md-hidden-top-pdg del-left-pdg">@lang('_.Training/Sport')</label>
                                <div class="col-sm-8 del-right-pdg md-del-left-pdg"> 
                                    <input required 
                                           id="museumSearch"
                                           type="text"
                                           class="form-control input"
                                           placeholder="@lang('_.Type name location or address')"
                                           style="z-index: 99999 " />
                                </div>

                                <input type="hidden" name="latitude" id="fldLatitude" />
                                <input type="hidden" name="longitude" id="fldLongitude" />
                                
                            </div>
							
							<input id="fldFullAddress" type="hidden" class="form-control input" name="full_address">

                            <div class="form-group">
                                <label class="control-label col-sm-4 del-left-pdg">@lang('_.Select date') *</label>
                                <div class="col-sm-8 clearfix del-right-pdg md-del-left-pdg">
                                    
									<div class="col-xs-6 date-combo pdg-right-5">
										<div class="wrapper-select select-date">
											<span class="wrapper-header">{{ date('M') }}</span>
											<div class="mjs-select  max-height z_index_custom">
												<input type="hidden" value="{{ date('m') }}" name="meeting_month" class="mjs-value" />
												 <div class="mjs-option" data-option=''>@lang('_.Month')</div>
												@for($month = 1; $month <= 12; $month++)
													<div class="mjs-option {{ date('M') == date('M', mktime(0, 0, 0, $month, 10)) ? 'selected' : ''}}" data-option='{{ $month }}'>
														{{ date('M', mktime(0, 0, 0, $month, 10)) }}
													</div>
												@endfor
											</div>
										</div>
									</div>
									
                                    <div class="col-xs-6 date-combo del-pdg pdg-left-5" id="day-date-combo">
										<div class="wrapper-select select-date">
											<span class="wrapper-header">{{ date('d') }}</span>
											<div class="mjs-select max-height">
												<input type="hidden" value="{{ date('d') }}" name="meeting_day" class="mjs-value" />
												 <div class="mjs-option " data-option=''>@lang('_.Day')</div>
												 @for($day = 1; $day <= 31; $day++)
													 <div class="mjs-option {{ $day == date('d') ? 'selected' : '' }}" data-option='{{ $day }}'>
														{{ $day }}
													</div>
												@endfor
											</div>
										</div>
                                    </div>
									
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-8 col-sm-offset-4 md-del-left-pdg">
									<div>
										<input type="checkbox" name="flexible" class="flexible" id="test1" />
										<label for="test1" class="del-mrg lbl-flexible">@lang('_.I\'m flexible') (+/- 3 @lang('_.days'))</label>
									</div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-sm-4 del-left-pdg">@lang('_.Select gender') *</label>
                                <div class="col-sm-8 del-right-pdg md-del-left-pdg">
                                   <div class="wrapper-select select-date max-width-180 z_index_1">
										<span class="wrapper-header">@lang('_.Choose')</span>
										<div class="mjs-select max-height">
											<input type="hidden" name="gender" value="" class="mjs-value" />
											 <div class="mjs-option " data-option='ALL'>@lang('_.All')</div>
											 <div class="mjs-option " data-option='M'>@lang('_.M')</div>
											 <div class="mjs-option " data-option='F'>@lang('_.F')</div>
										</div>
									</div>
								</div>
                            </div>

                            
                            <div class="form-group">
                                <label class="col-sm-4 del-left-pdg">Select age *</label>
                                <div class="col-sm-8 search-slider pdg-top-10">
                                    <p>
                                        <input  id="ageSlider"
                                                type="text"
                                                class="span2"
                                                value=""
                                                data-slider-min="18"
                                                data-slider-max="99"
                                                data-slider-step="1"
                                                data-slider-value="[18,50]"
                                                rangeHighlights="[{'start' : 18, 'end' : 30}]" />
                                    </p>
                                    <input type="hidden" name="age_min" class="unselectable" id="ageMin" value="18" />
                                    <input type="hidden" name="age_max"  class="unselectable" id="ageMax" value="50" />
                                    <span class="pull-left">
                                        18
                                    </span>
                                    <span class="pull-right">
                                        99
                                    </span>
                                </div>
                               
                            </div>

                            <div class="text form-group del-bottom-mrg">
                                <div class="col-sm-4">

                                </div>
                                <div class="col-sm-8 text-right del-right-pdg md-del-left-pdg">
                                    <button id="btnCreateMeeting" class="btn btn-lg btn-warning btn-find-people del-mrg-all">
                                        @lang('_.FIND PEOPLE')
                                    </button>
                                </div>
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
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key={{ Config::get('services.google_maps.api_key') }}&libraries=places&callback=initService" async defer></script>
<script type="text/javascript">
    var SearchBox;

    function _isValidPlace(place) {
        if(place.name && place.geometry.location) {
            return true;
        }
        return false;
    }

    function processResult(place) {
        if( ! _isValidPlace(place)) {
            alert("Place invalid!");
        }

        $("#fldLatitude").val(place.geometry.location.lat());
        $("#fldLongitude").val(place.geometry.location.lng());
        $("#fldFullAddress").val(place.formatted_address)
    }

    function placeSelected(){
		$("#museumSearch").removeClass('input-invalid');
        var places = searchBox.getPlaces();
        if( places.length != 1 ){
            alert("@lang('_.Please select correct place')!");
        } else {
            processResult(places[0]);
        }
    }

	function initService() {
       var input = document.getElementById('museumSearch');
       if (input)
       {
            searchBox = new google.maps.places.SearchBox(input, { });
            searchBox.addListener('places_changed', placeSelected);
       }
    }
	
	function daysInMonth(month,year) {
		return new Date(year, month, 0).getDate();
	}

    $(function(){
		$("#meeting_month").change(function(){

            var monthIndex = $(this).val();
            var meetingYear = new Date().getYear();

            var currentMonth = (new Date().getMonth()) + 1;

            if(monthIndex > currentMonth){
                meetingYear++;
            }

            var tmpDaysInMonth = daysInMonth(monthIndex,meetingYear);

            var daysComboHtml = "";
            for(let i = 1; i <= tmpDaysInMonth; i++){
                daysComboHtml += "<option>" + i + "</option>";
            }

            $("#meeting_day").html(daysComboHtml);
        });

        $(window).keydown(function(event){
            if(event.keyCode == 13) {
                event.preventDefault();
                return false;
            }
        });
		
        $("#modalSearch").modal('show'); 
		
        $("#formMeeting").submit(function(event){
			event.preventDefault();
			var pData = $("#formMeeting").serialize();
			
			if ( ! $("#fldLatitude").val() || ! $("#fldLatitude").val() )
			{
				$("#museumSearch").addClass('input-invalid');
				showNotification("@lang('_.No selected address')", { color : "#f62217"});
				return false;
			}

            if ( ! $("[name=meeting_month]").val() || ! $("[name=meeting_day]").val())
			{
				$("[name=meeting_month]").parent().paretn().addClass('input-invalid');
                $("[name=meeting_day]").parent().addClass('input-invalid');
				showNotification("@lang('_.The date fields are required!')", { color : "#f62217"});
				return false;
			}
			
			$.ajax({
                url : "/api/meeting/save",
                data : pData,
                type : "post",
                complete : function(response, status){
                    var res = $.parseJSON(response.responseText);
					
					if(res.id)
					{
                        showNotification("@lang('_.System is searching for people. You will be redirected in a few seconds')...", 
							{
								onClose : function(){
									window.location = "/meeting/result/" + res.base_url;
								},
							});
                    }
					else 
					{
                        var laravelErrors = laravelValidateErrorsToArray(res);
                        for(let idx in laravelErrors)
						{
							showNotification(laravelErrors[idx],  {'color' : '#f62217'});
						}
					}
                }
            });
	    });
		
		$("#ageSlider").slider({});
		
		$("#labelAgeMin").text("18");
		
		$("#labelAgeMax").text("50");
		
		$("#ageSlider").on("slide", function(slideEvt) {
			$("#ageMin").val(slideEvt.value[0]);
			$("#ageMax").val(slideEvt.value[1]);
			$("#labelAgeMin").html(slideEvt.value[0]);
			$("#labelAgeMax").html(slideEvt.value[1]);
        });
        
        $("#ageSlider").on("change", function(){
            var value = $("#ageSlider").val().split(',');
            $("#labelAgeMin").html(value[0]);
            $("#labelAgeMax").html(value[1]);
        });
		
		$(".nicescroll").niceScroll({'cursorcolor':"#b0afae", 'railpadding' : {'bottom' : 3, 'top' : 3, 'left' : 5}});
	
	});
       

</script>

@endpush