@section('settitle') By Month {{config('attendance.sitename')}}
@endsection

@extends('layouts.app')
@section('content')
<h1 class="page-header">By Month</h1> 
<div class="view-filters even" style="background-color: #e1eded;height: 106px;border-radius: 0px;">

  <form class="form-inline" id="myform" action="{{ url('/monthBy') }}">
    <div class="row">
      <label class="control-label" for="year" style="margin-left:17px">Year</label>
      <label class="control-label" for="year" style="margin-left:100px">Month</label>
      <label for="name" style="margin-left:680px">Search By Name</label>
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
                    "01" => "January", "02" => "February", "03" => "March", "04" => "April","05" => "May", "06" => "June", "07" => "July", "08" => "August",
                    "09" => "September", "10" => "October", "11" => "November", "12" => "December",
                );
      ?>
      <select name="month" id="month1" class="form-control">
      <option value="0">Select Month</option>
         <?php
            foreach ($MonthArray as $key => $value) {?>
            <option value="{{$key}}">{{$value}}</option>
         <?php } ?>

      </select>
    </div>

    <div class="form-group">
    <button type="submit" class="btn btn-info" style="margin-left:10px" id="apply">Apply</button>
    </div>

    <div class="form-group">
     {{ HTML::link('monthBy/print?year='.$year.'&month='.$month, "Print" , array('class' => 'btn btn-info print fa fa-camera-retro','id' => 'print','style' => 'margin-left:10px'))}}
    </div>

    <div class="form-group" id="search_name" style="margin-left:450px">
      <select class="selectpicker" data-live-search="true" name="empName" id="empName">
          <option value="0">Select Name</option>
        @foreach($all_record as $key => $value)
        <option value="{{$key}}">{{$value['name'] }}</option>
        @endforeach
      </select>
    </div>

  </form>

  <div class="row">
     <div id="year_err" class="col-sm-2" style="color:red;margin-top: -11px;
    margin-left: 7px;"></div>
      <div id="month_err" class="col-sm-2"  style="color:red;margin-top: -11px;margin-left:-58px;"></div>
  </div> 
</div> 
<div>
   <?php if($year == 0 || $month == 0)
          {?>   <h2 style="text-align:center;">Please Enter valid year and month</h2> 
             <?php }
              else{?>
   <table class="views-table cols-12 table view-content" id="tbl_show">
     <thead>
        <tr>
            <th class="views-field views-field-field-profile-image">Employee Name</th>
            <th class="views-field views-field-field-profile-image">Total Hours</th>
            <th class="views-field views-field-field-profile-image">Leave</th>
            <th class="views-field views-field-field-profile-image">Other Reason</th>
        </tr>
      </thead>
     <tbody id="test"> 
        <?php 
          $i=1;
          $count=count($all_record);
         foreach ($all_record as $key => $value){ if($i == 1){?>
                <tr style="background-color:rgb(225,237,237)">  
            <?php }else if($i % 2 == "0"){?>
                 <tr style="background-color:rgb(242,247,247)"> 
            <?php }else if($i % 2 != "0"){ ?>
                 <tr style="background-color:rgb(230,240,239)"> 
            <?php } ?>
            <td class="views-field views-field-field-profile-image" >
               <a href={{'calender?id='.$key}}> {{$value['name'] }}</a>
            </td>
            <td class="views-field views-field-field-profile-image" >
              <?php if(!isset($value['hrs'])){echo "0";}else{echo $value['hrs'];}?>
            </td>
            <td class="views-field views-field-field-profile-image" >
              <?php if(!isset($value['leave'])){echo "None";}else{echo $value['leave'];}?>
            </td>
            <td class="views-field views-field-field-profile-image" >
              <?php if(!isset($value['other'])){echo "None";}else{echo $value['other'];}?>
            </td>
          </tr>
        <?php  $i++;} }?> 
      </tbody>
  </table>
  {!! $all_record->render() !!}
</div> 
@endsection
<style type="text/css">
.print
{
  margin-left: 588px;
}
tr.hover { background-color:#25AAC2; }
</style>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<!-- {{ HTML::script('assets/js/bootstrap-select.js') }} -->
@section('script')
<script type="text/javascript">     
    $('document').ready(function(){
          

  $("#empName").change(function(){

        var id = $(this).val();
        var year = $('#year1').val();  
        var month = $('#month1').val();
          var all_id = id+"/"+year+"/"+month
    $.ajax({
        url:"/searchName/" +all_id ,
        type:'GET',
        success:function(data) {
            console.log(data); 
           $('#test').html(
                    $('<tr style="background-color:#F2F7F7">')
                        .append($('<td>').append(data.name))
                        .append($('<td>').append(data.hrs)) 
                        .append($('<td>').append(data.leave)) 
                        .append($('<td>').append(data.other)) 
                ); 
        }
    });
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
           $('#year1 option[value="'+currentYear+'"]').prop('selected', true);
           $("#month1 option:contains(" + currentMonth + ")").attr('selected', 'selected');
      } 

       
        
      

    $('#year1').change(function(){  
        var val=$(this).val();
        var d = new Date(); 
        var current_year=d.getFullYear(); 

        var val1=$('#month1').val();
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
  }); 
</script>
@endsection

