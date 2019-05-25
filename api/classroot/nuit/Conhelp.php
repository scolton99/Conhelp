<?php
namespace nuit;

use nuit\storage\MySQL;

class Conhelp {
	private static $lastDb = null;

	public static function appRoot(): string {
		return self::getConfig()["app_root"];
	}

	public static function relAppRoot(): string {
		return self::getConfig()["rel_app_root"];
	}

	public static function getDB() {
		$config = self::getConfig();

		switch ($config["database"]) {
			case "MySQL": {
				if (self::$lastDb != null)
					return new MySQL(self::$lastDb);

				$username = $config["db_properties"]["username"];
				$password = $config["db_properties"]["password"];
				$database = $config["db_properties"]["database"];
				$port = $config["db_properties"]["port"];
				$host = $config["db_properties"]["host"];

				self::$lastDb = new \mysqli($host, $username, $password, $database, $port);
				return new MySQL(self::$lastDb);
			}
			default: {
				return null;
			}
		}
	}

	public static function setup() {
		session_start();

		$GLOBALS["_FLASH"] = $_SESSION["flash"] ?? null;
		unset($_SESSION["flash"]);

		spl_autoload_extensions(".php");
		spl_autoload_register("self::autoload");
	}

	public static function autoload($class) {
		$class = str_replace("\\", DIRECTORY_SEPARATOR, $class);

		if (file_exists((self::appRoot()."/api/classroot/").$class.".php")) {
			require_once((self::appRoot()."/api/classroot/").$class.".php");
		}
	}

	private static function getConfig(): array {
		$parseStr = file_get_contents(__DIR__."/../../config.json");
		return json_decode($parseStr, true);
	}

	public static function requireAdmin(string $message = "You need administrator permission to do that.") {
		if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == -1) {
			$_SESSION["flash"] = $message;
			header("Location: ".self::relAppRoot());
		}
	}

	public static function fullAppPath() {
		return "http://".self::getConfig()["site_domain"].self::getConfig()["rel_app_root"];
	}
}