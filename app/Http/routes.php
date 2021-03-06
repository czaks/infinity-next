<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group([
	'prefix' => '/',
	'middleware' => [
		\App\Http\Middleware\LocalizedSubdomains::class
	],
], function () {

	/*
	| Index route
	*/
	Route::get('/', 'WelcomeController@getIndex');

	Route::controller('boards.html', 'BoardlistController');

	Route::get('overboard.html', 'MultiboardController@getOverboard');

	Route::get('{page_title}.html', 'PageController@getPage');

	/*
	| Internal requests used by ESI to retrieve partial templates.
	*/
	Route::group([
		'namespace' => "Internal",
		'prefix'    => ".internal",
	], function() {

		Route::get('site/global-nav',    'SiteController@getGlobalNavigation');
		Route::get('site/recent-images', 'SiteController@getRecentImages');
		Route::get('site/recent-posts',  'SiteController@getRecentPosts');

	});

	/*
	| Control Panel (cp)
	| Anything having to deal with secure information goes here.
	| This includes:
	| - Registration, Login, and Account Recovery.
	| - Contributor status.
	| - Board creation, Board management, Volunteer management.
	| - Top level site management.
	*/
	Route::group([
		'namespace'  => 'Panel',
		'prefix'     => 'cp',
		'as'         => 'panel.',
	], function()
	{
		Route::group([
			'middleware' => 'csrf'
		], function()
		{
		// Simple /cp/ requests go directly to /cp/home
		Route::get('/', 'HomeController@getIndex');

		Route::controllers([
			// /cp/auth handles sign-ins and registrar work.
			'auth'     => 'AuthController',
			// /cp/home is a landing page.
			'home'     => 'HomeController',
			// /cp/password handles password resets and recovery.
			'password' => 'PasswordController',
		]);

		// /cp/donate is a Stripe cashier system for donations.
		if (env('CONTRIB_ENABLED', false))
		{
			Route::controller('donate', 'DonateController');
		}


		// /cp/histoy/ip will show you post history for an address.
		Route::get('history/{ip}', 'HistoryController@getHistory');

		Route::group([
			'prefix'    => 'bans',
		], function()
		{
			Route::get('banned',              'BansController@getIndexForSelf');
			Route::get('board/{board}/{ban}', 'BansController@getBan');
			Route::put('board/{board}/{ban}', 'BansController@putAppeal');
			Route::get('global/{ban}',        'BansController@getBan');
			Route::get('board/{board}',       'BansController@getBoardIndex');
			Route::get('global',              'BansController@getGlobalIndex');
			Route::get('/',                   'BansController@getIndex');
		});

		/**
		 *  Page Controllers (Panel / Management)
		 */
		Route::group([
			'middleware' => [
				\App\Http\Middleware\BoardAmbivilance::class,
			],
		], function()
		{
			Route::get('site/page/{page}/delete', [
				'as' => 'page.delete',
				'uses' => 'PageController@delete',
			]);
			Route::get('site/pages', 'PageController@index');
			Route::resource('site/page', 'PageController', [
				'names' => [
					'index'   => 'page.index',
					'create'  => 'page.create',
					'store'   => 'page.store',
					'show'    => 'page.show',
					'edit'    => 'page.edit',
					'update'  => 'page.update',
					'destroy' => 'page.destroy',
				],
			]);
			Route::get('board/{board}/page/{page}/delete', [
				'as' => 'board.page.delete',
				'uses' => 'PageController@delete',
			]);
			Route::get('board/{board}/pages', 'PageController@index');
			Route::resource('board/{board}/page', 'PageController', [
				'names' => [
					'index'   => 'board.page.index',
					'create'  => 'board.page.create',
					'store'   => 'board.page.store',
					'show'    => 'board.page.show',
					'edit'    => 'board.page.edit',
					'update'  => 'board.page.update',
					'destroy' => 'board.page.destroy',
				],
			]);
		});

		Route::group([
			'namespace' => 'Boards',
			'prefix'    => 'boards',
		], function()
		{
			Route::get('/', 'BoardsController@getIndex');

			Route::get('create', 'BoardsController@getCreate');
			Route::put('create', 'BoardsController@putCreate');

			Route::get('assets', 'BoardsController@getAssets');
			Route::get('config', 'BoardsController@getConfig');
			Route::get('staff',  'BoardsController@getStaff');
			Route::get('tags',   'BoardsController@getTags');

			Route::controller('appeals', 'AppealsController');
			Route::controller('reports', 'ReportsController');

			Route::group([
				'prefix'    => 'report',
			], function()
			{
				Route::get('{report}/dismiss',     'ReportsController@getDismiss');
				Route::get('{report}/dismiss-ip',  'ReportsController@getDismissIp');
				Route::get('{post}/dismiss-post',  'ReportsController@getDismissPost');
				Route::get('{report}/promote',     'ReportsController@getPromote');
				Route::get('{post}/promote-post',  'ReportsController@getPromotePost');
				Route::get('{report}/demote',      'ReportsController@getDemote');
				Route::get('{post}/demote-post',   'ReportsController@getDemotePost');
			});
		});

		Route::group([
			'namespace' => 'Boards',
			'prefix'    => 'board',
		], function()
		{
			Route::controllers([
				'{board}/staff/{user}' => 'StaffingController',
				'{board}/staff'        => 'StaffController',
				'{board}/role/{role}'  => 'RoleController',
				'{board}/roles'        => 'RolesController',
				'{board}'              => 'ConfigController',
			]);
		});

		Route::group([
			'namespace' => 'Site',
			'prefix'    => 'site',
		], function()
		{
			Route::get('/', 'SiteController@getIndex');
			Route::get('phpinfo', 'SiteController@getPhpinfo');

			Route::controllers([
				'config' => 'ConfigController',
			]);
		});

		Route::group([
			'namespace' => 'Users',
			'prefix'    => 'users',
		], function()
		{
			Route::get('/', 'UsersController@getIndex');
		});

		Route::group([
			'namespace' => 'Roles',
			'prefix'    => 'roles',
		], function()
		{
			Route::controller('permissions/{role}', 'PermissionsController');
			Route::get('permissions', 'RolesController@getPermissions');
		});
		});

		// /cp/adventure forwards you to a random board.
		Route::controller('adventure', 'AdventureController');

	});

	/*
	| Page Controllers
	| Catches specific strings to route to static content.
	*/
	if (env('CONTRIB_ENABLED', false))
	{
		Route::get('contribute', 'PageController@getContribute');
		Route::get('contribute.json', 'API\PageController@getContribute');
	}

	/*
	| API
	*/
	Route::group([
		'namespace' => "API",
	], function()
	{
		Route::get('board-details.json',  'BoardlistController@getDetails');
		Route::post('board-details.json', 'BoardlistController@getDetails');
	});

	/*
	| Board (/anything/)
	| A catch all. Used to load boards.
	*/
	Route::group([
		'prefix'    => '{board}',
	], function()
	{
		/*
		| Board API Routes (JSON)
		*/
		Route::group([
			'namespace' => "API\Board",
		], function()
		{
			// Gets the first page of a board.
			Route::any('index.json', 'BoardController@getIndex');

			// Gets index pages for the board.
			Route::get('page/{id}.json', 'BoardController@getIndex');

			// Gets all visible OPs on a board.
			Route::any('catalog.json', 'BoardController@getCatalog');

			// Gets all visible OPs on a board.
			Route::any('config.json', 'BoardController@getConfig');

			// Put new thread
			Route::put('thread.json', 'BoardController@putThread');

			// Put reply to thread.
			Route::put('thread/{post_id}.json', 'BoardController@putThread');

			// Get single thread.
			Route::get('thread/{post_id}.json', 'BoardController@getThread');

			// Get single post.
			Route::get('post/{post_id}.json', 'BoardController@getPost');
		});

		/*
		| Legacy API Routes (JSON)
		*/
		if (env('LEGACY_ROUTES', false))
		{
			Route::group([
				'namespace' => "API\Legacy",
			], function()
			{
				// Gets the first page of a board.
				Route::any('index.json', 'BoardController@getIndex');

				// Gets index pages for the board.
				Route::get('{id}.json', 'BoardController@getIndex');

				// Gets all visible OPs on a board.
				Route::any('threads.json', 'BoardController@getThreads');

				// Get single thread.
				Route::get('res/{post_id}.json', 'BoardController@getThread');
			});
		}


		/*
		| Post History
		*/
		Route::get('history/{post_id}', 'Panel\HistoryController@getBoardHistory');


		/*
		| Board Routes (Standard Requests)
		*/
		Route::group([
			'as'        => 'board.',
			'namespace' => 'Board',
		], function()
		{
			/*
			| Legacy Redirects
			*/
			if (env('LEGACY_ROUTES', false))
			{
				Route::any('index.html', function(App\Board $board) {
					return redirect("{$board->board_uri}");
				});
				Route::any('catalog.html', function(App\Board $board) {
					return redirect("{$board->board_uri}/catalog");
				});
				Route::any('{id}.html', function(App\Board $board, $id) {
					return redirect("{$board->board_uri}/{$id}");
				});
				Route::any('res/{id}.html', function(App\Board $board, $id) {
					return redirect("{$board->board_uri}/thread/{$id}");
				});
				Route::any('res/{id}+{last}.html', function(App\Board $board, $id, $last) {
					return redirect("{$board->board_uri}/thread/{$id}/{$last}");
				})->where(['last' => '[0-9]+']);
			}


			/*
			| Board Post Routes (Modding)
			*/
			Route::group([
				'prefix' => 'post/{post_id}',
			], function()
			{
				Route::controller('', 'PostController');
			});


			/*
			| Board Controller Routes
			| These are greedy and will redirect before others, so make sure they stay last.
			*/
			// Get stylesheet
			Route::get('{style}.css', 'BoardController@getStylesheet');
			Route::get('{style}.txt', 'BoardController@getStylesheetAsText');

			// Pushes simple /board/ requests to their index page.
			Route::any('/', 'BoardController@getIndex');

			// Get the catalog.
			Route::get('catalog', 'BoardController@getCatalog');

			// Get the config.
			Route::get('config', 'BoardController@getConfig');

			// Get moderator logs
			Route::get('logs', 'BoardController@getLogs');

			// Generate post preview.
			Route::any('post/preview', 'PostController@anyPreview');

			// Check if a file exists.
			Route::get('check-file', 'BoardController@getFile');

			// Handle a file upload.
			Route::post('upload-file', 'BoardController@putFile');


			// Routes /board/1 to an index page for a specific pagination point.
			Route::get('{id}', 'BoardController@getIndex');

			Route::group([
				'as' => 'thread.',
			], function()
			{
				// Redirect to a post.
				Route::get('redirect/{post_id}', [
					'as' => 'redirect',
					'uses' => 'BoardController@getThreadRedirect',
				]);
				Route::get('post/{post_id}', [
					'as' => 'goto',
					'uses' => 'BoardController@getThread',
				]);

				// Put new thread
				Route::put('thread', [
					'as' => 'put',
					'uses' => 'BoardController@putThread',
				]);

				// Get single thread.
				Route::get('thread/{post_id}', [
					'as' => 'get',
					'uses' => 'BoardController@getThread',
				]);

				// Get a splice of a single thread.
				Route::get('thread/{post_id}/{splice}', [
					'as' => 'splice',
					'uses' => 'BoardController@getThread'
				]);

				// Put reply to thread.
				Route::put('thread/{post_id}', [
					'as' => 'reply',
					'uses' => "BoardController@putThread",
				]);
			});
		});
	});

});

/*
| Board Attachment Routes (Files)
| Utilizes a CDN domain.
*/
if (env('APP_URL_MEDIA'))
{
	Route::group([
		'domain'     => env('APP_URL_MEDIA'),
		'prefix'     => 'file',
		'middleware' => [
			\App\Http\Middleware\FileFilter::class,
		],
		'namespace'  => 'Content',
	], function() {

		Route::get('{hash}/{filename}', [
			'as'   => 'static.file.hash',
			'uses' => 'ImageController@getImageFromHash',
		])->where(['hash' => "[a-f0-9]{32}",]);

		Route::get('{attachment}/{filename}', [
			'as'   => 'static.file.attachment',
			'uses' => 'ImageController@getImageFromAttachment',
		]);

		Route::get('thumb/{hash}/{filename}', [
			'as'   => 'static.thumb.hash',
			'uses' => 'ImageController@getThumbnailFromHash',
		])->where(['hash' => "[a-f0-9]{32}",]);

		Route::get('thumb/{attachment}/{filename}', [
			'as'   => 'static.thumb.attachment',
			'uses' => 'ImageController@getThumbnailFromAttachment',
		]);

	});
}
/*
| Board Attachment Routes (Files)
| Plain routing.
*/
else if (!env('APP_URL_MEDIA'))
{
	Route::group([
		'prefix'     => '{board}/file',
		'middleware' => \App\Http\Middleware\FileFilter::class,
		'namespace'  => 'Content',
	], function()
	{
		Route::get('{hash}/{filename}', [
			'as'   => 'static.file.hash',
			'uses' => 'ImageController@getImageFromHash',
		])->where(['hash' => "[a-f0-9]{32}",]);

		Route::get('{attachment}/{filename}', [
			'as'   => 'static.file.attachment',
			'uses' => 'ImageController@getImageFromAttachment',
		]);

		Route::get('thumb/{hash}/{filename}', [
			'as'   => 'static.thumb.hash',
			'uses' => 'ImageController@getThumbnailFromHash',
		])->where(['hash' => "[a-f0-9]{32}",]);

		Route::get('thumb/{attachment}/{filename}', [
			'as'   => 'static.thumb.attachment',
			'uses' => 'ImageController@getThumbnailFromAttachment',
		]);

		Route::get('remove/{attachment}', 'ImageController@getDeleteAttachment');
		Route::post('remove/{attachment}', 'ImageController@postDeleteAttachment');
		Route::get('spoiler/{attachment}', 'ImageController@getSpoilerAttachment');
		Route::post('spoiler/{attachment}', 'ImageController@postSpoilerAttachment');
		Route::get('unspoiler/{attachment}', 'ImageController@getSpoilerAttachment');
		Route::post('unspoiler/{attachment}', 'ImageController@postSpoilerAttachment');
	});
}
