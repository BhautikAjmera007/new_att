@section('settitle') Calender {{config('attendance.sitename')}}
@endsection

@section('style')
{{ HTML::style('assets/css/fullcalendar.css') }}

<style type="text/css">
 .fc-mon, .fc-tue, .fc-wed, .fc-thu, .fc-fri, .fc-sat, .fc-sun {
  background-color: rgb(51,122,183);

 }
</style>
@endsection

@extends('layouts.app')
@section('content')

<h1 class="page-header">Calender</h1>

<div id='calendar'></div>

@endsection

@section('script')
{{ HTML::script('assets/js/moment.min.js') }}
{{ HTML::script('assets/js/fullcalendar.min.js') }}
<script> 
$(document).ready(function(){  
 
    var referrer = document.referrer; 

    if(referrer.indexOf("/monthBy?year") > -1)
    {
        var arr = referrer.split('year=');
        var arr1 = arr[1].split('&'); 
        var year = arr1[0]; 
        var month_arr = arr1[1].split('month=');
        var date = moment(year+"-"+month_arr+"-01"); 
    }
    else if(referrer.indexOf("/day_report?year") > -1)
    {
        var arr = referrer.split('year=');
        var arr1 = arr[1].split('&'); 
        var year = arr1[0]; 
        var month_arr = arr1[1].split('month=');
        var date = moment(year+"-"+month_arr+"-01"); 
    }
    else{
        var date = new Date();
    }



  var url="<?php echo $url; ?>";

  if(url != "1"){
       history.pushState({},null,url);  
      }


   $('#emp').change(function(){
         var val=$(this).val();
         window.location='calender?id=' + val
   });

 // Get Current Date

  
    
  $('#calendar').fullCalendar({ 
   dayRender: function (date, cell) {
       cell.css("background-color", "white");
   },

   header: {
    left: 'prev,next today',
    center: 'title',
    right: 'month,basicWeek,basicDay'
   },
    
   defaultView: 'month',
   firstDay: 1,   //To Start week from Monday
   defaultDate: date,
   editable: false,

   businessHours: true, 
   events:"{{ url('/myevent')}}/{!! $id !!}",
   eventRender: function (event, element, monthView) { 
        element.find('.fc-title').append("<br/>" + event.description); 
       }
});
}); 

 

</script>
 
@endsection