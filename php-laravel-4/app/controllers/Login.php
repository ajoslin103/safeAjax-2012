<?php

if (! defined('NEW_REGISTRANT')){
	define('NEW_REGISTRANT',"New.Registrant");
}

if (! defined('NEW_REGISTRANT_ACCESS')){
	define('NEW_REGISTRANT_ACCESS',1);
}


class Login extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		Log::info("public function Login@index()");

		$who = Auth::user();
		$now = date('r',time());
		parent::ensureMinimumAccess($who,2);

		$response = DB::table('tbl_users')
			->orderBy('username','asc')
			->get();

		return $response;
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		Log::info("public function Login@create()");
		//
		Log::warning("this should never be called!");
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		Log::info("public function Login@store()");
		//
		Log::warning("this should never be called!");
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		Log::info("public function Login@show()");

		$who = Auth::user();
		$now = date('r',time());
		parent::ensureMinimumAccess($who,2);

		$response = new stdClass();

		$response->userRow = DB::table('tbl_users')
			->where('id', $id)
			->get();

		return $this->response->item($response, new LoginTransformer);
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		Log::info("public function Login@edit()");
		//
		Log::warning("this should never be called!");
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		Log::info("public function Login@update()");

		$who = Auth::user();
		$now = date('r',time());
		parent::ensureMinimumAccess($who,2);

		$userId = $id * 1; // ensure numeric

// access: "2,99"
// confirmation_code: null
// confirmed: 0
// created_at: null
// creatorId: "4cd0af427340d4.58982076"
// email: "junkx"
// id: 20
// lastModified: "2015-08-02 17:13:23"
// password: "simplex"
// remember_token: null
// updated_at: null
// userId: "4cd9a042e938d0.39161448"
// username: "ajoslin-oldx"

		$response = new stdClass();

		$response->userRow = DB::table('tbl_users')
			->where('id', $id)
			->get();

		// if the target has more access than you then no-dice
		parent::ensureMinimumAccess($who,$response->userRow[0]->access);

		// get the access choice & choose the high end
		$newAccessArr = explode(',',Input::get('access'));
		$newAccess = $newAccessArr[1];

		// limit the access to one less than your own
		if ($newAccess >= $who->access) {
			$newAccess = ($who->access -1);
		}

		// if this is a new registrant than claim as your own
		$creatorId = Input::get('creatorId');
		if ($creatorId == NEW_REGISTRANT) {
			$creatorId = $who->userId;
		}

		// if we are resetting them to a new registrant then clear their owner
		if (Input::get('access') == NEW_REGISTRANT_ACCESS) {
			$creatorId = NEW_REGISTRANT;
		}

		// update starting section
		DB::table('tbl_users')
			->where('id',$userId)
			->update([
				'access' => $newAccess,
				'email' => Input::get('email'),
				'username' => Input::get('username'),
				'creatorId' => $creatorId,
				'updated_at' => $now
			]);

	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		Log::info("public function Login@destroy()");

		$who = Auth::user();
		$now = date('r',time());
		parent::ensureMinimumAccess($who,1000);

		$userId = $id * 1; // ensure numeric

		// delete the user
		DB::table('tbl_users')
			->where('id',$userId)
			->delete();
	}


}
