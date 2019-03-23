<?php

# https://github.com/tymondesigns/jwt-auth

use Tymon\JWTAuth\Exceptions\JWTException;

class AuthenticateController extends Controller
{

    /**
     * Shows the change password form with the given token
     *
     * @param  string $token
     *
     * @return  Illuminate\Http\Response
     */
    public function resetForm($token)
    {
        return View::make('reset_password') #Config::get('confide::reset_password_form'))
                ->with('token', $token);
    }

    /**
     * Attempt to send change password link to the given email
     * -- taken from UsersController.php [confide] - modified for modal use
     *
     * @return  Illuminate\Http\Response
     */
    public function forgot()
    {
        if (Confide::forgotPassword(Input::get('email'))) {
            $notice_msg = Lang::get('confide::confide.alerts.password_forgot');
            return [ 'success' => true, 'message' => $notice_msg];
        } else {
            $error_msg = Lang::get('confide::confide.alerts.wrong_password_forgot');
            return [ 'success' => false, 'error' => $error_msg];
        }
    }

    /**
     * Attempt change password of the user
     * -- taken from UsersController.php [confide] - changed redirects
     *
     * @return  Illuminate\Http\Response
     */
    public function reset()
    {
        $repo = App::make('UserRepository');
        $input = array(
            'token'                 =>Input::get('token'),
            'password'              =>Input::get('password'),
            'password_confirmation' =>Input::get('password_confirmation'),
        );

        // By passing an array with the token, password and confirmation
        if ($repo->resetPassword($input)) {
            $notice_msg = Lang::get('confide::confide.alerts.password_reset');
            return Redirect::away($_ENV['PUBLIC_URL'].'/reset-success')
                ->with('notice', $notice_msg);
        } else {
            $error_msg = Lang::get('confide::confide.alerts.wrong_password_reset');
            return Redirect::action('AuthenticateController@resetForm', array('token'=>$input['token']))
                ->withInput()
                ->with('error', $error_msg);
        }
    }

    /**
     * Attempt to confirm account with code
     * -- taken from UsersController.php [confide] - changed redirects
     *
     * @param  string $code
     *
     * @return  Illuminate\Http\Response
     */
    public function confirm($code)
    {
        if (Confide::confirm($code)) {
            $notice_msg = Lang::get('confide::confide.alerts.confirmation');
            return Redirect::away($_ENV['PUBLIC_URL'].'/confirm-success')
                ->with('notice', $notice_msg);
        } else {
            $error_msg = Lang::get('confide::confide.alerts.wrong_confirmation');
            return Redirect::away($_ENV['PUBLIC_URL'].'/confirm-failure')
                ->with('error', $error_msg);
        }
    }

    /**
     * Attempt to do login
     * -- taken from UsersController.php [confide] - modified for jwt's & modal use
     *
     * @return  Illuminate\Http\Response
     */
    public function doLogin()
    {
        $repo = App::make('UserRepository');
        $input = Input::all();

        if ($repo->login($input)) {
            // return Redirect::intended('/');
        
            $credentials = Input::only('username', 'password');
            try {

                $who = Auth::user();

                // add the user's access level, their role, and....
                $customClaims = [ 'username' => $credentials['username'], 'email' => $who->email, 'access' => $who->access ];

                // attempt to verify the credentials and create a token for the user
                if (! $token = JWTAuth::attempt($credentials,$customClaims)) {
                    return [ 'success' => false, 'error' => 'invalid_credentials'];
                }
            } catch (JWTException $e) {
                // something went wrong whilst attempting to encode the token
                return [ 'success' => false, 'error' => 'could_not_create_token'];
            }
            // all good so return the token
            return compact('token');

        } else {
            if ($repo->isThrottled($input)) {
                $err_msg = Lang::get('confide::confide.alerts.too_many_attempts');
            } elseif ($repo->existsButNotConfirmed($input)) {
                $err_msg = Lang::get('confide::confide.alerts.not_confirmed');
            } else {
                $err_msg = Lang::get('confide::confide.alerts.wrong_credentials');
            }

            return [ "success" => false, "message" => $err_msg ];
        }
    }

    /**
     * Stores new account
     * -- taken from UsersController.php [confide] - modified for jwt's & modal use
     * -- updated the database with our own fields 
     *
     * @return  Illuminate\Http\Response
     */
    public function store()
    {
        $repo = App::make('UserRepository');
        $user = $repo->signup(Input::all());

        if ($user->id) {

            DB::table('tbl_users')
            ->where('id', $user->id)
            ->update(array(
                'userId' => uniqid('',true), 
                'creatorId' => 'New.Registrant',
                'access'=> 1
            ));

            if (Config::get('confide::signup_email')) {
                Mail::queueOn(
                    Config::get('confide::email_queue'),
                    Config::get('confide::email_account_confirmation'),
                    compact('user'),
                    function ($message) use ($user) {
                        $message
                            ->to($user->email, $user->username)
                            ->subject(Lang::get('confide::confide.email.account_confirmation.subject'));
                    }
                );
            }

            return [ "success" => true, "message" => Lang::get('confide::confide.alerts.account_created') ];

        } else {
            $error = $user->errors()->all(':message');

            return [ "success" => false, "message" => $error ];
        }
    }

    /**
     * Log the user out of the application.
     *
     * @return  Illuminate\Http\Response
     */
    public function logout()
    {
        Confide::logout();
        JWTAuth::invalidate(Input::get('token'));

        return [ "success" => true, "message" => "user is logged out." ];
    }
    
}
