@section('settitle') By User {{config('attendance.sitename')}}
@endsection

@extends('layouts.app')
<style type="text/css">
  table thead tr{
  	background-color: white;
  	color:black;
  }

  table tbody tr{
  	background-color:rgb(225,237,237);
  }
  
  table tbody tr:nth-child(even) {
    background:rgb(242,247,247);
  }
</style> 
@section('content')
<h1 class="page-header">By User</h1>
<div class="view-filters even" style="background-color: #e1eded;height: 88px;border-radius: 0px;">
  <form class="form-inline" action="{{ url('user') }}" method="get">
    <input type="hidden" name="_token" value="<?=  csrf_token(); ?>">
    <div class="row" style="margin-left:7px;margin-bottom:10px;">
      <label class="control-label">Search</label>
    </div>

    <div class="row" style="margin-left:7px;margin-bottom:10px;">
      <input type="text" name="searchval" class="form-control" placeholder="Search By Name" autofocus>
      <input type="submit" class='btn btn-info'style="margin-left:20px" value="Apply" >
    </div> 
  </form> 
</div>  
<div class="view-content">
        {!! $table->render() !!}
</div>

@endsection

 
