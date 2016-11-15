
<?php

echo date('Y-m-d H:i:s');

// Connecting, selecting database
$dbconn = pg_connect ( "host=10.72.6.143 dbname=dba40818f4855403588ff015a3fcfecd3 user=uf704ee0c1f404e8cb52937553e2810d4 password=53639f87bbf940e5b15d8fe45864e165" ) or die ( 'Could not connect: ' . pg_last_error () );

// Performing SQL query
$query = 'select * from stock';
$result = pg_query ( $query ) or die ( 'Query failed: ' . pg_last_error () );

// Printing results in HTML
echo "<table>\n";
while ( $line = pg_fetch_array ( $result, null, PGSQL_ASSOC ) ) {
	echo "\t<tr>\n";
	foreach ( $line as $col_value ) {
		echo "\t\t<td>$col_value</td>\n";
	}
	echo "\t</tr>\n";
}
echo "</table>\n";

// Free resultset
pg_free_result ( $result );

// Closing connection
pg_close ( $dbconn );
?>