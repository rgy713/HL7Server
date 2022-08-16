<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>HIS-LIS Setting</title>

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 30px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 18px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
            .input{
                width: 500px;
                font-size: 18px;
                line-height: 18px;
                padding: 5px;
            }
            .submit{
                background-color: #4CAF50; /* Green */
                border: none;
                color: white;
                padding: 8px 20px;
                text-align: center;
                text-decoration: none;
                display: inline-block;
                font-size: 16px;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            <div class="content">
                <div class="title m-b-md">
                    科室、实验室项目代码设置
                </div>

                <div class="links">
                    <form method="POST" action="/setting/update">
                        @csrf
                        <p style="text-align: left">科室ID:</p><textarea class="input" name="departments" placeholder="请输入科室ID(实例：1,2,3)" rows="3" pattern="[a-zA-Z0-9\s]+">{{$departments}}</textarea>
                        <br/><br/>
                        <p style="text-align: left">实验室项目代码:</p><textarea class="input" name="test_items" placeholder="请输入实验室项目代码(实例：1,2,3)" rows="5" pattern="[a-zA-Z0-9\s]+">{{$test_items}}</textarea>
                        <br/><br/>
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                        <button class="submit" type="submit">设 置</button>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
