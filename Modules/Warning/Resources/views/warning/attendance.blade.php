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
                    <img src="{{ $logo }}" name="Picture 1" align="bottom" width="311" height="250" border="0" />
                </td>
            </tr>

            <tr>
                <td style="text-align: center;text-decoration: underline;">
                <p class="western" align="center">
                    <span style="display: inline-block; border: none"><b>Verbal Warning
                    - Attendance Policy</b></span></p>
                </td>
            </tr>
    <br/>
            <tr>
                <td>
                    <p class="western">
                        Dear {{$userWarning->user->name}},
                    </p>
                    <br />

                    <p class="western">
                        This letter serves as a formal verbal warning regarding your attendance. We have observed that there have been inconsistancy with your attendance, which is not in line with the company’s attendance policy.
                    </p>
                    <br />

                    <p class="western">
                        It is important to understand that regular attendance is essential to the smooth operation of the team and company. We expect you to adhere to the agreed-upon work schedule and make necessary arrangements to improve your attendance moving forward.
                    </p>
                    <br />

                    <p class="western">
                        Please treat this as an opportunity to correct the situation. Continued attendance issues may result in further disciplinary action including payroll cut off.
                    </p>
                    <br />

                    <p class="western">
                        We trust that you will address this matter and ensure there are no further occurrences.
                    </p>
                    <br />

                    <p class="western">
                        Regards,
                    </p>

                    <p class="western">
                        {{getSetting('site_title')}}</p>
                    </p>

                    <p class="western">
                        Management
                    </p>
                    <br />

                </td>
            </tr>
        </table>

    </div>
</body>

</html>
