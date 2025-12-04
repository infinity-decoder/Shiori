<?php

class Field
{
    public static function getAll(bool $onlyActive = false): array
    {
        $pdo = DB::get();
        $sql = "SELECT * FROM fields";
        if ($onlyActive) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY order_index ASC";
        return $pdo->query($sql)->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $pdo = DB::get();
        $stmt = $pdo->prepare("SELECT * FROM fields WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = DB::get();
        $stmt = $pdo->prepare("
            INSERT INTO fields (name, label, type, options, is_active, is_custom, section, order_index)
            VALUES (:name, :label, :type, :options, :is_active, :is_custom, :section, :order_index)
        ");
        $stmt->execute([
            ':name' => $data['name'],
            ':label' => $data['label'],
            ':type' => $data['type'],
            ':options' => $data['options'] ?? null,
            ':is_active' => $data['is_active'] ?? 1,
            ':is_custom' => $data['is_custom'] ?? 1,
            ':section' => $data['section'] ?? 'main',
            ':order_index' => $data['order_index'] ?? 99,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $pdo = DB::get();
        $fields = [];
        $params = [':id' => $id];

        foreach ($data as $k => $v) {
            if (in_array($k, ['label', 'type', 'options', 'is_active', 'order_index'], true)) {
                $fields[] = "`$k` = :$k";
                $params[":$k"] = $v;
            }
        }

        if (empty($fields)) return;

        $sql = "UPDATE fields SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    public static function toggle(int $id): void
    {
        $pdo = DB::get();
        $stmt = $pdo->prepare("UPDATE fields SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);
    }
    
    public static function delete(int $id): void
    {
        $pdo = DB::get();
        // Only custom fields can be deleted
        $stmt = $pdo->prepare("DELETE FROM fields WHERE id = ? AND is_custom = 1");
        $stmt->execute([$id]);
    }
}
