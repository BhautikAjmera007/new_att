 
<html>
<head>
	 {{ HTML::style('assets/css/bootstrap.min.css') }}
</head>
	<body> 
		<div class="view-content">

  			<table class="views-table cols-12 table">
     			<thead>
         			<tr>
         					<th>Employee Name</th>
         					<?php 
         						$d=cal_days_in_month(CAL_GREGORIAN,$month,$year);
      							for($i=1;$i<=$d;$i++){
      						?>
         						<th class="views-field views-field-field-profile-image">{{$i}}</th>
         					<?php } ?>
         			</tr>
              <tr>
         			<?php foreach ($all_record_report_day as $key => $value) { ?>
      				<td>
      					{{$key}}
      				</td>

      				<?php foreach ($value as $key1 => $value1) { ?>
      					<?php for($i=1;$i<=$d;$i++){ 
      						if($i == date("j",strtotime($key1))) { ?>
      							<td>{{$value1[0]}}</td>
      						<?php }?>
      						 
      					<?php  }?>
      				<?php } ?>
      	   </tr>
      	<?php }?> 	
      			</thead>
      			
  </table>


</div> 
</body>

</html>
@section('script')
<script type="text/javascript">
  var legandcolors = ["WFC","WFH"];
  var legandtext = ["Work From Client","Work From Home"];
  
  for(i=0;i<legandcolors.length;i++) {
    $("#legand").append("<div class='col-lg-2' style='background-color:white'><span>"+ legandtext[i] +"</span><div class='legand' style='background-color:"+legandcolors[i] +"''></div></div>");
  }
  </script>
@endsection


