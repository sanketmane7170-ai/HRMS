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
                    <p><strong> WARNING NOTICE </strong></p>
                </td>
            </tr>

            <tr>
                <td>
                    <table border="1" cellspacing="0" cellpadding="5" style="width: 100%;text-align: left;">
                        <tr>
                            <th style="width: 180px;background-color: #e1eff4;">Employee Name</th>
                            <th>{{$userWarning->user->name}}</th>
                        </tr>
                        <tr>
                            <th style="background-color: #e1eff4;">Employee Number</th>
                            <th>{{$userWarning->user->employee_id}}</th>
                        </tr>
                        <tr>
                            <th style="background-color: #e1eff4;">Position</th>
                            <th>{{$userWarning->user->designation->name}}</th>
                        </tr>
                        <tr>
                            <th style="background-color: #e1eff4;">Department</th>
                            <th>{{$userWarning->user->department->name}}</th>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td>
                    <div style="margin-top: 36px;"></div>
                </td>
            </tr>

            <tr>
                <td>
                    <table border="1" cellspacing="0" cellpadding="5" style="width: 100%;text-align: left;">
                        <tr>
                            <th style="width: 180px;background-color: #e1eff4;">Alleged offence</th>
                        </tr>
                        <tr>
                            <td> {!! $userWarning->detail !!}</td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td>
                    <div style="margin-top: 36px;"></div>
                </td>
            </tr>

            <tr>
                <td>
                    <table border="1" cellspacing="0" cellpadding="5" style="width: 100%;text-align: left;">
                        <tr>
                            <th style="width: 260px;background-color: #e1eff4;">Day and time of alleged offence:</th>
                            <th>{{formatDate($userWarning->date)}}</th>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td>
                    <div style="margin-top: 25px;"></div>
                </td>
            </tr>

            <tr>
                <td style="text-align: center;color: red;"><strong>Penalty</strong></td>
            </tr>

            <tr>
                <td>
                    <p>Due to the offence above, you are herewith given (tick relevant block):</p>
                </td>
            </tr>

            <tr>
                <td>
                    <table cellpadding="6" style="width: 100%;">
                        <tr>
                            <td>
                                <span style="display: inline-block;width: 20px;height: 20px;border: 1px solid #000;text-align: center;">
                                    @if($userWarning->type->value == $types[0]->value)
                                    X
                                    @endif
                                </span>
                            </td>
                            <td><span style="color: red;">Verbal</span> Warning</td>
                            <td>(Valid for 3 months)</td>
                            <td>Valid until Date</td>
                            <td>
                                <span style="display: inline-block;width: 100%;height: 20px;border: 1px solid #000;text-align: center;">
                                    @if($userWarning->type->value == $types[0]->value)
                                    @if(request()->getHttpHost()!="boon.momdigital.io")
                                    {{$userWarning->created_at->addMonths(3)->format(config('project.date_format'))}}
                                    @endif
                                    @endif
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span style="display: inline-block;width: 20px;height: 20px;border: 1px solid #000;text-align: center;">
                                    @if($userWarning->type->value == $types[1]->value)
                                    X
                                    @endif
                                </span>
                            </td>
                            <td><span style="color: red;">1st Written</span> Warning</td>
                            <td>(Valid for 6 months)</td>
                            <td>Valid until Date</td>
                            <td>
                                <span style="display: inline-block;width: 100%;height: 20px;border: 1px solid #000;text-align: center;">
                                    @if($userWarning->type->value == $types[1]->value)
                                    @if(request()->getHttpHost()!="boon.momdigital.io")
                                    {{$userWarning->created_at->addMonths(3)->format(config('project.date_format'))}}
                                    @endif
                                    @endif
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span style="display: inline-block;width: 20px;height: 20px;border: 1px solid #000;text-align: center;">
                                    @if($userWarning->type->value == $types[2]->value)
                                    X
                                    @endif
                                </span>
                            </td>
                            <td><span style="color: red;">2nd Written</span> Warning</td>
                            <td>(Valid for 6 months)</td>
                            <td>Valid until Date</td>
                            <td>
                                <span style="display: inline-block;width: 100%;height: 20px;border: 1px solid #000;text-align: center;">
                                    @if($userWarning->type->value == $types[2]->value)
                                    @if(request()->getHttpHost()!="boon.momdigital.io")
                                    {{$userWarning->created_at->addMonths(3)->format(config('project.date_format'))}}
                                    @endif
                                    @endif
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span style="display: inline-block;width: 20px;height: 20px;border: 1px solid #000;text-align: center;">
                                    @if($userWarning->type->value == $types[3]->value)
                                    X
                                    @endif
                                </span>
                            </td>
                            <td><span style="color: red;">Final Written</span> Warning</td>
                            <td>(Valid for 9 months)</td>
                            <td>Valid until Date</td>
                            <td>
                                <span style="display: inline-block;width: 100%;height: 20px;border: 1px solid #000;text-align: center;">
                                    @if($userWarning->type->value == $types[3]->value)
                                    @if(request()->getHttpHost()!="boon.momdigital.io")
                                    {{$userWarning->created_at->addMonths(3)->format(config('project.date_format'))}}
                                    @endif
                                    @endif
                                </span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td>
                    <p>Should you be found guilty of a further offence whilst this warning is still in force, the penalty that will be imposed on you could be influenced by this WARNING.</p>
                </td>
            </tr>

            <tr>
                <td>
                    <div style="margin-top: 15px;"></div>
                </td>
            </tr>

            <tr>
                <td>
                    <table cellpadding="10" cellspacing="0" style="width: 100%;text-align: center;">
                        <tr>
                            <td style="width: 336px">
                                {{auth()->user()->name}}
                                <hr />
                                Person Issuing the Warning
                            </td>
                            <td style="width: 250px;">
                                {{auth()->user()->designation->name ?? 'HR Manager'}}
                                <hr />
                                Job Title
                            </td>
                            <td>
                                {{now()->format(config('project.date_format'))}}
                                <br />
                                Date
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td>
                    <div style="margin-top: 25px;"></div>
                </td>
            </tr>

            <tr>
                <td>
                    <p>By signing this warning letter, you have acknowledged that you have received and understand the letter issued to you. In the event that the employee refused to sign the letter, any person who was present at the time the letter was communicated should sign as witness.</p>
                </td>
            </tr>

            <tr>
                <td>
                    <div style="margin-top: 25px;"></div>
                </td>
            </tr>

            <tr>
                <td>
                    <table cellpadding="10" cellspacing="0" style="width: 100%;text-align: center;">
                        <tr>
                            <td style="width: 300px">
                                {{$userWarning->user->name}}
                                <hr />
                                Employee Name
                            </td>
                            <td style="width: 180px;">
                                &nbsp;
                                <hr />
                                Date
                            </td>
                            <td>&nbsp;</td>
                        </tr>
                    </table>
                </td>
            </tr>


        </table>

    </div>
</body>

</html>