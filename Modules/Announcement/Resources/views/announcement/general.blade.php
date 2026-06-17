<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{getSetting('site_title')}} | {{__trans('announcement')}}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;0,900;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

    <style type="text/css" media="screen">
        body {
            font-family: 'Roboto', sans-serif;
            line-height: 1.6;
            color: #333;
        }

        img {
            max-width: 100%;
        }

        .announcement-content {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>

<body style="padding:0px !important; margin:0 !important; display:block !important; min-width:100% !important; width:100% !important; background:#ffffff; -webkit-text-size-adjust:none;">

    <div class="wrapper" style="max-width: 850px;display: block;margin: auto;border: 1px solid #ddd;">

        <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff" style="padding: 20px 50px;">
            <tr>
                <td style="text-align: center; padding-bottom: 30px;">
                    <img src="{{getSmallLogo()}}" alt="{{getSetting('site_title')}}" width="200" border="0" />
                </td>
            </tr>

            <tr>
                <td style="text-align: center; padding-bottom: 20px;">
                    <h2 style="color: #2c3e50; margin: 0; font-weight: bold;">
                        {{$types}}
                    </h2>
                </td>
            </tr>

            <tr>
                <td>
                    <p style="margin-bottom: 20px;">
                        <strong>Date:</strong> {{formatDate($announcement->start_at)}}
                    </p>
                </td>
            </tr>

            @if(isset($user))
            <tr>
                <td>
                    <p style="margin-bottom: 20px;">
                        Dear {{$user->name}},
                    </p>
                </td>
            </tr>
            @else
            <tr>
                <td>
                    <p style="margin-bottom: 20px;">
                        Dear Team,
                    </p>
                </td>
            </tr>
            @endif

            <tr>
                <td>
                    <div class="announcement-content">
                        {!! $announcement->body !!}
                    </div>
                </td>
            </tr>

            @if($announcement->file)
            <tr>
                <td style="padding-top: 20px;">
                    <p><strong>Attachment:</strong> 
                        <a href="{{$announcement->file}}" style="color: #3498db; text-decoration: none;">
                            View Attachment
                        </a>
                    </p>
                </td>
            </tr>
            @endif

            <tr>
                <td style="padding-top: 40px;">
                    <p style="margin-bottom: 10px;">
                        <strong>Valid Period:</strong><br>
                        From: {{formatDate($announcement->start_at)}}<br>
                        To: {{formatDate($announcement->end_at)}}
                    </p>
                </td>
            </tr>

            <tr>
                <td style="padding-top: 30px; text-align: center; border-top: 1px solid #ddd; margin-top: 30px;">
                    <p style="margin: 10px 0; color: #666; font-size: 14px;">
                        This is an automated announcement from {{getSetting('site_title')}}
                    </p>
                    <p style="margin: 0; color: #888; font-size: 12px;">
                        Please do not reply to this email.
                    </p>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>