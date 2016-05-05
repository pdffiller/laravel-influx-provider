<?php

return [
	/*
	 * Protocol could take values 'http', 'https', 'udp'
	 */
	'protocol' => 'http',

	'user' => 'admin',

	'password' => 'qwe123',

	'host' => 'localhost',

	'port' => '1111',

	'database' => 'main',
	
	/*
	 * Use Queue for sending to InfluxDB, if 'true'
	 */
	'use_queue' => 'true',
	
	/**
	 * Use InfluxDB for error/exception collector if 'true'
	 */
	'use_monolog_handler' => 'true'
];
