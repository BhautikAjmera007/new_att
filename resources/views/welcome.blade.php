<!DOCTYPE html>
<html>
<head>
{{ HTML::style('assets/css/bootstrap.min.css') }}
</head>
<body>
	<div class="container">
		<div class="row">
			<div class="col-sm-12">
				<span class="text-left"><h4><b>Hi Employee Name,</b></h4></span>
			</div>
		</div>

		<div class="row">
			<div class="col-sm-12">
				We have noted that there is no biometric punch registered for the date <?php print_r(date('d/m/Y')); ?>. Should you have availed leave/work from home/work from client location on this date, please add relevant request in HRMS portal.
			</div>
		</div>

		<div class="row" style="margin-top:10px">
			<div class="col-sm-12">
				For any additional queries please get in touch with HR Team or write to hr@knowarth.com 
			</div>
		</div>

		<div class="row" style="margin-top:10px">
			<div class="col-sm-12">
				Thank you.</br>
				HR Team</br>
				KNOWARTH</br>
			</div>
		</div>
	</div>
</body>
</html>

