@extends('layouts.app')
@section('content')	
<div class="table-responsive">
<table class="table table-hover">
    <thead>
    <tr class="success">
        <td  class="text-center" colspan="4"><h2>Monthly Report</h2>
        </td>
    </tr>
      <tr class="success">
        <th class="text-center">Employee Name</th>
        <th class="text-center">Total Hours</th>
        <th class="text-center">Leave</th>
        <th class="text-center">Other Reason</th> 
      </tr>
    </thead>
    	<tbody>

    	<?php foreach ($all_record as $key => $value) { 
	 	  ?>	
          <tr class="success">
    		 <td class="text-center">{{$value['name']}}</td>
    		 <td class="text-center"><?php if(!isset($value['hrs'])){echo "0";}else{echo $value['hrs'];}?></td>
    		 <td class="text-center"><?php if(!isset($value['leave'])){echo "Non";}else{echo $value['leave'];}?></td>
         <td class="text-center"><?php if(!isset($value['other'])){echo "Non";}else{echo $value['other'];}?></td>
           </tr>
    	<?php } ?>
   
    </tbody>

</table>
</div>
@endsection