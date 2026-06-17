
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{getSetting('site_title')}} | {{__trans('filemanager')}}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;0,900;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

    <style type="text/css" media="screen">
        body {
            font-family: 'Roboto', sans-serif;
        }

        img {
            max-width: 100%;
        }
    </style>

</head>

<body style="padding:0px !important; margin:0 !important; display:block !important; min-width:100% !important; width:100% !important; background:#ffffff; -webkit-text-size-adjust:none;">


    <div class="wrapper" style="max-width: 850px;display: block;margin: auto;border: 1px solid #ddd;">

        <table autosize="0" width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff" style="padding: 20px 50px;">
            <tr>
                <td style="text-align: center;text-decoration: underline;">
                    <!-- <img src="{{asset('assets/default/logo.png')}}" name="Picture 1" align="bottom" width="311" height="250" border="0" /> -->
                    <img src="{{getSmallLogo()}}" name="Picture 1" align="bottom" width="311" height="250" border="0" />
                </td>
            </tr>

            <tr>
                <td style="text-align: center;text-decoration: underline;">
                <p class="western" align="center">
                    <span style="display: inline-block; border: none"><b>DOCUMENT EXPIRY NOTIFICATION</b></span></p>
                </td>
            </tr>
    <br/>
            <tr>
                <td>
                    

                    <p class="western">
                     Dear {{$filemanager->employee->name}},
                    </p>
                    <br />
                    <p class="western" style="text-align:left;">
                       Title : {{$filemanager->title}}
                    </p>
                    <br />
                    <p class="western" style="text-align:left;">
                       Expiry Date : {{$filemanager->expiry_date}}
                    </p>
                 
                    <br />
                    <p class="western">
                    {{$userData['message']}}
                    </p>
                    <br />
                    <br />

                    <p class="western">
                    <br />

                    </p>

                </td>
            </tr>
        </table>

    </div>
</body>

</html>
