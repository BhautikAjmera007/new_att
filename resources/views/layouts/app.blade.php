<?php use config\attendance; ?>

<!DOCTYPE html>
<html>
<head profile="http://www.w3.org/1999/xhtml/vocab">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <!-- <title>Dashboard | HRMS</title> -->
  <title>@yield('settitle')</title>
  {{ HTML::style('assets/css/bootstrap.min.css') }} 
  {{ HTML::style('assets/css/menu_icons.css') }}
  {{ HTML::style('assets/css/overrides.css') }}
  {{ HTML::style('assets/css/styles.css') }}
  {{ HTML::style('assets/css/system.base.css') }}  
  {{ HTML::style('assets/css/bootstrap-select.min.css') }}
  {{ HTML::style('assets/css/chosen.min.css') }}
   
@yield('style')
</head>
<body class="html not-front logged-in no-sidebars page-employee-dashboard" >
  <div id="skip-link">
    <a href="#main-content" class="element-invisible element-focusable">Skip to main content</a>
  </div>

  <div class="navbar-fixed-top">
    <header id="navbar" role="banner" class="navbar navbar-default container-fluid">
      <div class="container">
        <div class="navbar-header">
          <a class="logo navbar-btn pull-left" href="http://hrms.knowarth.com/employee-dashboard" title="Home">
          {{ HTML::image('assets/image/logo.png','Home',array('class' => '')) }}
          </a>

          <!-- .btn-navbar is used as the toggle for collapsed navbar content -->
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
        </div>

        <div class="navbar-collapse collapse">
          <nav role="navigation">
            <div class="region region-navigation">
              <section id="block-views-user-profile-info-block" class="block block-views clearfix">
                <div class="view view-user-profile-info view-id-user_profile_info view-display-id-block view-dom-id-55df3a887f25dfe4966c4f2543b413d0">
                  <div class="view-content">
                    <div class="views-row views-row-1 views-row-odd views-row-first views-row-last">
                        <div class="views-field views-field-field-profile-image">        
                          <div class="field-content">
                            <img typeof="foaf:Image" src="{{ Auth::user()->uimg }}" width="100" height="100" alt="" />
                          </div>  
                        </div>  
                    <div class="views-field views-field-nothing">
                     <span class="field-content">
                        <div class="user-info-links dropdown-menu">
                          <div class="media"> 
                            <div class="media-left pull-left"> 
                              <p><img typeof="foaf:Image" src="{{ Auth::user()->uimg }}" width="100" height="100" alt="" /></p> 
                            </div> 
                            <div class="media-body"> 
                                <p class="media-heading">{{ Auth::user()->name }}</p>
                               <span class='hrms_designation'>{{ Auth::user()->designation }}</span>
                               <span class="hrms_Email">{{ Auth::user()->email }}</span>
                            </div> 
                            </div>

                            <div class='Profile_footer-dropdown'>
                              <a class="pull-left btn btn-info" href="http://hrms.knowarth.com/profile-personal_details/{{ Auth::user()->uid }}">My Account</a>
                              <a class='pull-right btn btn-danger' href='logout'>Logout</a>
                            </div>
                        </div>
                        </span> 
                      </div>  
                    </div>
                  </div>
                </div>
</section> <!-- /.block -->
</div>
</nav>
</div>
</div>
</header>
<div class="container-fluid hrms-navbarmain">
  <header role="banner" id="page-header" class="container">    
      <div class="region region-header">
          <section id="block-menu-menu-employee-header-menu-links" class="block block-menu clearfix">
            <ul class="menu nav">
              <li class="leaf">
 
                {{ HTML::link(config('attendance.menuurl').'employee-dashboard', "Dashboard" , array('class' => 'menu_icon menu-721'))}}
 
              </li>

              <li class="leaf">
                {{ HTML::link(config('attendance.menuurl').'employees-list', "PIM" , array('class' => 'menu_icon menu-1249'))}}
              </li> 
              <li class="expanded leaf ">
                {{ HTML::link(config('attendance.menuurl').'apply_leave', "LMS" , array('class' => 'menu_icon menu-1376'))}}
              </li> 

              <li class="expanded leaf ">
                {{ HTML::link(config('attendance.menuurl').'reimbursement', "Reimbursement" , array('class' => 'menu_icon menu-1378'))}}
              </li>
              <li class="leaf active-trail">

                <!--  For Employees Start -->
                <?php if(!isset($users_data)) {?>
                {{ HTML::link("", "Attendance" , array('class' => 'menu_icon menu-1249 active-trail active'))}}
                <?php } ?>
                <!--  For Employees End -->
 
                <!--  For HR, Managing Director and Deliver Head Start -->
                <?php if(isset($users_data)) {?>
                <li class="expanded leaf">
                  <a href="" class="menu_icon menu-1249 active-trail active" data-target="#" data-toggle="dropdown">Attendance<span class="caret"></span>
                  </a>
                    <ul class="dropdown-menu">

                      <li class="first leaf" style="border-bottom-style: solid; border-color:#0284B9;border-width: 1.5px;">
                        <input type="button" id="calender" class="btn btn-link dropbtn dropdown" style="text-decoration:none;width: 159px;text-align: left;font-size:14px;margin-left:10px;" value="Calender"></input>
                      </li>

                      <li class="first expanded dropdown-submenu">
                        <a href="#" data-target="#" data-toggle="dropdown">Report</a>
                          <ul class="dropdown-menu">
                           <!--<li class="first leaf" style="border-bottom-style: solid; border-color:#0284B9;border-width: 1.5px;">
                              <input type="button" id="userby" class="btn btn-link dropbtn dropdown" style="text-decoration:none;width: 159px;text-align: left;" value="By User"></input>
                            </li>-->

                            <li class="leaf" style="border-bottom-style: solid; border-color:#0284B9;border-width: 1.5px;">
                              <input type="button" id="monthby" class="btn btn-link dropbtn" style="text-decoration:none;width: 159px;text-align: left;" value="By Month"></input>
                            </li>

                            <li class="leaf" style="border-bottom-style: solid; border-color:#0284B9;border-width: 1.5px;">
                              <input type="button" id="dayby" class="btn btn-link dropbtn" style="text-decoration:none;width: 159px;text-align: left;" value="By Day"></input>
                            </li>

                            <li class="last leaf" style="border-bottom-style: solid; border-color:#0284B9;border-width: 1.5px;">
                              <input type="button" id="statusby" class="btn btn-link dropbtn" style="text-decoration:none;width: 159px;text-align: left;" value="By Status"></input>
                            </li>

                            <?php if($boolManuallyInoutStatus == "1"){ ?>
                            <li class="last leaf" style="border-bottom-style: solid; border-color:#0284B9;border-width: 1.5px;">
                              <input type="button" id="manually" class="btn btn-link dropbtn" style="text-decoration:none;width: 159px;text-align: left;" value="Manually In-Out"></input>
                            </li>

                            <li class="last leaf" style="border-bottom-style: solid; border-color:#0284B9;border-width: 1.5px;">
                              <input type="button" id="worktime" class="btn btn-link dropbtn" style="text-decoration:none;width: 159px;text-align: left;" value="Work Time"></input>
                            </li>
                            <?php } ?>

                          </ul>
                      </li>
                    </ul>
                  <?php } ?>
                </li>
                <!--  For HR, Managing Director and Deliver Head End -->
              </li>         
              </li>
            
              <li class="expanded leaf ">
                {{ HTML::link(config('attendance.menuurl').'documents-library', "Documents" , array('class' => 'menu_icon menu-1464'))}}
              </li>
            </ul>
</section> <!-- /.block -->
</div>
</header> <!-- /#page-header -->
</div>
</div>

<div class="hrms-mainContentData">
<div class="main-container container">
<div class="row" style="margin-bottom:10px;margin-right: 2px"> 

 <div class="pull-right" id="drop_cal_graph"> 
    <?php if(isset($users_data)) {?>
      <div class="col-lg-7" style="margin-bottom: -31px;">

      <select class="selectpicker chzn-select" selected="true" data-live-search="true" name="emp" id="emp">
        <option value="">-- Select Employee --</option> 
        <?php foreach ($users_data as $key => $value) { ?>
          <option value="{{$value['employee_code']}}"> {{$value['name']}} </option>           
        <?php } ?>
      </select>

      </div>
      <?php }?>

     <div class="btn-group pull-right">              
            <?php
              if(isset($_REQUEST['id'])){ ?>

                  {{ HTML::link('calender?id='.$_REQUEST['id'], 'Calender', array('class' =>  Request::is('chart') || Request::is('chart/next') || Request::is('chart/previous')? 'btn btn-default': 'btn btn-primary'))}}

                  {{ HTML::link('chart?id='.$_REQUEST['id'], 'Graph', array('class' => Request::is('calender')? 'btn btn-default': 'btn btn-primary','id' => 'chart'))}}

            <?php }else if(!isset($_REQUEST['id'])){ $id=\Auth::user()->employee_code;?>

                  {{ HTML::link('calender?id='.$id, 'Calender', array('class' =>  Request::is('chart') || Request::is('chart/next') || Request::is('chart/previous')? 'btn btn-default': 'btn btn-primary'))}}

                  {{ HTML::link('chart?id='.$id, 'Graph', array('class' => Request::is('calender')? 'btn btn-default': 'btn btn-primary','id' => 'chart'))}}

            <?php } ?>
      </div>
       
  </div>
</div>
@yield('content')

<footer class="footer container">
    <div class="region region-footer">
    <section id="block-block-2" class="block block-block  col-md-12 clearfix">
    <div id="bottom_part">
      <div class="left">
       <h2 class="title">Copyright HRMS. All rights reserved</h2>
      </div>
      <div class="center">
           <h2 class="title">HRMS <span>0.1</span></h2>
      </div>
      <div class="right">

             <h2 class="title">Developed By {{ HTML::image('assets/image/content.png') }}</h2>
      </div>
    </div>
    </section> <!-- /.block -->
  </div>
</footer>
<div>
</div>
{{ HTML::script('assets/js/jquery.min.js') }}
{{ HTML::script('assets/js/bootstrap.min.js') }}
{{ HTML::script('assets/js/cookie.js') }}
{{ HTML::script('assets/js/bootstrap-select.js') }}
{{ HTML::script('assets/js/chosen.jquery.min.js') }}
<script type="text/javascript">

$('document').ready(function(){
   
    
    $('#calender').click(function(){
       url = "/calender";
       window.location.replace(url); 
    });

    $('#worktime').click(function(){
       url = "/worktime";
       window.location.replace(url); 
    });

    $('#userby').click(function(){
       url = "/userBy";
       window.location.replace(url); 
    });

    $('#monthby').click(function(){
       url = "/monthBy";
       window.location.replace(url); 
    });

    $('#dayby').click(function(){
       url = "/day_report";
       window.location.replace(url); 
    });

    $('#statusby').click(function(){
       url = "/status";
       window.location.replace(url); 
    });

    $('#manually').click(function(){
       url = "/manually";
       window.location.replace(url);  
    });
 
    if(window.location.href.indexOf("userBy") > -1|| window.location.href.indexOf("manually") > -1 ||window.location.href.indexOf("status") > -1 ||window.location.href.indexOf("monthBy") > -1 || window.location.href.indexOf("day_report") > -1  ) {
          $(".pull-right").hide(); 
    }
    /*$("select").chosen({allow_single_deselect:true});*/
    // Manage Dropdown Start
    var empcode = '<?php if(!isset($_REQUEST["id"])) { echo $_SESSION["id"]; }else if(isset($_REQUEST["id"])){ echo $_REQUEST["id"]; } ?>'
    
    if(empcode != ""){ 
        $("#emp option[value="+empcode+"]").attr("selected","selected"); 
    } 
    
     $('#emp').change(function(){
        var val=$(this).val();
        
        if(val != ""){
            window.location='chart?id=' + val

            if(window.location.href.indexOf("next") > -1 || window.location.href.indexOf("previous") > -1 ){ 
               var emp_code = $(this).val();
               window.location.replace("/chart?id="+emp_code);     
            } 
        }
      
     });
    // Manage Dropdown End

    function hideAll(){
      jQuery(".user-info-links.dropdown-menu").hide();
      jQuery(".notification-count .view-message-notifications table").hide();
    }
    //jQuery("#block-views-user-profile-info-block .user-info-links").hide();
    jQuery("#block-views-user-profile-info-block").click(function(){
      jQuery(".notification-count .view-message-notifications table").hide();
      jQuery(".user-info-links.dropdown-menu").toggle();
    });
    //jQuery(".notification-count .view-message-notifications table").hide();
    jQuery("#notification-img").click(function(){
      jQuery(".user-info-links.dropdown-menu").hide();
      jQuery(".notification-count .view-message-notifications table").fadeToggle("slow");
    });
    jQuery("#edit-search-block-form--2").on({
      keyup:function(){
      hideAll();
     }
    })
});
 

 jQuery('body').click(function(e) {
     var target = jQuery(e.target);
     if(!target.is('#btn') && !target.is('#block-views-user-profile-info-block')) {
        if (jQuery('.user-info-links.dropdown-menu').is(':visible')) 
         jQuery('.user-info-links.dropdown-menu').hide();
     }
 }); 
</script>
@yield('script')
</body>
</html>
