<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Job Offer Letter - {{ $candidate_name }}</title>
    <style>
        @page {
            size: A4;
            margin: 20mm;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #333333;
            margin: 0;
            padding: 0;
        }
        
        .letterhead {
            background-color: #D61F69;
            color: white;
            padding: 30px;
            margin: -20mm -20mm 30px -20mm;
            position: relative;
            overflow: hidden;
        }
        
        .letterhead::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            transform: rotate(45deg);
        }
        
        .header-content {
            display: table;
            width: 100%;
            position: relative;
            z-index: 1;
        }
        
        .header-left {
            display: table-cell;
            vertical-align: top;
            width: 65%;
        }
        
        .header-right {
            display: table-cell;
            vertical-align: top;
            width: 35%;
            text-align: right;
        }
        
        .company-name {
            color: white;
            font-size: 32pt;
            font-weight: bold;
            margin: 0 0 10px 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .document-title {
            color: #ECA770;
            font-size: 20pt;
            font-weight: bold;
            margin: 0;
            padding-left: 10px;
            border-left: 4px solid #ECA770;
        }
        
        .logo {
            max-height: 80px;
            max-width: 140px;
            background: rgba(255,255,255,0.15);
            padding: 10px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }
        
        .section-title {
            color: #D61F69;
            font-size: 14pt;
            font-weight: bold;
            margin: 25px 0 15px 0;
            border-bottom: 2px solid #ECA770;
            padding-bottom: 5px;
        }
        
        .details-table {
            width: 100%;
            margin: 15px 0;
        }
        
        .details-table td {
            padding: 5px 15px 5px 0;
            vertical-align: top;
        }
        
        .label {
            font-weight: bold;
            width: 120px;
        }
        
        .salary-highlight {
            font-size: 13pt;
            font-weight: bold;
            color: #D61F69;
        }
        
        .benefits-list {
            margin: 15px 0;
            padding-left: 20px;
        }
        
        .benefits-list li {
            margin-bottom: 5px;
        }
        
        .signature-section {
            margin-top: 40px;
        }
        
        .signature-line {
            margin-top: 30px;
            margin-bottom: 5px;
        }
        
        .date-line {
            color: #666;
            font-style: italic;
            margin-bottom: 25px;
        }
        
        .candidate-address {
            margin-bottom: 25px;
        }
        
        p {
            margin: 10px 0;
        }
        
        strong {
            font-weight: bold;
        }
        
        .contingencies {
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #ECA770;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <!-- Professional Letterhead -->
    <div class="letterhead">
        <div class="header-content">
            <div class="header-left">
                <h1 class="company-name">{{ $company_name }}</h1>
                <h2 class="document-title">Job Offer Letter</h2>
            </div>
            <div class="header-right">
                @if(isset($logo_base64) && $logo_base64)
                    <img src="data:image/{{ $logo_mime ?? 'png' }};base64,{{ $logo_base64 }}" alt="Company Logo" class="logo">
                @elseif(isset($logo_path) && $logo_path)
                    {{-- Convert storage path (public/logos/...) to absolute path (storage_path('app/public/logos/...')) --}}
                    <img src="{{ storage_path('app/' . $logo_path) }}" alt="Company Logo" class="logo">
                @endif
            </div>
        </div>
    </div>

    <!-- Date -->
    <div class="date-line">
        {{ $generated_date }}
    </div>

    <!-- Candidate Address -->
    <div class="candidate-address">
        <strong>{{ $candidate_name }}</strong><br>
        Dear {{ $candidate_name }},
    </div>

    <!-- Opening Paragraph -->
    <p>
        We are pleased to extend this offer of employment for the position of <strong>{{ $job_title }}</strong> 
        in our <strong>{{ $department }}</strong> department at <strong>{{ $company_name }}</strong>.
    </p>

    <!-- Position Details Section -->
    <h3 class="section-title">Position Details</h3>
    
    <table class="details-table">
        <tr>
            <td class="label">Job Title:</td>
            <td>{{ $job_title }}</td>
            <td class="label">Start Date:</td>
            <td>{{ $start_date }}</td>
        </tr>
        <tr>
            <td class="label">Department:</td>
            <td>{{ $department }}</td>
            <td class="label">Location:</td>
            <td>{{ $location }}</td>
        </tr>
        <tr>
            <td class="label">Reporting To:</td>
            <td>{{ $reporting_to }}</td>
            <td class="label">Schedule:</td>
            <td>{{ $work_schedule }}</td>
        </tr>
    </table>

    <!-- Compensation Section -->
    <h3 class="section-title">Compensation</h3>
    
    <p>
        Your starting salary will be <span class="salary-highlight">{{ $currency_symbol ?? '$' }}{{ $salary_amount }} per {{ strtolower($payment_period) }}</span>, 
        paid {{ strtolower($pay_frequency) }}.
    </p>

    <!-- Benefits Section -->
    @if(!empty($benefits))
    <h3 class="section-title">Benefits Package</h3>
    
    <ul class="benefits-list">
        @foreach($benefits as $benefit)
            @if(trim($benefit))
                <li>{{ trim($benefit) }}</li>
            @endif
        @endforeach
    </ul>
    @endif

    <!-- Terms & Conditions Section -->
    @if($contingencies)
    <h3 class="section-title">Terms & Conditions</h3>
    
    <div class="contingencies">
        <p>{{ $contingencies }}</p>
    </div>
    @endif

    <!-- Closing Paragraph -->
    <p>
        Please confirm your acceptance of this offer by <strong>{{ $expiration_date }}</strong>. 
        We are excited about the possibility of you joining our team and look forward to your positive response.
    </p>

    <p>
        If you have any questions about this offer, please don't hesitate to contact us.
    </p>

    <!-- Signature Section -->
    <div class="signature-section">
        <p>Sincerely,</p>
        
        <div class="signature-line">
            <strong>{{ $sender_name }}</strong><br>
            {{ $sender_title }}<br>
            {{ $company_name }}
        </div>
    </div>

    <!-- Footer -->
    <div style="position: fixed; bottom: 20mm; left: 0; right: 0; text-align: center; color: #666; font-size: 10pt; border-top: 1px solid #ddd; padding-top: 10px;">
        This offer letter was generated on {{ $generated_date }}
    </div>
</body>
</html>