<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{getSetting('site_title')}} | {{__trans('warning_notice')}}</title>
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
                    <img src="{{ asset('/assets/default/burroLogo.jpeg') }}" name="Picture 1" align="bottom" width="311" height="250" border="0" />
                </td>
            </tr>

            <tr>
                <td style="text-align: center;text-decoration: underline;">
                <p class="western" align="center">
                    <span style="display: inline-block; border: none"><b>Termination Letter</b></span></p>
                </td>
            </tr>
    <br/>
            <tr>
                <td>
                    <p class="western" style="text-align:left;">
                        To: {{$userWarning->user->name}}
                    </p>
                    <p class="western" style="text-align:left;">
                        Date: {{formatDate($userWarning->date)}}
                    </p>
                    <p class="western" style="text-align:left;">
                        Subject: Termination of Employment
                    </p>
                    <p class="western" style="text-align:left;">
                        Dear {{$userWarning->user->name}},
                    </p>
                    <p class="western">
                        This letter serves as formal notice of the termination of your employment, effective immediately, due to
                    </p>
                    <p class="western">
                        {!! $userWarning->detail !!}
                    </p>
                    <br />

                    <p class="western">
                    Sincerely,</p>

                    <p class="western">
                        {{auth()->user()->designation->name ?? 'HR Manager'}} 
                    </p>

                    <p class="western">
                        By signing below, you acknowledge and fully understand the content of this warning letter.
                    </p>
                    <p class="western">
                        <b>AGREEMENT & ACKNOWLEDGMENT</b>
                    </p>
                    <p class="western">
                        <b>EMPLOYEE SIGNATURE:</b>
                    </p>
                    <p class="western">
                        <b>DATE: </b>
                    </p>
                    <p class="western">
                        {{getSetting('site_title')}} SIGNATURE:
                    </p>
                    <p class="western">
                        <b>DATE: </b>
                    </p>
                    <br />
                    
                </td>
            </tr>
        </table>

    </div>
</body>

</html>
