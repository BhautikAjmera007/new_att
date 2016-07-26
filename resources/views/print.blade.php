 
<html>
<head>
	 {{ HTML::style('assets/css/bootstrap.min.css') }}
</head>
	<body>
		<h1 class="page-header">Monthly Report(<?php echo $year.'-'.$month;?>)</h1>
		<div class="view-content">
  <table class="views-table cols-12 table" >
     <thead>
        <tr>
            <th class="views-field views-field-field-profile-image" >Employee Name</th>
            <th class="views-field views-field-field-profile-image" >Total Hours</th>
            <th class="views-field views-field-field-profile-image" >Leave</th>
            <th class="views-field views-field-field-profile-image" >Other Reason</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($all_record as $key => $value) { ?>  
          <tr class="odd views-row-first">
            <td class="views-field views-field-field-profile-image" >{{$value['name']}}</td>
            <td class="views-field views-field-field-profile-image" >
              <?php if(!isset($value['hrs'])){echo "0";}else{echo $value['hrs'];}?>
            </td>
            <td class="views-field views-field-field-profile-image" >
              <?php if(!isset($value['leave'])){echo "Non";}else{echo $value['leave'];}?>
            </td>
            <td class="views-field views-field-field-profile-image" >
              <?php if(!isset($value['other'])){echo "Non";}else{echo $value['other'];}?>
            </td>
          </tr>
        <?php } ?>

      </tbody>
  </table>
</div> 
	</body>

</html>
<script type="text/javascript">
		window.print();
</script> 
