<!DOCTYPE html>
<head>
	<meta charset="utf-8">
	{{ HTML::style('assets/css/bootstrap.min.css') }}
</head>
<body>
	<div class="row">
		<h3><u>Absent Employee Details</u></h3>
	</div>

	<div class="row">
		<div class="table-responsive">
			<table class="table table-hover">
				<thead>
					<tr class="text-center">
						<th>Name</th>
						<th>Email ID</th>
						<th>Employee Code</th>
				</tr>
				<?php
				foreach ($data as $key => $value) { ?>
				<tr>
						<td>{{$value[0]}}</td>
						<td>{{$value[2]}}</td>
						<td>{{$value[1]}}</td>
				</tr>
				<?php } ?>
		</table>
	</body>

</html>