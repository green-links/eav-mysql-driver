<?php
declare(strict_types=1);

namespace GreenLinks\EavMySql\MySql;

use PDOException;
use PDOStatement;
use PDO;

use function gettype;
use function sprintf;

class Transaction
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function statement(string $sql, array $params = []): self
    {
        $this->createAndExecuteStatement($sql, $params);

        return $this;
    }

    public function query(string $sql, array $params = []): array
    {
        return $this->createAndExecuteStatement($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function fetchPdo(): PDO
    {
        return $this->pdo;
    }

    private function createAndExecuteStatement(string $sql, array $params = []): PDOStatement
    {
        $pdoStatement = $this->pdo->prepare($sql);

        if ($pdoStatement) {
            foreach ($params as $name => $value) {
                switch ($phpType = gettype($name)) {
                    case 'boolean':
                        $pdoType = PDO::PARAM_BOOL;
                        break;

                    case 'integer':
                        $pdoType = PDO::PARAM_INT;
                        break;

                    case 'NULL':
                        $pdoType = PDO::PARAM_NULL;
                        break;

                    case 'float':
                    case 'string':
                        $pdoType = PDO::PARAM_STR;
                        break;

                    default:
                        throw new MySqlException(sprintf('Invalid parameter type: "%s"', $phpType));
                }

                if (!$pdoStatement->bindParam($name, $value, $pdoType)) {
                    throw new MySqlException(
                        sprintf('Parameter could not be bound: "%s", "%s"', $name, $sql)
                    );
                }
            }

            try {
                $pdoStatement->execute();
            } catch (PDOException $e) {
                throw $this->createSqlException($sql);
            }

            return $pdoStatement;
        }

        throw $this->createSqlException($sql);
    }

    private function createSqlException(string $sql)
    {
        return new MySqlException(
            sprintf('SQL statement could not be executed: "%s".', $sql)
        );
    }
}
