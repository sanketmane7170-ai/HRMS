<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; color: #fff; }
        .content { padding: 30px; line-height: 1.6; }
        .footer { background: #f9f9f9; padding: 20px; text-align: center; font-size: 12px; color: #777; border-top: 1px solid #eee; }
        .btn { display: inline-block; padding: 12px 25px; background-color: #764ba2; color: #fff !important; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 20px; }
        .details-box { background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 5px; margin-top: 15px; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .status-pending { background: #e0f2fe; color: #0369a1; }
        .status-approved { background: #dcfce7; color: #15803d; }
        .status-rejected { background: #fee2e2; color: #b91c1c; }
        .status-completed { background: #f3f4f6; color: #374151; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0; font-size: 24px;">Resignation Module 💼</h1>
        </div>
        <div class="content">
            <p>Hello <strong>{{ $user->name }}</strong>,</p>
            
            <p>This is an automated notification regarding a resignation process update.</p>

            <div class="details-box">
                <p style="margin: 0;"><strong>Event:</strong> 
                    @if($type == 'submitted') Resignation Application Submitted
                    @elseif($type == 'approved') Resignation Approved
                    @elseif($type == 'rejected') Resignation Rejected
                    @elseif($type == 'waived') Notice Period Waived
                    @elseif($type == 'completed') Process Completed
                    @endif
                </p>
                <p style="margin: 5px 0 0 0;"><strong>Employee:</strong> {{ $resignation->employee->name }}</p>
                <p style="margin: 5px 0 0 0;"><strong>Status:</strong> 
                    <span class="status-badge status-{{ $resignation->status }}">{{ strtoupper($resignation->status) }}</span>
                </p>
            </div>

            @if(isset($data['comments']) && $data['comments'])
                <p><strong>Remarks from HR/Manager:</strong><br>
                <em>"{{ $data['comments'] }}"</em></p>
            @endif

            @if($type == 'approved' && $resignation->approved_last_working_date)
                <p><strong>Approved Last Working Date:</strong> {{ \Carbon\Carbon::parse($resignation->approved_last_working_date)->format('d M, Y') }}</p>
            @endif

            <p>Please log in to your dashboard to view the full details and take any necessary next steps.</p>

            <div style="text-align: center;">
                <a href="{{ $resignation->id ? url('/resignation') : url('/dashboard') }}" class="btn">View Dashboard</a>
            </div>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} MOM DIGITAL LLC HR Management System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
