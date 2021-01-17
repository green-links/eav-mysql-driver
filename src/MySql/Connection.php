<?php
declare(strict_types=1);

namespace GreenLinks\EavMySql\MySql;

use PDOException;
use Throwable;
use PDO;

use function call_user_func;
use function sprintf;

class Connection extends Transaction
{
    private const DSN = 'mysql:dbname=%s;host=%s;port=%d';

    private Transaction $transaction;

    public function __construct(
        string $username,
        string $password,
        string $dbName,
        string $host = 'localhost',
        int $port = 3306
    ) {
        $pdo = $this->createPdo($username, $password, $dbName, $host, $port);

        $this->transaction = new Transaction($pdo);

        parent::__construct($pdo);
    }

    /**
     * @return mixed
     */
    public function transaction(callable $func)
    {
        $this->runPdoMethod('beginTransaction', 'Could not start transaction');

        try {
            $result = call_user_func($func, $this->transaction);
        } catch (Throwable $e) {
            $this->runPdoMethod('rollBack', 'Could not roll back transaction');

            throw new MySqlException('An error occurred during the transaction', $e);
        }

        $this->runPdoMethod('commit', 'Could not commit transaction');

        return $result;
    }

    private function createPdo(string $username, string $password, string $dbName, string $host, int $port): PDO
    {
        $dsn = sprintf(self::DSN, $dbName, $host, $port);

        try {
            $pdo = new PDO($dsn, $username, $password);
        } catch (PDOException $e) {
            throw new MySqlException(
                sprintf('Could not connect to database: "%s"', $dbName)
            );
        }

        if(!$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION)) {
            throw new MySqlException(
                sprintf('Could not configure PDO error mode: "%s"', $dbName)
            );
        }

        return $pdo;
    }

    private function runPdoMethod(string $method, string $message): void
    {
        $pdo = $this->fetchPdo();

        try {
            if (!call_user_func([$pdo, $method])) {
                throw new MySqlException($message);
            }
        } catch (PDOException $e) {
            throw new MySqlException($message);
        }
    }
}
