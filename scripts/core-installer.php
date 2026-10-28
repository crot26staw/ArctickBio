<?php
// Одноразовый установщик для хостинга без SSH — шаблон, токен подставляет scripts/core-export.sh.
// Действия (POST t, do): probe — проверка хостинга и базы; core — распаковать wp-core.zip; db — импорт arctik-dump.sql.gz в пустую базу.
// После любого запуска удаляет себя, а также zip / дамп.

register_shutdown_function( static function () {
	@unlink( __FILE__ );
} );

if ( ! hash_equals( '__TOKEN__', (string) ( $_POST['t'] ?? '' ) ) ) {
	http_response_code( 404 );
	exit;
}

set_time_limit( 0 );
header( 'Content-Type: text/plain; charset=utf-8' );

// Доступы базы — из wp-config.php рядом (без загрузки WordPress)
function arctik_install_db() {
	$config = @file_get_contents( __DIR__ . '/wp-config.php' );
	if ( ! $config ) {
		exit( "Нет wp-config.php\n" );
	}
	$db = [];
	foreach ( [ 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_HOST' ] as $key ) {
		if ( ! preg_match( "/define\(\s*'$key',\s*'((?:[^'\\\\]|\\\\.)*)'\s*\)/", $config, $m ) ) {
			exit( "В wp-config.php нет $key\n" );
		}
		$db[ $key ] = str_replace( [ "\\'", '\\\\' ], [ "'", '\\' ], $m[1] );
	}
	mysqli_report( MYSQLI_REPORT_OFF );
	$link = @new mysqli( $db['DB_HOST'], $db['DB_USER'], $db['DB_PASSWORD'], $db['DB_NAME'] );
	if ( $link->connect_errno ) {
		exit( 'База: ошибка входа — ' . $link->connect_error . "\n" );
	}
	$link->set_charset( 'utf8mb4' );
	return $link;
}

$action = (string) ( $_POST['do'] ?? '' );

if ( 'probe' === $action ) {
	echo 'PHP ', PHP_VERSION, ', ZipArchive: ', class_exists( 'ZipArchive' ) ? 'да' : 'нет', "\n";
	echo 'memory_limit ', ini_get( 'memory_limit' ), ', max_execution_time ', ini_get( 'max_execution_time' ), "\n";
	echo 'Папка ', __DIR__, is_writable( __DIR__ ) ? ' — запись есть' : ' — ЗАПИСИ НЕТ', "\n";
	echo 'HTTPS=', $_SERVER['HTTPS'] ?? '-', ', X-Forwarded-Proto=', $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '-', "\n";
	$link   = arctik_install_db();
	$tables = $link->query( 'SHOW TABLES' )->num_rows;
	$packet = $link->query( "SHOW VARIABLES LIKE 'max_allowed_packet'" )->fetch_row()[1] ?? '?';
	echo 'База: вход есть, ', $link->server_info, ', таблиц — ', $tables, ', max_allowed_packet ', $packet, "\n";
	exit;
}

if ( 'core' === $action ) {
	$file = __DIR__ . '/wp-core.zip';
	$zip  = new ZipArchive();
	if ( true !== $zip->open( $file ) ) {
		exit( "Не открылся wp-core.zip\n" );
	}
	$count = $zip->numFiles;
	$ok    = $zip->extractTo( __DIR__ );
	$zip->close();
	@unlink( $file );
	if ( ! $ok ) {
		exit( "Ошибка распаковки\n" );
	}
	// Заглушка Reg.ru открывается раньше index.php
	if ( is_file( __DIR__ . '/index.html' ) ) {
		@unlink( __DIR__ . '/index.html' );
	}
	echo "Распаковано файлов: $count\n";
	exit;
}

if ( 'db' === $action ) {
	$file = __DIR__ . '/arctik-dump.sql.gz';
	$sql  = @gzdecode( (string) @file_get_contents( $file ) );
	@unlink( $file );
	if ( ! $sql ) {
		exit( "Нет дампа\n" );
	}
	$link   = arctik_install_db();
	$tables = $link->query( 'SHOW TABLES' )->num_rows;
	if ( $tables > 0 ) {
		exit( "База не пустая (таблиц — $tables), импорт отменён\n" );
	}
	$done = 0;
	if ( $link->multi_query( $sql ) ) {
		do {
			$result = $link->store_result();
			if ( $result ) {
				$result->free();
			}
			$done++;
		} while ( $link->more_results() && $link->next_result() );
	}
	if ( $link->errno ) {
		exit( "Ошибка после запроса №$done: " . $link->error . "\n" );
	}
	echo 'Импорт готов: запросов — ', $done, ', таблиц — ', $link->query( 'SHOW TABLES' )->num_rows, "\n";
	exit;
}

echo "Неизвестное действие\n";
