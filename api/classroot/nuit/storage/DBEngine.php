<?php
namespace nuit\storage;

interface DBEngine {

	const DB_NULL = 0x0;
	const DB_NOT_NULL = 0x1;

	const DB_ASC = 0x10;
	const DB_DESC = 0x11;

	const DB_ALL_COLUMNS = 0x100;
	const DB_WILDCARD_GREEDY = 0x101;
	const DB_WILDCARD_LAZY = 0x110;

	function insert(string $table): DBEngine;
	function columns(...$columns): DBEngine;
	function values(...$values): DBEngine;
	function where(string $column, $value): DBEngine;
	function and(string $column, $value): DBEngine;
	function limit(int $limit): DBEngine;
	function order(string $column, int $order): DBEngine;

	function update(string $table): DBEngine;
	function set(array $map): DBEngine;

	function delete(string $table): DBEngine;

	function select($what, string $table): DBEngine;

	function query();

	function getSingleValue($value);

	function getRowArray();
	function getRowJSON();
	function getRowObj();

	function getArray();
	function getJSON();
	function getObj();

	function custom(string $query);

	function describe(string $table);

	function getInsertId(): int;

	function getError(): string;
	function getErrno(): int;
}