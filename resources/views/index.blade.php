@section('style')
{{ HTML::style('assets/css/fullcalendar.css') }}
{{ HTML::style('assets/css/fullcalendar1.print.css') }}
<style type="text/css">
    .fc-mon, .fc-tue, .fc-wed, .fc-thu, .fc-fri, .fc-sat, .fc-sun {
        background-color: rgb(51,122,183);
    }
</style>
@endsection

@extends('layouts.app')
@section('content')
<div id='calendar'></div>
@endsection

@section('script')
{{ HTML::script('assets/js/moment.min.js') }}
{{ HTML::script('assets/js/fullcalendar.min.js') }}

<script>

    $(document).ready(function() {
        // Get Current Date
        var date = new Date();

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
            defaultDate: '2016-02-11',
            editable: 'false',
            eventBorderColor: 'black',

            // hiddenDays: [ 6 ],  //To hide particular day
            // eventOrder: '-title', //Define Reverse Sorting
            events: [
                {
                    title: 'Leave',
                    start: '2016-02-11'
                },
                {
                    title: 'Approved',
                    start: '2016-02-11'
                },
                {
                    title: 'Present',
                    start: '2016-02-12'
                },
                {
                    title: 'In Time',
                    start: '2016-02-12T09:30:00'
                },
                {
                    title: 'Out Time',
                    start: '2016-02-12T19:30:00'
                },
                // {
                //  title: 'Total Hours',
                //  start: '2016-02-12T20:00:00'
                // },
                {
                    title: 'Holiday',
                    start: '2016-02-13'
                },
                {
                    title: 'Holiday',
                    start: '2016-02-14'
                },
                {
                    title: 'Absent',
                    start: '2016-02-15'
                },
                {
                    title: 'Approved',
                    start: '2016-02-15'
                },
                {
                    title: 'Work From Home',
                    start: '2016-02-16'
                },
                {
                    title: 'Approved',
                    start: '2016-02-16'
                },
                {
                    title: 'Work From Client Location',
                    start: '2016-02-17'
                },
                {
                    title: 'Approved',
                    start: '2016-02-17'
                },
                {
                    title: 'Half Day',
                    start: '2016-02-18'
                },
                {
                    title: 'In Time',
                    start: '2016-02-18T09:30:00'
                },
                {
                    title: 'Out Time',
                    start: '2016-02-18T14:00:00'
                },
                // Previous Month
                {
                    title: 'Leave',
                    start: '2016-01-11'
                },
                // Next Month
                {
                    title: 'Leave',
                    start: '2016-03-11'
                },
            ],

            eventRender: function (event, element, monthView) { 
              if (event.title == "Leave") {
                 // element.css("background-color", "red");
                 var dataToFind = moment(event.start).format('YYYY-MM-DD');
                 $("td[data-date='"+dataToFind+"']").css("background-color", "red");
              }
            }

        });

        

    });
</script>
@endsection