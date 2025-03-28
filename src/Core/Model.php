<?php

namespace App\Core;

abstract class Model
{
    protected Database $db;
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $hidden = [];

    public function __construct()
    {
        $this->db = Application::getInstance()->getDatabase();
    }

    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $result = $this->db->query($sql, [$id])->fetch();
        
        return $result ? $this->filterAttributes($result) : null;
    }

    public function all(): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $results = $this->db->query($sql)->fetchAll();
        
        return array_map([$this, 'filterAttributes'], $results);
    }

    public function create(array $data): int
    {
        $data = $this->filterFillable($data);
        
        $columns = implode(', ', array_keys($data));
        $values = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$values})";
        
        $this->db->query($sql, array_values($data));
        
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $data = $this->filterFillable($data);
        
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE {$this->table} SET {$set} WHERE {$this->primaryKey} = ?";
        
        $values = array_values($data);
        $values[] = $id;
        
        $stmt = $this->db->query($sql, $values);
        
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->query($sql, [$id]);
        
        return $stmt->rowCount() > 0;
    }

    public function where(string $column, string $operator, $value): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} {$operator} ?";
        $results = $this->db->query($sql, [$value])->fetchAll();
        
        return array_map([$this, 'filterAttributes'], $results);
    }

    protected function filterAttributes(array $data): array
    {
        foreach ($this->hidden as $attribute) {
            unset($data[$attribute]);
        }
        return $data;
    }

    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }

    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->db->commit();
    }

    public function rollBack(): bool
    {
        return $this->db->rollBack();
    }
} 