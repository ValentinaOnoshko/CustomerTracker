<?php

namespace App\config;

use Dotenv\Dotenv;
use PDO;
use PDOException;

class Database {
	private string $host;
	private string $db_name;
	private string $username;
	private string $password;

	public function __construct() {
		$dotenv = Dotenv::createImmutable(__DIR__ . '/customerTracker');
		$dotenv->load();

		$this->host = $_ENV['DB_HOST'];
		$this->db_name = $_ENV['DB_NAME'];
		$this->username = $_ENV['DB_USER'];
		$this->password = $_ENV['DB_PASSWORD'];
	}

	public function getConnection(): PDO {
		try {
			$pdo = new PDO(
				"mysql:host=" . $this->host . ";dbname=" . $this->db_name,
				$this->username,
				$this->password
			);
			$pdo->exec("set names utf8");
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			return $pdo;
		} catch(PDOException $exception) {
			echo "Connection error: " . $exception->getMessage();
			throw $exception;
		}
	}
}