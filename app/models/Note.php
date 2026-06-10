<?php

declare(strict_types=1);

class Note extends Model
{
    public function all(int $userId, array $filters = []): array
    {
        $where = ['n.user_id = :user_id'];
        $params = ['user_id' => $userId];

        $where[] = isset($filters['deleted']) && $filters['deleted'] === '1' ? 'n.is_deleted = 1' : 'n.is_deleted = 0';

        if (!empty($filters['search'])) {
            $where[] = '(n.title LIKE :search OR n.content LIKE :search OR EXISTS (
                SELECT 1 FROM note_tags nt
                INNER JOIN tags t ON t.id = nt.tag_id
                WHERE nt.note_id = n.id AND t.name LIKE :search
            ))';
            $params['search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['category_id'])) {
            $where[] = 'n.category_id = :category_id';
            $params['category_id'] = (int)$filters['category_id'];
        }

        if (!empty($filters['tag_id'])) {
            $where[] = 'EXISTS (SELECT 1 FROM note_tags nt WHERE nt.note_id = n.id AND nt.tag_id = :tag_id)';
            $params['tag_id'] = (int)$filters['tag_id'];
        }

        if (!empty($filters['priority'])) {
            $where[] = 'n.priority = :priority';
            $params['priority'] = $filters['priority'];
        }

        if (isset($filters['pinned']) && $filters['pinned'] !== '') {
            $where[] = 'n.is_pinned = :pinned';
            $params['pinned'] = (int)$filters['pinned'];
        }

        $sql = 'SELECT n.*, c.name AS category_name
                FROM notes n
                LEFT JOIN categories c ON c.id = n.category_id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY n.is_pinned DESC, n.updated_at DESC, n.created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $notes = $stmt->fetchAll();

        foreach ($notes as &$note) {
            $note['tags'] = $this->tagsForNote((int)$note['id'], $userId);
        }

        return $notes;
    }

    public function find(int $id, int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT n.*, c.name AS category_name
            FROM notes n
            LEFT JOIN categories c ON c.id = n.category_id
            WHERE n.id = :id AND n.user_id = :user_id');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $note = $stmt->fetch() ?: null;

        if ($note) {
            $note['tags'] = $this->tagsForNote($id, $userId);
        }

        return $note;
    }

    public function create(int $userId, array $data, array $tagIds): int
    {
        $stmt = $this->db->prepare('INSERT INTO notes
            (user_id, category_id, title, content, priority, image_path, is_pinned)
            VALUES (:user_id, :category_id, :title, :content, :priority, :image_path, :is_pinned)');
        $stmt->execute([
            'user_id' => $userId,
            'category_id' => $data['category_id'] ?: null,
            'title' => $data['title'],
            'content' => $data['content'],
            'priority' => $data['priority'],
            'image_path' => $data['image_path'],
            'is_pinned' => (int)$data['is_pinned'],
        ]);

        $noteId = (int)$this->db->lastInsertId();
        $this->syncTags($noteId, $userId, $tagIds);
        return $noteId;
    }

    public function update(int $id, int $userId, array $data, array $tagIds): bool
    {
        $stmt = $this->db->prepare('UPDATE notes SET
            category_id = :category_id,
            title = :title,
            content = :content,
            priority = :priority,
            image_path = :image_path,
            is_pinned = :is_pinned,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = :id AND user_id = :user_id');
        $ok = $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
            'category_id' => $data['category_id'] ?: null,
            'title' => $data['title'],
            'content' => $data['content'],
            'priority' => $data['priority'],
            'image_path' => $data['image_path'],
            'is_pinned' => (int)$data['is_pinned'],
        ]);

        $this->syncTags($id, $userId, $tagIds);
        return $ok;
    }

    public function softDelete(int $id, int $userId): bool
    {
        return $this->setDeleted($id, $userId, 1);
    }

    public function restore(int $id, int $userId): bool
    {
        return $this->setDeleted($id, $userId, 0);
    }

    public function forceDelete(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM notes WHERE id = :id AND user_id = :user_id');
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }

    public function togglePin(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare('UPDATE notes SET is_pinned = IF(is_pinned = 1, 0, 1), updated_at = CURRENT_TIMESTAMP WHERE id = :id AND user_id = :user_id');
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }

    public function counts(int $userId): array
    {
        $queries = [
            'notes' => 'SELECT COUNT(*) FROM notes WHERE user_id = :user_id AND is_deleted = 0',
            'important' => "SELECT COUNT(*) FROM notes WHERE user_id = :user_id AND priority = 'Important' AND is_deleted = 0",
            'pinned' => 'SELECT COUNT(*) FROM notes WHERE user_id = :user_id AND is_pinned = 1 AND is_deleted = 0',
            'deleted' => 'SELECT COUNT(*) FROM notes WHERE user_id = :user_id AND is_deleted = 1',
        ];

        $counts = [];
        foreach ($queries as $key => $sql) {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $userId]);
            $counts[$key] = (int)$stmt->fetchColumn();
        }

        return $counts;
    }

    private function setDeleted(int $id, int $userId, int $deleted): bool
    {
        $stmt = $this->db->prepare('UPDATE notes SET is_deleted = :deleted, updated_at = CURRENT_TIMESTAMP WHERE id = :id AND user_id = :user_id');
        return $stmt->execute(['deleted' => $deleted, 'id' => $id, 'user_id' => $userId]);
    }

    private function syncTags(int $noteId, int $userId, array $tagIds): void
    {
        $this->db->prepare('DELETE FROM note_tags WHERE note_id = :note_id')->execute(['note_id' => $noteId]);

        $validIds = [];
        foreach ($tagIds as $tagId) {
            $tagId = (int)$tagId;
            if ($tagId > 0) {
                $validIds[] = $tagId;
            }
        }

        if (!$validIds) {
            return;
        }

        $check = $this->db->prepare('SELECT id FROM tags WHERE user_id = :user_id AND id = :id');
        $insert = $this->db->prepare('INSERT IGNORE INTO note_tags (note_id, tag_id) VALUES (:note_id, :tag_id)');

        foreach (array_unique($validIds) as $tagId) {
            $check->execute(['user_id' => $userId, 'id' => $tagId]);
            if ($check->fetchColumn()) {
                $insert->execute(['note_id' => $noteId, 'tag_id' => $tagId]);
            }
        }
    }

    private function tagsForNote(int $noteId, int $userId): array
    {
        $stmt = $this->db->prepare('SELECT t.* FROM tags t
            INNER JOIN note_tags nt ON nt.tag_id = t.id
            WHERE nt.note_id = :note_id AND t.user_id = :user_id
            ORDER BY t.name ASC');
        $stmt->execute(['note_id' => $noteId, 'user_id' => $userId]);
        return $stmt->fetchAll();
    }
}

