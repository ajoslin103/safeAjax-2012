<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\ControllerTrait;

class BaseController extends Controller {

	use ControllerTrait;

	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	protected function setupLayout()
	{
		if ( ! is_null($this->layout))
		{
			$this->layout = View::make($this->layout);
		}
	}

	public function ensureMinimumAccess ( $user, $needs, $msg = '' ) {
		if ($user) {
			$scold = 'Newly registered users must be granted permissions by existing users.';
			if (! $user->access) {
				App::abort(401,$scold);
			}
			$scold = 'You need elevated permissions to complete this action.';
			if ($msg) { $scold .= ' '.$msg; }
			if ($needs > $user->access) {
				Log::info("Forbidden: user access:".$user->access." is lower than needed:".$needs);
				App::abort(403,$scold);
			}
		}
	}

}
