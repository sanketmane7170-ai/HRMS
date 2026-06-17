<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col">
                <h4>{{__trans('server_environments')}}</h4>
            </div>
            <div class="col-auto">

            </div>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-bordered-plain table-lg table-plain-info fs-13">
            <tr>
                <td width="250">Server Info</td>
                <td>{{ request()->server('SERVER_SOFTWARE') }}</td>
            </tr>
            <tr>
                <td width="250">Server Timezone</td>

                <td>{{ date_default_timezone_get() }}</td>
            </tr>
            <tr>
                <td width="250">PHP Version</td>
                <td>
                    {!! phpversion() !!}
                </td>
            </tr>
            <tr>
                <td width="250">cURL version</td>
                <td>
                    {!! (!empty(curl_version()) ? curl_version()['version'].', '.curl_version()['ssl_version'] : '-') !!}
                </td>
            </tr>
            <tr>
                <td width="250">MySQL Version</td>
                <td>
                    @php
                    $results = DB::select( "select version()") ;
                    $mysql_version = isset($results[0]->{'version()'}) ? $results[0]->{'version()'} : '*.*.*';
                    @endphp
                    {{ $mysql_version }}
                </td>
            </tr>
            <tr>
                <td width="250">PHP Post Max Size</td>
                <td>
                    {{ ini_get('post_max_size').'B' }} {!! ((int)ini_get('post_max_size') < 32 ? '<em class="ml-1 fas fa-info-circle fs-11 text-light" data-toggle="tooltip" data-placement="top" title="Recommend is 32MB or above."></em>' : '' ) !!} </td>
            </tr>
            <tr>
                <td width="250">Max Upload Size</td>
                <td>
                    {{ ini_get('upload_max_filesize').'B' }} {!! ((int)ini_get('upload_max_filesize') < 8 ? '<em class="ml-1 fas fa-info-circle fs-11 text-light" data-toggle="tooltip" data-placement="top" title="Recommend is 8MB or above."></em>' : '' ) !!} </td>
            </tr>
            <tr>
                <td width="250">PHP Memory Limit</td>
                <td>
                    {{ ini_get('memory_limit').'B' }} {!! ((int)ini_get('memory_limit') < 256 ? '<em class="ml-1 fas fa-info-circle fs-11 text-light" data-toggle="tooltip" data-placement="top" title="Recommend is 256MB or above."></em>' : '' ) !!} </td>
            </tr>
            <tr>
                <td width="250">PHP Time Limit</td>
                <td>
                    {{ ini_get('max_execution_time') }} {!! ((int)ini_get('max_execution_time') < 300 ? '<em class="ml-1 fas fa-info-circle fs-11 text-light" data-toggle="tooltip" data-placement="top" title="Recommend is 300 or above."></em>' : '' ) !!} </td>
            </tr>
            <tr>
                <td width="250">PHP Max Input Vars</td>
                <td>
                    {{ ini_get('max_input_vars') }} {!! ((int)ini_get('max_input_vars') < 1500 ? '<em class="ml-1 fas fa-info-circle fs-11 text-light" data-toggle="tooltip" data-placement="top" title="Recommend is 1500 or above."></em>' : '' ) !!} </td>
            </tr>
        </table>
    </div>
</div>
