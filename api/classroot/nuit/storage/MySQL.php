<?php
namespace nuit\storage;

use nuit\models\DynamicModel;

class MySQL implements DBEngine {
	
	private $query = "";

	/** @var \mysqli_result */
	private $result = null;

	/** @var \mysqli */
	private $sql;

	public function __construct(\mysqli $sql) {
		$this->sql = $sql;
	}

	public function insert(string $table): DBEngine {
		$this->query .= "INSERT INTO `$table` ";
		return $this;
	}
	
	public function columns(...$columns): DBEngine {
		$start = "(";

		for ($i = 0; $i < sizeof($columns); $i++) {
			if (!is_string($columns[$i]))
				throw new \Exception("A column in the query was not a string.");
			$start .= "`$columns[$i]`";
			if ($i + 1 < sizeof($columns))
				$start .= ",";
		}
		$start .= ")";

		$this->query .= "$start ";
		return $this;
	}

	/**
	 * @param array ...$values
	 *
	 * @return DBEngine
	 */
	public function values(...$values): DBEngine {
		$start = "VALUES (";

		for ($i = 0; $i < sizeof($values); $i++) {
			$start .= $this->stringify($values[$i]);
			if ($i + 1 < sizeof($values))
				$start .= ",";
		}

		$start .= ")";

		$this->query .= "$start ";
		return $this;
	}
	
	public function where(string $column, $value): DBEngine {
		$safe = $this->stringify($value);
		$this->query .= "WHERE `$column`=$safe ";
		return $this;
	}

	public function and (string $column, $value): DBEngine {
		$this->andor("AND", $column, $value);
		return $this;
	}

	public function or (string $column, $value): DBEngine {
		$this->andor("OR", $column, $value);
		return $this;
	}

	private function andor(string $which, string $column, $value) {
		$safe = $this->stringify($value);
		$str = "`$column`=$safe";

		$this->query .= "$which $str ";
	}

	public function limit(int $limit): DBEngine {
		$this->query .= "LIMIT $limit ";
		return $this;
	}

	public function order(string $column, int $order): DBEngine {
		$value = null;

		switch ($order) {
			case DBEngine::DB_ASC: {
				$value = "ASC";
				break;
			}
			case DBEngine::DB_DESC: {
				$value = "DESC";
				break;
			}
			default: {
				throw new \Exception("Invalid order value.");
			}
		}

		$this->query .= "ORDER BY `$column` $value ";
		return $this;
	}

	public function update(string $table): DBEngine {
		$this->query .= "UPDATE `$table` ";
		return $this;
	}

	public function set(array $map): DBEngine {
		$start = "SET ";

		$ct = 0;
		foreach ($map as $column => $value) {
			if (!is_string($column))
				throw new \Exception("A column in the query was not a string.");

			$safe = $this->stringify($value);
			$start .= "`$column`=$safe";

			if ($ct + 1 < sizeof($map))
				$start .= ",";

			$ct++;
		}
		unset($ct);

		$this->query .= "$start ";
		return $this;
	}

	public function delete(string $table): DBEngine {
		$this->query .= "DELETE FROM `$table` ";
		return $this;
	}

	public function select($what, string $table): DBEngine {
		$str = null;

		if ($what === DBEngine::DB_ALL_COLUMNS) {
			$str = "*";
		} else if (is_array($what)) {
			$str = "";

			$ct = 0;
			foreach ($what as $column) {
				if (!is_string($column))
					throw new \Exception("A column in the query was not a string.");

				$str .= "`$column`";
				if ($ct + 1 < sizeof($what))
					$str .= ",";
			}
			unset($ct);
		} else if (is_string($what)) {
			$str = "`$what`";
		}

		$this->query = "SELECT $str FROM `$table` ";
		return $this;
	}

	public function query() {
		$this->query .= ";";
		$this->result = $this->sql->query($this->query);
		$this->query = null;
	}

	public function custom(string $query) {
		$this->query = $query;
		$this->result = $this->sql->query($this->query);
	}

	public function __get($name) {
		return $this->sql->$name;
	}

	public function getResult() {
		return $this->result;
	}

	public function getSingleValue($str) {
		if (is_object($this->result) && $this->result->num_rows == 1) {
			$arr = $this->result->fetch_assoc();
			return $arr[$str];
		}
		return null;
	}

	public function getRowArray() {
		if (is_bool($this->result))
			return null;

		return $this->result->fetch_assoc();
	}

	public function getRowJSON() {
		if (is_bool($this->result))
			return null;

		return json_encode($this->result->fetch_assoc());
	}

	public function getRowObj() {
		if (is_bool($this->result))
			return null;

		return $this->result->fetch_object();
	}

	public function getArray() {
		return $this->result->fetch_all(MYSQLI_ASSOC);
	}

	public function getJSON() {
		return json_encode($this->result->fetch_all(MYSQLI_ASSOC));
	}

	public function getObj() {
		$res = [];

		$this->result->data_seek(0);

		if ($this->result->num_rows > 0)
			while ($row = $this->result->fetch_object())
				$res[] = $row;

		return $res;
	}

	private function stringify($var) {
		if (is_string($var)) {
			$sanitize = $this->sql->escape_string($var);
			return "'$sanitize'";
		} else if ($var instanceof \DateTime) {
            return "'".$var->format("Y-m-d H:i:s")."'";
        } else if ($var instanceof DynamicModel) {
            return "$var->id";
        } else if (is_bool($var)) {
            return $var ? "1" : "0";
        } else if (is_int($var)) {
		    return "$var";
        } else {
		    throw new \Exception("Had trouble serializing passed value. ".var_export($var));
        }
	}

	public function describe(string $table) {
		$this->query = "DESCRIBE `$table`;";
		$this->query();
	}

	public function getInsertId(): int {
		return $this->sql->insert_id;
	}

	public function getError(): string {
		return $this->sql->error;
	}

	public function getErrno(): int {
		return $this->sql->errno;
	}
}