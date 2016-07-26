@section('settitle') By Day {{config('attendance.sitename')}}
@endsection

@extends('layouts.app')
@section('content')
<h1 class="page-header">By Day</h1>
<div class="view-filters even" style="background-color:#e1eded;height: 99px;border-radius: 0px;">

  <form class="form-inline" action="{{ url('/day_report') }}" >
    <div class="row">
      <label class="control-label" for="year" style="margin-left:17px">Year</label>
      <label class="control-label" for="year" style="margin-left:100px">Month</label>
      <label for="name" style="margin-left:705px">Search By Name</label>
    </div>

    <div class="form-group">
      <?php
     $current_date=date('Y-m-d'); 
      $y  = date('Y',strtotime($current_date));  
      $yearArray = range(2014,$y);
    ?>
     <select class="form-control" name="year" id="year1" autofocus>
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
                    "01" => "January", "02" => "February", "03" => "March", "04" => "April",
                    "05" => "May", "06" => "June", "07" => "July", "08" => "August",
                    "09" => "September", "10" => "October", "11" => "November", "12" => "December",
                );
      ?>
      <select name="month" id="month1" class="form-control">
      <option value="">Select Month</option>
         <?php
            foreach ($MonthArray as $key => $value) {?>
            <option value="{{$key}}">{{$value}}</option>
         <?php } ?>

      </select>
    </div>

    <div class="form-group">
    <button type="submit" class="btn btn-info" style="margin-left:10px" id="apply">Apply</button>
    </div>

    <div class="form-group" id="search_name" style="margin-left:540px">
      <select class="selectpicker" data-live-search="true" name="empName" id="empName">
         <option value="0">Select Name</option>
        @foreach($all_record_report_day as $key => $value){
        <option value="{{$key}}">{{$key}}</option>
        @endforeach
      </select>
    </div>

  </form>


  <div class="row">
     <div id="year_err" class="col-sm-2" style="color:red;margin-top: -15px;margin-left: 7px;"></div>
      <div id="month_err" class="col-sm-2"  style="color:red; margin-top: -15px;margin-left:-58px;"></div>
  </div> 
</div>

<div class="row">
  <!--  -->
  <div style="overflow: auto; height:270px;white-space: nowrap;margin-left: 15px;margin-right: 12px" class="view-content showstatus">
    <div class="table-responsove">
    <table class="views-table table" id="tblData">
     
        <?php if(empty($all_record_report_day))
            { ?>
              <h3 style="text-align:center;">Not Data Found</h3>
            <?php }
            else{ ?>
          <tr> 
            <th class="views-field views-field-field-profile-image view-content">Employee Name</th>
              <?php  
                $d=cal_days_in_month(CAL_GREGORIAN,$month,$year);
                for($i=1;$i<=$d;$i++){
              ?> 
                <th class="views-field views-field-field-profile-image view-content" style="border: 1px solid #C0DADB; padding: 5px 5px">{{$i}}</th>
              <?php } ?>
          </tr id="tr_day"> 
              <?php $count =0;$j=1;
              foreach($all_record_report_day as $key => $value){
                if($j == 1){?>
                  <tr style="background-color:rgb(225,237,237)"> 
              <?php }else if($j % 2 == "0"){?>
                 <tr style="background-color:rgb(242,247,247)">
              <?php }else if($j % 2 != "0"){?>
                 <tr style="background-color:rgb(230,240,239)"> 
              <?php }?> 
              <td class="view-content">
                <a href={{'calender?id='.$name_code[$count][0] }}>{{$key}}</a> 
              </td>   
              <?php $count++;$j++;foreach ($value as $key1 => $value1) { ?>
                <?php for($i=1;$i<=$d;$i++){ 
                  if($i == date("j",strtotime($key1))) { ?>
                    <td  style="border: 1px solid #C0DADB" class="view-content">{{$value1[0]}}</td>
                  <?php }?> 
                <?php  }?>
              <?php } ?>
        </tr>
        <?php }}?>  
    
  </table>
 
</div>
  </div>
  <!--  -->
</div>
<?php if(!empty($all_record_report_day)){ ?>
 <div class="row view-content" style="margin-left:398px;padding-top: 30px;" id="legand"></div>
 <?php }?>
@endsection
<style type="text/css">
.print
{
  margin-left: 588px;
}

</style>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<!-- {{ HTML::script('assets/js/bootstrap-select.js') }} -->
@section('script')
  
<script type="text/javascript">   
    $("#legand").append("<div class='col-lg-3'><span><b>WFH</b>--</span>Work From Home</div>");
    $("#legand").append("<div class='col-lg-3'><span><b>WFC</b>--</span>Work From Client</div>");
    
 $('document').ready(function(){
 
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

          $('#year1 option[value="'+currentYear+'"]').prop('selected', true);
          $("#month1 option:contains(" + currentMonth + ")").attr('selected', 'selected');
      } 
    $('#year1').change(function(){
        var val=$(this).val(); 
        if(val != "") {
          $.cookie('drp_valuey',val);
        }
         if(val == 0)
        {   
           $('#apply').prop('disabled', true); 
           $('#year_err').text("please enter year");
        }
        else{
          $('#apply').prop('disabled', false); 
          $('#year_err').text(" "); 
         }

        });
     $('#month1').change(function(){
        var val=$(this).val(); 
        var val1=$('#year1').val();
        if(val != "") {
          $.cookie('drp_value1',val);
        }
        if(val == 0)
        {   
           $('#apply').prop('disabled', true); 
           $('#month_err').text("please enter month"); 
        }
        else{ 

          $('#month_err').text(" ");
        }
        if(val != 0 && val1 != 0)
        {
              $('#apply').prop('disabled', false); 
        }
       });

  $("#empName").change(function(){  
       filter();
    }); 

  });
 
function filter(){
    inp = $('#empName').val()
    // This should ignore first row with th inside
    $("tr:not(:has(>th))").each(function() {
        if (~$(this).text().toLowerCase().indexOf( inp.toLowerCase() ) ) {
            // Show the row (in case it was previously hidden)
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

</script>

@endsection