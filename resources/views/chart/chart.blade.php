@section('settitle') Chart {{config('attendance.sitename')}}
@endsection

<?php 
  // $users_data is set when loggedin user is HR
  if(!isset($users_data)){
    // If in the query string Id parameter value is set
    if(isset($_REQUEST['id'])){
      if($_REQUEST['id'] != \Auth::user()->employee_code){
        $url=str_replace($_REQUEST['id'],\Auth::user()->employee_code,$_SERVER['REQUEST_URI']);
      }else if($_REQUEST['id'] == \Auth::user()->employee_code){
        $url="1";
      }
    }else if(!isset($_REQUEST['id'])){
      $url="1";
    }
  }else if(isset($users_data)){
    $url="1";
  }
?>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css">

@section('style')
<style type="text/css">
tr.hover { background-color:#25AAC2; }
</style>
@endsection

<?php  
      if(isset($nextMonthName) && isset($nextYear)){
          $currYear=$nextYear;
          $currMonth=$nextMonthName;
          $temp=$currYear.'-'.$currMonth.'-'.'01';

          $currMonthNum= date('m', strtotime("+1 month", strtotime($temp)));
      }else if(isset($previousMonthName) && isset($previousYear)){
          $currYear=$previousYear;
          $currMonth=$previousMonthName;
          $temp=$currYear.'-'.$currMonth.'-'.'01';

          $currMonthNum= date('m', strtotime("-1 month", strtotime($temp)));
      }else{
          $currYear=date('Y');           
          $currMonth=date('M');
          $temp=$currYear.'-'.$currMonth.'-'.'01';

          $currMonthNum= date('m', strtotime("+1 month", strtotime($temp)));
          $previousMonthNumber= date('m', strtotime("-1 month", strtotime($temp)));
      }
?>  
@extends('layouts.app') 
@section('content')

<h1 class="page-header">Chart</h1>

  <?php $employee_code =isset($_REQUEST['id'])?$_REQUEST['id']:$_SESSION["id"];?>
<div class="month_nextprev">
  <div class="row">
    <div class="col-sm-2 pull-left" style="margin-left:20px;margin-top:10px;">
        <div class="btn-group">
        <!-- Next Previous Button Start -->
            <!-- Previous Button Start--> 
            @if(isset($previousMonthNumber))
                {{ HTML::link('chart/previous?id='.$employee_code.'&month='.$previousMonthNumber.'&year='.$currYear, "" , array('class' => 'btn btn-default fa fa-chevron-left fa-2x','style' => 'background-color:#f3f8f8','id' => 'previous'))}}
            @else
                @if(isset($_REQUEST['month']) && $_REQUEST['month'] == "01")
                  <?php $n=$currYear - 1;?>
                  {{ HTML::link('chart/previous?id='.$employee_code.'&month=12&year='.$n, "" , array('class' => 'btn btn-default fa fa-chevron-left fa-2x','style' => 'background-color:#f3f8f8','id' => 'previous'))}}
                @else
                  <?php if(isset($_REQUEST['month'])){ $currMonthNum = $_REQUEST['month'] - 1; }?>
                  {{ HTML::link('chart/previous?id='.$employee_code.'&month='.$currMonthNum.'&year='.$currYear, "" , array('class' => 'btn btn-default fa fa-chevron-left fa-2x','style' => 'background-color:#f3f8f8','id' => 'previous'))}}
                @endif
            @endif
            <!-- Previous Button End--> 

            <!-- Next Button Start-->
            @if(isset($_REQUEST['month']) && $_REQUEST['month'] == "12")
                <?php $n=$currYear + 1;?>
                {{ HTML::link('chart/next?id='.$employee_code.'&month=01&year='.$n, "" , array('class' => 'btn btn-default fa fa-chevron-right fa-2x','style' => 'background-color:#f3f8f8','id' => 'next'))}}
            @else
                <?php if(isset($_REQUEST['month'])){ $currMonthNum = $_REQUEST['month'] + 1; }?>
                {{ HTML::link('chart/next?id='.$employee_code.'&month='.$currMonthNum.'&year='.$currYear, "" , array('class' => 'btn btn-default fa fa-chevron-right fa-2x','style' => 'background-color:#f3f8f8','id' => 'next'))}}
            @endif
            <!-- Next Button End  -->
        <!-- Next Previous Button End -->
  
        </div>    
    </div>

    <!-- Display Chart Heading Start -->
    @if(isset($_REQUEST['year']))
      <div class="col-sm-5 col-sm-offset-3">  
          <span><h2>{{$currMonth." ".$_REQUEST['year']}}</h2></span>
       </div>
    @else
      <div class="col-sm-5 col-sm-offset-3">  
        <span><h2>{{$currMonth." ".$currYear}}</h2></span>
      </div>
    @endif
    <!-- Display Chart Heading End --> 
  </div>
</div>

  <div id="chartContainer" style="height: 400px; width: 100%;"></div>
  <div style="background-color: white;margin-right: -48px;" id="legand"></div>

  <?php 

  if(isset($inout_rpt_data) && !empty($inout_rpt_data))
  { 
  ?> 
  <div class="row" style="margin-top:30px">
    <div class="text-center">          
      <span><h3><?php echo $currMonth." ".$currYear; ?> In-Out Report</h3></span>
    </div>
  </div>

    <div class="table-responsive">
        <table class="views-table view-content cols-12 table" style="background-color: white">
            <thead>
              <tr bgcolor = "#FFFFFF">
                  <th class="text-left views-field views-field-field-profile-image">Date</th>
                  <th class="text-left views-field views-field-field-profile-image" >Type</th>
                  
                  <?php if($boolManuallyInoutStatus == "1"){ ?>
                    <th class="text-center views-field views-field-field-profile-image" >Set Time</th>
                  <?php } ?>
                  
                  <th class="text-center views-field views-field-field-profile-image" >In Time</th>
                  <th class="text-center views-field views-field-field-profile-image" >Out Time</th>
                  <!-- 
                  <th class="text-center views-field views-field-field-profile-image" >Hours</th>
                  -->
                  <th class="text-center views-field views-field-field-profile-image" >Time(HH:MM:SS)</th>
              </tr>
            </thead> 
            <tbody>
              <?php $i=1;?>
              @foreach($inout_rpt_data as $key => $value)
              @foreach($value as $key1 => $value1)
                <?php if($i == 1){?>
                      <tr style="background-color:rgb(225,237,237)"> 
                <?php }else if($i % 2 == 0){?>
                      <tr style="background-color:rgb(242,247,247)">  
                <?php }else if($i % 2 != 0){?>
                      <tr style="background-color:rgb(230,240,239)"> 
                <?php }?>      
                      <td class="text-left views-field views-field-field-profile-image">{{$key1}}</td>

                      <td>{{$value1['type']}}</td>

                      <?php if($boolManuallyInoutStatus == "1"){ ?>
                      <td>
                          @if(isset($value1['setWorktime']))
                              {{ $value1['setWorktime'] }} 
                          @endif
                      </td>
                      <?php } ?>

                      <td class="text-center views-field views-field-field-profile-image">
                        @if(count($value1['checkin']) > 1)
                          @foreach ($value1['checkin'] as $key11 => $value11) 
                            {{ $value11 }} <br>
                          @endforeach               
                          @else 

                            <?php if($boolManuallyInoutStatus == "1"){ ?>
                              @if(isset($value1['late']))
                              <span style="color:red">{{ $value1['checkin'] }}</span>
                              @else
                              {{ $value1['checkin'] }}
                              @endif
                            <?php } else {?>
                              {{ $value1['checkin'] }}
                            <?php } ?>
                        @endif
                      </td>
                      
                      <td class="text-center views-field views-field-field-profile-image">
                        @if(count($value1['checkout']) > 1)
                          @foreach ($value1['checkout'] as $key11 => $value11) 
                            {{ $value11 }}<br>
                          @endforeach
                          @else
                            {{ $value1['checkout'] }}
                        @endif
                      </td>

                      <!-- 
                      <td class="text-center views-field views-field-field-profile-image">
                        @if(count($value1['worked']) > 1)
                          @foreach($value1['worked'] as $key11 => $value11)
                            {{ $value11 }}<br>
                          @endforeach
                          @else
                          {{$value1['worked']}}
                        @endif
                      </td>
                      -->

                      <td class="text-center views-field views-field-field-profile-image">{{$value1['time']}}</td>
                  </tr>
                  <?php $i++;?>
                @endforeach
              @endforeach
            </tbody>

            
        </table>
    </div>
  <?php } else {?>
    <div class="row" style="margin-top:30px">
      <div class="text-center">
          <span><h3><?php echo $currMonth." ".$currYear; ?> In-Out Report Data Not Found</h3></span>
      </div>
    </div>
  <?php }?>
 
@endsection

@section('script')
{{ HTML::script('assets/js/moment.min.js') }}
{{ HTML::script('assets/js/canvasjs.min.js') }}
  
<script type="text/javascript">

$('document').ready(function(){   
   
  var url="<?php echo $url; ?>";

  if(url != "1"){  
     history.pushState(null, null,url);   
  }

  $("tr.data").mouseover(function() {
      $(this).css('background-color', '#25AAC2');
  }).mouseout(function() {
      $(this).css('background-color', 'transparent');
  });

  // Generating Legand Start
  var legandcolors = ["#1DA275", "#FF6600", "#50A8D6", "#F0B416", "#BBDD40", "#c908D3","#333300","#bdbdbd"];
  var legandtext = ["Present", "Leave", "Holiday", "WFH", "WFC", "Half Day","Absent","Comment"];
  
  for(i=0;i<legandcolors.length;i++) {
    $("#legand").append("<span style='width:12%;float:left;background-color: white;'><span>"+ legandtext[i] +"</span><span class='legand' style='background-color:"+legandcolors[i] +"''></span></span>");
  }
  // Generating Legand Endss

  // Generating Chart Start
  var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun","Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

  var currentYear = (new Date).getFullYear();
 
  var currentMonth = (new Date).getMonth()+1;  

  var data = <?php echo $data; ?>;
  var hlfdata = <?php echo $hlfdata; ?>; 

  var month=<?php $month=isset($_REQUEST['month'])?$_REQUEST['month'] : 'currentMonth';echo $month;?>;

  var year=<?php $year=isset($_REQUEST['year'])?$_REQUEST['year']: 'currentYear';echo $year;?>;
   

 var chart = new CanvasJS.Chart("chartContainer",
 {
  title:{
              fontWeight: "bolder",
              fontColor: "#008B8B",
              fontFamily: "tahoma",       
              fontSize: 25,
              padding: 10 
      },

      legend: {
       maxWidth: 400
      }, 
      animationEnabled: true,
      animationDuration: 5000,
 
      exportFileName: monthNames[month-1]+ " " + year + " Attendance Report",
 
      exportEnabled: true,
      theme: "theme4",

      axisY: {
             title: "Hours",
             interval: 2
      },

      axisX: {
            title: "Day",
            interval: 1,
            labelAngle:-45
      },

      legend: {
              verticalAlign: "bottom",
              horizontalAlign: "center"
      },

      data: [
              {        
                type: "stackedColumn",
                toolTipContent: "{label} "+monthNames[month-1]+" "+ year+"<hr/>Total Hours: {y}",
                dataPoints:data,
              },
              {        
                type: "stackedColumn",
                toolTipContent: "{label} "+monthNames[month-1]+" "+ year+"<hr/>Total Hours: {y}",
                dataPoints: hlfdata,
              } 
            ]
});
  chart.render();
  // Chart Generating End
});
 
</script>
 
@endsection