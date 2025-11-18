<?php

namespace App\config;

use Dotenv\Dotenv;
use Exception;
use PDO;
use PDOException;

class Database {
	private string $host;
	private string $db_name;
	private string $username;
	private string $password;

	public function __construct() {
		try {
			$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
			$dotenv->load();
		} catch (Exception) {
			// .env файл не найден, используем значения по умолчанию из docker-compose
		}

		$this->host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'mysql';
		$this->db_name = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'tracker';
		$this->username = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'user';
		$this->password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '111111';
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