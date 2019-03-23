<?php

class Theme extends BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		Log::info("public function Theme@index()");

		$who = Auth::user();
		$now = date('r',time());
		parent::ensureMinimumAccess($who,2);

		$response = DB::table('tbl_themes')
			->orderBy('name','asc')
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
		Log::info("public function Theme@create()");
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
		Log::info("public function Theme@store()");
		//
		App::abort(409,'Theme creation NYI.');
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		Log::info("public function Theme@show()");

		$who = Auth::user();
		$now = date('r',time());
		parent::ensureMinimumAccess($who,2);

		$response = new stdClass();

		$response->themeRow = DB::table('tbl_themes')
			->where('id', $id)
			->get();

		return $this->response->item($response, new ThemeTransformer); 
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		Log::info("public function Theme@edit()");
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
		Log::info("public function Theme@update()");
		//
		App::abort(409,'Theme update NYI.');
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		Log::info("public function Theme@destroy()");
		//
		App::abort(409,'Theme deletion NYI.');
	}


}
