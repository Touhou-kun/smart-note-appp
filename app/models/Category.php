<?php

declare(strict_types=1);

class Category extends Model
{
    public function all(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE user_id = :user_id ORDER BY name ASC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function find(int $id, int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE id = :id AND user_id = :user_id');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        return $stmt->fetch() ?: null;
    }

    public function create(int $userId, string $name): bool
    {
        $stmt = $this->db->prepare('INSERT INTO categories (user_id, name) VALUES (:user_id, :name)');
        return $stmt->execute(['user_id' => $userId, 'name' => $name]);
    }

    public function update(int $id, int $userId, string $name): bool
    {
        $stmt = $this->db->prepare('UPDATE categories SET name = :name WHERE id = :id AND user_id = :user_id');
        return $stmt->execute(['id' => $id, 'user_id' => $userId, 'name' => $name]);
    }

    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM categories WHERE id = :id AND user_id = :user_id');
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }

    public function count(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM categories WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        return (int)$stmt->fetchColumn();
    }
}

