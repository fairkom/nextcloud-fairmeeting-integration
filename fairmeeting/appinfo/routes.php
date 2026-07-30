<?php

declare(strict_types=1);

return [
	'routes' => [
		// pages
		['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
		[
			'name' => 'page#room',
			'url' => '/rooms/{publicId}',
			'verb' => 'GET',
			'postfix' => 'shortroom'
		],
		['name' => 'page#room', 'url' => '/rooms/{publicId}/{roomName}', 'verb' => 'GET'],
		['name' => 'page#blank', 'url' => '/blank', 'verb' => 'GET'],
		// Calendar-invitation join target: hops through Nextcloud so the
		// logged-in user's display name can be appended to the Jitsi URL.
		['name' => 'page#join', 'url' => '/j/{roomName}', 'verb' => 'GET'],

		// API
		['name' => 'room#index', 'url' => '/rooms', 'verb' => 'GET'],
		['name' => 'room#create', 'url' => '/rooms', 'verb' => 'POST'],
		[
			'name' => 'room#get',
			'url' => '/api/rooms/{publicId}',
			'verb' => 'GET',
		],
		[
			'name' => 'room#update',
			'url' => '/api/rooms/{publicId}',
			'verb' => 'PUT',
		],
		['name' => 'room#delete', 'url' => '/rooms/{id}', 'verb' => 'DELETE'],
		[
			'name' => 'room#token',
			'url' => '/api/rooms/{publicId}/tokens',
			'verb' => 'POST',
		],
		['name' => 'user#get', 'url' => '/api/user', 'verb' => 'GET'],
		['name' => 'personal#update_jwt_token', 'url' => '/api/personal/jwt-token', 'verb' => 'PUT'],
		['name' => 'admin#index', 'url' => '/api/admin/settings', 'verb' => 'GET'],
		['name' => 'admin#update', 'url' => '/api/admin/settings', 'verb' => 'PUT'],

		// assets
		['name' => 'assets#soundsTest', 'url' => '/assets/sounds/test.wav', 'verb' => 'GET'],
	],
];
