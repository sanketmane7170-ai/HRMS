@include('notifications.partials.header',['title' => getSetting('site_title')])

<tr>
    <td>
        <p>Welcome to {{ getSetting('site_title') }},</p>

        <p>With your digital parent, you’ll get access to features that help you communicate with coworkers and managers effectively. We are eagerly awaiting your start!</p>
    </td>
</tr>

<tr>
    <td><b>Your Credentials :</b></td>
</tr>

<tr>
    <td><br /></td>
</tr>

<tr>
    <td>
        @if(!empty($employee_id))
        <p><strong>Employee ID :</strong> {{ $employee_id }}</p>
        @endif
        <!-- @if(!empty($email))
        <p><strong>Email :</strong> {{ $email }}</p>
        @endif
        @if(!empty($phone))
        <p><strong>Phone :</strong> {{ $phone }}</p>
        @endif -->
        @if(!empty($password))
        <p><strong>Password :</strong> {{ $password }}</p>
        @endif
        @if(!empty($unique_code))
        <p><strong>Server Code :</strong> {{ $unique_code }}</p>
        @endif
    </td>
</tr>

<tr>
    <td>
        <a href="{{ route('login') }}" style="display: inline-block;padding: 12px 25px;color: #fff;background-color: #f53e61;background: linear-gradient(to right, #f53e61, #f99f25);text-decoration: none;border-radius: 5px;">Discover WorkPilot</a>
    </td>
</tr>

<tr>
    <td>
        <p>Here are the download links:</p>

        <p>
            Android users:
            <a href="https://play.google.com/store/apps/details?id=com.employee.mom" target="_blank" rel="noopener noreferrer">
                Download on Google Play
            </a>
        </p>

        <p>
            iOS users:
            <a href="https://apps.apple.com/app/id6463124736" target="_blank" rel="noopener noreferrer">
                Download on the App Store
            </a>
        </p>
    </td>
</tr>


@include('notifications.partials.footer')
