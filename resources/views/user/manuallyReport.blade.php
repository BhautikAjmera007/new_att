@section('settitle') Manually In-Out {{config('attendance.sitename')}}
@endsection

<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
@section('style') 
@endsection 
 
{{ HTML::style('assets/css/bootstrap-datetimepicker.min.css') }} 
{{ HTML::style('assets/css/bootstrap-combined.min.css') }} 
@extends('layouts.app')
@section('content')
<h1 class="page-header">Manually  In-Out</h1>
@if (Session::has('message'))
    <div class="alert alert-info" id="msg"><h4><b>{{ Session::get('message') }}</b></h4></div>
@endif

 
<div class="view-filters even" style="background-color: #e1eded;height: 280px;border-radius: 0px;">
 
  <form class="form-vertically"   role="form" action="{{ url('/manuallyReportInsert') }}" method="post">
  <div class="row">
      <label for="empname" class="control-label col-sm-2" style="margin-top: 10px;">Employee Name</label>
      <div class="col-sm-3">
         <select class="selectpicker chzn-select" selected="true" data-live-search="true" name="name" id="emp_name">
        <option value="">-- Select Employee --</option> 
         <?php  foreach ($user_name as $key => $value) {?>
             <option value="{{$key}}">{{$value}}</option>
          <?php } ?> 
      </select>
         @if ($errors->has('name'))
                  <div class="validation_error">{{ "Employee name is required" }} </div>
         @endif
      </div> 
  </div>
<div style="margin-left: 196px; color:red;" name="emp_name_msg" id="emp_name_msg"></div> 
  <div class="row" style="margin-top:7px;">
    <label for="date" class="control-label col-sm-2" style="margin-top: 10px;">Date</label>
    <div style="float: right;width: 313px;margin-right: 623px;" > 
      {{ Form::text('date',null,array('class' => 'form-control','id' => 'date','style' => 'height:33px')) }}
    </div>  
      <div class="col-sm-3" style="color:red;margin-left: 377px;margin-top: -45px;" id="maxDate"></div> 
    </div>

<div class="row" style="margin-top:40px;">
    <label for="comment" class="control-label col-sm-2" style="margin-left: -3px;
    margin-top: -36px;">Comment</label>
    <div style="float: right;width: 313px;margin-right: 623px;" > 
      {{ Form::text('comment',null,array('class' => 'form-control','id' => 'comment','style' => 'height:33px;margin-left: -1px;margin-top: -42px;' )) }}
    </div>  
       
    </div>

<div class="row" id="checkin_div">
      <label for="date" class="control-label col-sm-2">Checkin*</label>
      <div class="col-sm-4" id="checkin_time">      
          <div id="checkin" class="input-append">
            <input data-format="hh:mm:ss" type="text" name="checkin" id="checkin1" style="height:33px;width: 190px;"></input>
             <span class="add-on" style="height:33px" id="checkin_btn" class="glyphicon glyphicon-time">
              <i data-time-icon="icon-time" data-date-icon="icon-calendar">
              </i>
            </span>
          </div>
        </div>
        <div class="col-sm-3">
          @if ($errors->has('checkin'))
                  <div class="validation_error" style="color:red;">{{ "Checkin time is required" }} </div>
          @endif

          <div style="color:red;margin-left: -157px;" name="checkin_msg" id="checkin_msg"></div>
        </div>
  </div>
  <div class="row" id="checkout_div">
      <label for="date" class="control-label col-sm-2">Checkout*</label>
      <div class="col-sm-3" id="checkout_time">      
          <div id="checkout" class="input-append">
            <input data-format="hh:mm:ss" type="text" name="checkout" id="checkout1" style="height:33px;margin-top: 4px;width: 190px;"></input>
             <span class="add-on" style="height:33px;margin-top: 4px;" id="checkout_btn">
              <i data-time-icon="icon-time" data-date-icon="icon-calendar">
              </i>
            </span>
          </div>
      </div>
      <div class="col-sm-3">
          @if ($errors->has('checkout'))
            <div class="validation_error" style="color:red;">{{ "Checkout time is required" }} </div>
          @endif
          <div style="color:red;margin-left: -63px;" name="checkout_msg" id="checkout_msg"></div>
      </div>

  </div>
  <div class="row" style="margin-top:10px;">
    <div class="col-sm-offset-2 col-sm-10">
        <button type="submit" class="btn btn-info">Submit</button>
    </div>
  </div>
</form>
</div>
@endsection
@section('script')
{{ HTML::script('assets/js/jquery-ui.js') }}
{{ HTML::script('assets/js/bootstrap-datetimepicker.min.js') }}

<script type="text/javascript">
$('document').ready(function(){
      var currentDate = new Date(); 
      
       $('#comment').keyup(function(){
        var comment = $('#comment').val();
          
          if(comment.length != 0)
          { 
            $('#checkin_div').hide();
            $('#checkout_div').hide();
          }
          else{
            $('#checkin_div').show();
            $('#checkout_div').show();
          }
       });

      $('#date').change(function(){ 
        var today = new Date();
        var dd = today.getDate();
        var mm = today.getMonth()+1; //January is 0! 
        var yyyy = today.getFullYear();
        if(dd<10){
            dd='0'+dd
        } 
        if(mm<10){
            mm='0'+mm
        } 
        today=dd+'/'+mm+'/'+yyyy; 
        var selectedDate=$(this).val();

        if(selectedDate > new Date()){
          $('#date').val("");
          $('#maxDate').text("Can not specify the date which is greter than currrent date");
        }
        else
        {
           $('#maxDate').text(" ");   
        }
        
      }); 
      $('#date').datepicker({ 
          maxDate: new Date,
          inline: true,
          showOtherMonths: true, 
          dayNamesMin: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'], 
          dateFormat: 'dd/mm/yy', 
      }); 
  
      $("#date").datepicker("setDate", currentDate); 

      $('#msg').delay(2000).fadeOut();
 
      $('#checkin').datetimepicker({
        pickDate: false
      });

      $('#checkout').datetimepicker({
        pickDate: false
      });

      $('#emp_name').change(function(){
           var val = $(this).val();
           if(val == 0)
           {
               $('#emp_name_msg').text("Please Select Employee Name");
           }
           else
           {  
              $('#emp_name_msg').text(" ");  
           }
      }); 

      $('#checkin_btn').click(function(){
           var val1 = $('#checkin1').val(); 
           $('#checkin_msg').text(" ");  
        });
    
    $('#checkin1').blur(function(){
       var val1 = $('#checkin1').val();
       if(val1.length == 0)
       {
          $('#checkin_msg').text("Checkin time is required"); 
       } 
     }); 
    $('#checkout_btn').click(function(){
           var val1 = $('#checkout1').val();
           $('#checkout_msg').text(" ");  
      });
    
    $('#checkout1').blur(function(){
       var val1 = $('#checkout1').val();
       if(val1.length == 0)
       {
          $('#checkout_msg').text("Checkout time is required"); 
       }  
     });
});
</script>

@endsection