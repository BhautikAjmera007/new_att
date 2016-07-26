<!DOCTYPE html>
<html>
    <head>
        <title>Error 401 | HRMS</title>
        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
        {{ HTML::style('assets/css/bootstrap.min.css') }} 
        <style>
            html, body {
                height: 100%;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                color: #B0BEC5;
                display: table;
                font-weight: 100;
                font-family: 'Lato';
            }

            .container {
                text-align: center;
                display: table-cell;
                vertical-align: middle;
            }

            .content {
                text-align: center;
                display: inline-block;
            }

            .title {
                font-size: 72px;
                margin-bottom: 40px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="content">
            <div class="row">
                <div class="title">You are not access rights!</div>
            </div>

            {{ Form::open(array('url' => '/', 'method' => 'get', 'class' => 'form-horizontal')) }}
            {{ csrf_field() }}
                <div class="row">
                    <input type="submit" class="btn btn-primary" value="Back"/>
                </div>
            {{ Form::close() }}

            </div>
        </div>
    </body>
</html>
