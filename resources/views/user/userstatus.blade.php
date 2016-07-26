<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">

@section('settitle') By Status {{config('attendance.sitename')}}
@endsection

@extends('layouts.app')

<style type="text/css">
	tr.hover { background-color:#25AAC2; }
</style>

@section('content')
<h1 class="page-header">By Status</h1>
<div class"row">
	<div class="view-filters even" style="background-color: #e1eded;height: 88px;border-radius: 0px;">

		  <!-- Form Start -->
		{{ Form::open(array('url' => '/status', 'method' => 'post', 'class' => 'form-inline')) }}
	   	{{ csrf_field() }}
		    <div class="row">
		      <label class="control-label" for="year" style="margin-left:17px">Year</label>
		      <label class="control-label" for="year" style="margin-left:100px">Month</label>
		      <label for="name" style="margin-left:90px">Status</label>
		      <!-- <label for="name" style="margin-left:70px">Name</label> -->
		      <label for="name" style="margin-left:485px">Date</label>
		    </div>

		    <div class="form-group">
		    	<?php
			      $current_date=date('Y-m-d'); 
			      $y  = date('Y',strtotime($current_date)); 
			      $yearArray = range(2014,$y);  
			     ?>
			     <select class="form-control" name="fromDate" id="fromDate" autofocus>
			        <option value="0">Select Year</option>
			        <?php
			        foreach ($yearArray as $key => $value ) {  if($value > $y){?>   
			           <option value="{{$value}}" disabled>{{$value}}</option>
			         <?php } else{?> 
			               <option value="{{$value}}">{{$value}}</option>
			        <?php }}
			        ?>
			    </select>
		    </div>

		    <div class="form-group" style="margin-left:10px">
		    	<?php
			            $MonthArray = array(
			                    "01" => "January", "02" => "February", "03" => "March", "04" => "April","05" => "May", "06" => "June", "07" => "July", "08" => "August",
			                    "09" => "September", "10" => "October", "11" => "November", "12" => "December",
			                );
			      ?>
			      <select name="toDate" id="toDate" class="form-control">
			      <option value="0">Select Month</option>
			         <?php
			            foreach ($MonthArray as $key => $value) {?>
			            <option value="{{$key}}">{{$value}}</option>
			         <?php } ?> 
			      </select>
		    </div>

		    <div class="form-group">
			    <select class="form-control" name="status" id="drpdispstatus">
			  		<option value="Pending">Pending</option>
			  		<option value="Complete">Complete</option>
			  		<option value="All">All</option>
			  	</select>
		    </div>

		    <!-- 
		    <div class="form-group selectpicker" data-live-search="true">
			  	<select class="selectpicker" data-live-search="true" name="searchEmpName" id="searchEmpName">
			        <option value="">Select Name</option>
			        @foreach($rec as $key => $value)
			        	<option value="">{{$rec[$key]['name']}}</option>
			  		@endforeach
			      </select>
		    </div>
			 -->
		    <div class="form-group">
		    	<button type="submit" class="btn btn-info" id="apply" style="margin-left:10px">Apply</button>
		    </div>

		    <div class="form-group" style="margin-left:340px;">
		    	{{ Form::text('dateFilter',null,array('class' => 'form-control','id' => 'dateFilter')) }}

		    	<button type="button" class="btn btn-info" id="applyByDate" style="margin-left:10px">Apply</button>
		    </div>
  		{{ Form::close() }}
		<!-- Form End -->

	</div>
	<!-- Table Start -->
	@if(count($rec) == "0")
		<span class="text-center"><h3><b>{{"No Record Found"}}</b></h3></span>
	@else
		<div class="table-responsive">
			<table class="views-table cols-12 table view-content">
				<thead>
					<tr>
						<th class="text-center views-field views-field-field-profile-image">No</th>
						<th class="text-center views-field views-field-field-profile-image">Employee Code</th>
						<th class="text-center views-field views-field-field-profile-image">Name</th>
						<th class="text-center views-field views-field-field-profile-image">Date</th>
						<th class="text-center views-field views-field-field-profile-image">Status</th>
						<th class="text-center views-field views-field-field-profile-image">WFH</th>
						<th class="text-center views-field views-field-field-profile-image">WFC</th>
						<th class="text-center views-field views-field-field-profile-image">Leave</th>
						<th class="text-center views-field views-field-field-profile-image">Action</th>
					</tr>
				</thead>

				<tbody>
					<?php $i="1"; $count=count($rec);?>
					@foreach($rec as $key => $value)
						@if($i == 1)
							<tr id="tr_<?php $rec[$key]['emp_code'] ?>" style="background-color:rgb(225,237,237)">
						@elseif($i == $count)
							<tr id="tr_<?php $rec[$key]['emp_code'] ?>" style="background-color:rgb(242,247,247)">
						@elseif($i % 2 == "0")
							<tr id="tr_<?php $rec[$key]['emp_code'] ?>" style="background-color:rgb(242,247,247)">
						@elseif($i % 2 != "0")
							<tr id="tr_<?php $rec[$key]['emp_code'] ?>" style="background-color:rgb(230,240,239)">
						@endif

						<td class="text-center">{{$i}}</td>

						<td class="text-center">
							{{$rec[$key]['emp_code']}}
							<input type="hidden" id="hid_empcode<?php echo $rec[$key]['emp_code']; ?>" value="<?php echo $rec[$key]['emp_code']; ?>">
						</td>

						<td class="text-left">
							{{$rec[$key]['name']}}
							<input type="hidden" id="hid_name<?php echo $rec[$key]['emp_code']; ?>" value="<?php echo $rec[$key]['name']; ?>">
						</td>

						<td class="text-center">
							<?php print_r(date("d-m-Y", strtotime($rec[$key]['date']))); ?>
						<input type="hidden" id="hid_date<?php echo $rec[$key]['emp_code']; ?>" value="<?php echo $rec[$key]['date']; ?>">
						</td>

						<td>
							{{$rec[$key]['status']}}
							<input type="hidden" id="hid_status<?php echo $rec[$key]['emp_code']; ?>" value="<?php echo $rec[$key]['status']; ?>">
						</td>

						<td class="text-center">
							<?php if(isset($rec[$key]['wfh'])){ 
										print_r($rec[$key]['wfh']);
								  }else{ echo " ";}
							 ?>
						</td>
						<td class="text-center">
							<?php if(isset($rec[$key]['wfc'])){ 
										print_r($rec[$key]['wfc']);
								  }else{ echo " ";}
							 ?>
						</td>
						<td class="text-center">
							<?php if(isset($rec[$key]['leave'])){ 
										print_r($rec[$key]['leave']);
								  }else{ echo " ";}
							 ?>
						</td>

						<td class="text-center">
							<a class="btn btn-primary editstatus" data-toggle="modal" href="javascript:void(0);" data-id="<?php echo $rec[$key]['emp_code']; ?>" data-target="#updateRecModel">Edit</a>
						</td>
					</tr>
					<?php $i++; ?>
					@endforeach
				</tbody>

			</table>
		</div>
		<!-- Table End -->
		<!-- < ?php echo $rec->render(); ?> -->
	</div>
	@endif
@endsection

@section('script')
{{ HTML::script('assets/js/jquery-ui.js') }}
<script type="text/javascript">
  	$('document').ready(function(){
  		var dstatus=<?php echo $dateStatus; ?>;
  	
  		// Display Current date in dd/mm/yy From Date
  		$( "#dateFilter" ).datepicker().datepicker("setDate", new Date());
		$( "#dateFilter" ).datepicker('option', 'dateFormat' , 'dd/mm/yy');

  		$('#applyByDate').click(function(){
  			var date = $('#dateFilter').val();
  			url = "/status?date=" + date;
       		window.location.replace(url); 
  		});

	   var monthNames =[ "January", "February", "March","April", "May", "June",
      "July", "August", "September","October", "November", "December"]; 

      var currentYear = (new Date).getFullYear();
      var currentMonth = monthNames[(new Date).getMonth()]; 

      if(window.location.href.indexOf("year") > -1) 
            {
                $("select option[value="+$.cookie('drp_valuey')+"]").attr("selected","selected");
                $("select option[value="+$.cookie('drp_value1')+"]").attr("selected","selected");
            }
      else{
           $('#fromDate option[value="'+currentYear+'"]').prop('selected', true);
           $("#toDate option:contains(" + currentMonth + ")").attr('selected', 'selected');
      } 

  		if(dstatus == "1"){
	  		$("#fromDate").datepicker("setDate", $.cookie('fromDate'));
	  		$("#toDate").datepicker("setDate", $.cookie('toDate'));
	  		$('#drpdispstatus').val($.cookie('dispStatus')).attr("selected", "selected");
  		}else if(dstatus == "0"){
	  		var currentDate = new Date();
	  		$("#fromDate").datepicker("setDate", currentDate);	  		
	  		$("#toDate").datepicker("setDate", currentDate);
	  		$('#drpdispstatus').val("Pending").attr("selected", "selected");	
  		}

  		// Set Data into the model
	  	$(".editstatus").click(function(){
			var d_id=$(this).data('id');
			var hidStatus = $("#hid_status"+d_id).val();

			var convertedDate = $("#hid_date"+d_id).val();
			convertedDate = convertedDate.substr(0, 10).split("-");
			convertedDate = convertedDate[2] + "-" + convertedDate[1] + "-" + convertedDate[0];

			$("#empcode").val($("#hid_empcode"+d_id).val());
			$("#empname").val($("#hid_name"+d_id).val());
			$("#date1").val(convertedDate);
			$("#leavestatus").val(hidStatus);
		});

	  	// Click on apply button store fromdate and todate in cookie
		$('#apply').click(function(){
			var fromDate=$("#fromDate").val(); 
			var toDate=$("#toDate").val(); 
			var dispStatus=$("#drpdispstatus").val(); 
			
			$.cookie('fromDate',fromDate);
			$.cookie('toDate',toDate);
			$.cookie('dispStatus',dispStatus);
		});

		/*$("tr.data").mouseover(function() {
            $(this).css('background-color', '#25AAC2');
        }).mouseout(function() {
            $(this).css('background-color', 'transparent');
        });*/

  	});
</script>
@endsection

<!-- Mode Start -->
<div class="modal fade" id="updateRecModel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">

  <div class="modal-dialog">  
    <div class="modal-content">

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="exampleModalLabel">Update Status</h4>
      </div>

      <div class="modal-body">
        {{ Form::open(array('url' => '/updatestatus', 'method' => 'post', 'class' => 'form-horizontal')) }}
   		{{ csrf_field() }}

   		  <div class="form-group">
          	{{ Form::label('empcode', 'Employee Code', array('class' => 'control-label')) }}
		    <div class="col-sm-6">
		    {{ Form::text('empcode',null,array('class' => 'form-control','id' => 'empcode','readonly')) }}
		    </div>
		  </div>

          <div class="form-group">
          	{{ Form::label('name', 'Name', array('class' => 'control-label')) }}
		    <div class="col-sm-6">
		    {{ Form::text('empname',null,array('class' => 'form-control','id' => 'empname','readonly')) }}
		    </div>
		  </div>

		  <div class="form-group">
          	{{ Form::label('date', 'Date', array('class' => 'control-label')) }}
		    <div class="col-sm-6">
		    {{ Form::text('date1',null,array('class' => 'form-control','id' => 'date1','readonly')) }}
		    </div>
		  </div>

		 <div class="form-group">
		    {{ Form::label('status', 'Status', array('class' => 'control-label')) }}
		    <div class="col-sm-6">
		      <select class="form-control" name="leavestatus" id="leavestatus">
			  		<option value="Pending">Pending</option>
			  		<option value="Complete">Complete</option>
			  </select>
		    </div>
		 </div>

		 <div class="form-group">
		    {{ Form::label('comment', 'Comment', array('class' => 'control-label')) }}
		    <div class="col-sm-6">
		      <textarea class="form-control" cols="5" rows="5" name="comment"></textarea>
		    </div>
		 </div>
      </div>

      <div class="modal-footer">
		<button type="submit" class="btn btn-primary">Save Changes</button>
		{{Form::close()}}
		<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>
	<!-- Mode End -->