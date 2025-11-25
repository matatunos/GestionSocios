ALTER TABLE tasks ADD COLUMN completed_at DATETIME DEFAULT NULL AFTER status;
ALTER TABLE tasks ADD COLUMN completed_by INT DEFAULT NULL AFTER completed_at;
ALTER TABLE tasks ADD CONSTRAINT fk_tasks_completed_by FOREIGN KEY (completed_by) REFERENCES users(id) ON DELETE SET NULL;

-- Migración: Crear tabla task_comments para comentarios en tareas
CREATE TABLE IF NOT EXISTS task_comments (
	id INT AUTO_INCREMENT PRIMARY KEY,
	task_id INT NOT NULL,
	user_id INT DEFAULT NULL,
	comment TEXT NOT NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
	FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;