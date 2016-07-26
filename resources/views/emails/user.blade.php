<!DOCTYPE html>
<html>
<head>
{{ HTML::style('assets/css/bootstrap.min.css') }}
</head>
<body>
	<div class="container">
		<div class="row">
			<div class="col-sm-12">
				<span class="text-left">Hi {{ session()->get('absentEmployeeName') }},</span>
			</div>
		</div>

		<div class="row">
			<div class="col-sm-12">
				We have noted that there is no biometric punch registered for the date @if(!empty(session()->get('reminderDate'))) {{session()->get('reminderDate')}} @else {{date('d/m/Y',strtotime("-1 days"))}} @endif. Should you have availed leave/work from home/work from client location on this date, please add relevant request in HRMS portal.
			</div>
		</div>

		<div class="row" style="margin-top:10px">
			<div class="col-sm-12">
				For any additional queries please get in touch with HR Team or write to hr@knowarth.com 
			</div>
		</div>

	    <div class="row" style="margin-top:10px">
	      <div class="col-sm-12">
	        Thank you.
	      </div>
	    </div>

	    <div class="row">
	      <div class="col-sm-12">
	        HR Team
	      </div>
	    </div>

	    <div class="row">
	      <div class="col-sm-12">
	        KNOWARTH
	      </div>
	    </div>
	</div>
</body>
</html>

