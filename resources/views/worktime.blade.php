@section('settitle') Work Time {{config('attendance.sitename')}}
@endsection

@extends('layouts.app')

@section('content')
<h1 class="page-header">Work time</h1> 
<div class="view-filters even" style="background-color: #e1eded;height: 80px;border-radius: 0px;">

  <form class="form-inline" id="myform" action="{{ url('#') }}">
    <div class="row">
      <label for="name" style="margin-left:18px">Search By Name</label>
    </div>

    <div class="form-group" id="search_name">
      <select class="selectpicker" data-live-search="true" name="empName" id="empName">
          <option value="">Select Name</option>
          @foreach($worktime as $key => $value)
          	<option value="{{$key}}">{{$value['name']}}</option>
          @endforeach
      </select>
    </div>

  </form>
</div> 

<div>
   <table class="views-table cols-12 table view-content" id="tbl_show">
     <thead>
        <tr>
            <th class="views-field views-field-field-profile-image">Employee Code</th>
            <th class="views-field views-field-field-profile-image">Employee Name</th>
            <th class="views-field views-field-field-profile-image">From Date</th>
            <th class="views-field views-field-field-profile-image">To Date</th>
            <th class="views-field views-field-field-profile-image">Settime</th>
            <th class="views-field views-field-field-profile-image">State</th>
        </tr>
      </thead>     

      <tbody> 
        <?php 
         $i=1;
         $count=count($worktime);
         foreach ($worktime as $key => $value){ if($i == 1){?>
                <tr style="background-color:rgb(225,237,237)">  
            <?php }else if($i % 2 == "0"){?>
                 <tr style="background-color:rgb(242,247,247)"> 
            <?php }else if($i % 2 != "0"){ ?>
                 <tr style="background-color:rgb(230,240,239)"> 
            <?php } ?>
            <td class="views-field views-field-field-profile-image" >
               {{$key}}
            </td>

            <td class="views-field views-field-field-profile-image" >
              {{$value['name']}}
            </td>

            <td class="views-field views-field-field-profile-image" >
              {{$value['fromdate']}}
            </td>

            <td class="views-field views-field-field-profile-image" >
              {{$value['todate']}}
            </td>

            <td class="views-field views-field-field-profile-image" >
              {{$value['settime']}}
            </td>

            <td class="views-field views-field-field-profile-image" >
              {{$value['state']}}
            </td>
          </tr>
        <?php  $i++;}?> 
      </tbody>

  </table>
</div>
@endsection

@section('script')
<script type="text/javascript">
$('document').ready(function(){
	$("#empName").change(function(){  
       filter();
    }); 

    function filter(){
		inp = $('#empName').val()
	    $("tr:not(:has(>th))").each(function() {
	        if (~$(this).text().toLowerCase().indexOf( inp.toLowerCase() ) ) {
	            $(this).show();
	        } else {
	            $(this).hide();
	        }
	    });
	}
});
</script>
@endsection