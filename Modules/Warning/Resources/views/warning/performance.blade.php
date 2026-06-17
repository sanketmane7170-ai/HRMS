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
                    <img src="http://boon.momdigital.io/assets/default/boon-logo.png" name="Picture 1" align="bottom" width="311" height="250" border="0" />
                </td>
            </tr>

            <tr>
                <td style="text-align: center;text-decoration: underline;">
                <p class="western" align="center">
                    <span style="display: inline-block; border: none"><b>PERFORMANCE
                    - WARNING LETTER</b></span></p>
                </td>
            </tr>
    <br/>
            <tr>
                <td>
                    <p class="western" style="text-align:left;">
                        Date: {{formatDate($userWarning->date)}}
                    </p>
              
                    <br />

                    <p class="western">
                     Dear {{$userWarning->user->name}},
                    </p>
                    <br />

                    <p class="western">
                        I hope this message finds you well.
                    </p>
                    <br />

                    <p class="western">
                    It
                    has come to our attention that your recent performance has not met
                    the expected standards for your role. 
                    </p>


                    <p class="western">
                    <br />

                    </p>


                    <p class="western">
                    We
                    believe in your potential and are committed to supporting you in
                    improving your performance. Specifically, we have noted the following
                    areas of concern:</p>


                    <p class="western">
                    <br />

                    </p>


                    <p class="western" >
                    <b>**Time Management**:</b>
                    Inefficient use of time during peak hours has resulted in longer wait
                    times and decreased overall productivity.</p>


                    <p class="western" >
                    <b>**Team Collaboration**:</b>
                    There have been issues with communication and teamwork, impacting the
                    smooth operation of shifts.</p>


                    <p class="western" >
                    <b>**Punctuality and
                    Attendance**:</b>
                    Repeated tardiness and absenteeism have disrupted shift schedules and
                    placed additional strain on other team members. Ignorance of
                    attendance 
                    </p>


                    <p class="western" >
                    <br />

                    </p>


                    <p class="western">
                    <br />

                    </p>


                    <p class="western">
                    Please
                    understand that it is crucial to address these issues promptly to
                    ensure continued employment with the company. We are confident that
                    with focus and dedication, you can achieve the required improvements.</p>


                    <p class="western">
                    If
                    you have any questions or need further assistance, please do not
                    hesitate to reach out to your manager. 
                    </p>


                    <p class="western">
                    <br />

                    </p>


                    <p class="western">
                    Sincerely,</p>


                    <p class="western">
                    {{auth()->user()->name}} 
                    </p>


                    <p class="western">
                    {{auth()->user()->designation->name ?? 'HR Manager'}} 
                    </p>


                    <p class="western">
                    {{getSetting('site_title')}}</p>


                    <p class="western">
                    <br />

                    </p>

                    <p class="western">
                    Acknowledged by</p>
                </td>
            </tr>
        </table>

    </div>
</body>

</html>
