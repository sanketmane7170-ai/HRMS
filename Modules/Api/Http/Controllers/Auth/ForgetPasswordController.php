<?php

namespace Modules\Api\Http\Controllers\Auth;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

/**
 * @group 2. Forget Password
 */
class ForgetPasswordController extends Controller
{
    /**
     * Send a reset link to the given user.
     *
     * @bodyParam email email required The email of the user. Example: employee@example.com
     * @response status=422 scenario="Validation error" {
     *    "message": "The given data was invalid.",
     *    "errors": {
     *        "email": [
     *             "Please enter a valid email address."
     *        ]
     *    }
     * }
     * @response status=200 scenario="Password Reset Email Send Successfully"{
     *     "success": true,
     *      "message": "Password reset link has been send to registered email address",
     * }
     * @response status=502 scenario="Password Reset Email not sent "{
     *     "success": true,
     *      "message": "Something went wrong. Please try again later !!!",
     * }
     */
    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:users,email'
        ], ['email.exists' => __trans('please_enter_a_valid_email_address')]);

        if ($validator->fails()) {
            return  response()->error(__trans('validation_failed'), $validator->errors(), 422);
        }

        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $response = $this->broker()->sendResetLink(
            $this->credentials($request)
        );

        return $response == Password::RESET_LINK_SENT
            ? response()->success(__trans('password_reset_link_has_been_send_to_registered_email_address'))
            : response()->error(__trans('something_went_wrong._please_try_again_later'), [], 502);
    }

    /**
     * Get the needed authentication credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only('email');
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        return Password::broker();
    }
}
