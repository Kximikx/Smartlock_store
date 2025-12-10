<?php
require_once '../config/database.php';

// Перевірка авторизації
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Обробка оновлення статусу
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    try {
        $conn = getDBConnection();
        $sql = "UPDATE contact_requests SET status = :status WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':status', $_POST['status']);
        $stmt->bindParam(':id', $_POST['id']);
        $stmt->execute();
        
        $success_message = 'Статус успішно оновлено';
    } catch(Exception $e) {
        $error_message = 'Помилка оновлення статусу';
    }
}

// Обробка видалення запиту
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    try {
        $conn = getDBConnection();
        $sql = "DELETE FROM contact_requests WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $_POST['id']);
        $stmt->execute();
        
        $success_message = 'Запит успішно видалено';
    } catch(Exception $e) {
        $error_message = 'Помилка видалення запиту';
    }
}

// Отримання фільтру статусу
$status_filter = $_GET['status'] ?? 'all';

// Отримання всіх запитів
try {
    $conn = getDBConnection();
    
    if ($status_filter === 'all') {
        $sql = "SELECT * FROM contact_requests ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
    } else {
        $sql = "SELECT * FROM contact_requests WHERE status = :status ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':status', $status_filter);
    }
    
    $stmt->execute();
    $requests = $stmt->fetchAll();
    
    // Отримання статистики
    $stats_sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_count,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_count,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count
                  FROM contact_requests";
    $stats_stmt = $conn->prepare($stats_sql);
    $stats_stmt->execute();
    $stats = $stats_stmt->fetch();
    
} catch(Exception $e) {
    error_log("Error: " . $e->getMessage());
    $requests = [];
    $stats = ['total' => 0, 'new_count' => 0, 'in_progress_count' => 0, 'completed_count' => 0];
}

$status_labels = [
    'new' => 'Новий',
    'in_progress' => 'В обробці',
    'completed' => 'Завершено',
    'cancelled' => 'Скасовано'
];

$status_colors = [
    'new' => '#4169FF',
    'in_progress' => '#F59E0B',
    'completed' => '#10B981',
    'cancelled' => '#EF4444'
];
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Адмін-панель - SmartLock</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        body {
            background: #F5F7FA;
        }
        .admin-header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 0;
            margin-bottom: 2rem;
        }
        .admin-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a202c;
        }
        .admin-user {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .btn-logout {
            padding: 0.5rem 1rem;
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 500;
        }
        .btn-logout:hover {
            background: #dc2626;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .stat-label {
            font-size: 0.875rem;
            color: #718096;
            margin-bottom: 0.5rem;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1a202c;
        }
        .filters {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        .filter-btn {
            padding: 0.5rem 1rem;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            color: #4a5568;
            transition: all 0.2s;
        }
        .filter-btn:hover {
            border-color: #4169FF;
            color: #4169FF;
        }
        .filter-btn.active {
            background: #4169FF;
            color: white;
            border-color: #4169FF;
        }
        .requests-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #f7fafc;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #2d3748;
            border-bottom: 2px solid #e2e8f0;
        }
        td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            color: #4a5568;
        }
        tr:hover {
            background: #f7fafc;
        }
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.875rem;
            font-weight: 500;
            color: white;
        }
        .actions {
            display: flex;
            gap: 0.5rem;
        }
        .btn-action {
            padding: 0.25rem 0.75rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .btn-view {
            background: #4169FF;
            color: white;
        }
        .btn-delete {
            background: #ef4444;
            color: white;
        }
        select.status-select {
            padding: 0.25rem 0.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.875rem;
        }
        .message-alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .message-success {
            background: #d1fae5;
            color: #065f46;
        }
        .message-error {
            background: #fee2e2;
            color: #991b1b;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }
        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
        }
        .btn-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #718096;
        }
        .detail-row {
            margin-bottom: 1rem;
        }
        .detail-label {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.25rem;
        }
        .detail-value {
            color: #4a5568;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <div class="admin-nav">
                <h1 class="admin-title">Адмін-панель SmartLock</h1>
                <div class="admin-user">
                    <span>Вітаємо, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</span>
                    <a href="logout.php" class="btn-logout">Вийти</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if (isset($success_message)): ?>
            <div class="message-alert message-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="message-alert message-error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Всього запитів</div>
                <div class="stat-value"><?php echo $stats['total']; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Нові</div>
                <div class="stat-value" style="color: #4169FF;"><?php echo $stats['new_count']; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">В обробці</div>
                <div class="stat-value" style="color: #F59E0B;"><?php echo $stats['in_progress_count']; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Завершено</div>
                <div class="stat-value" style="color: #10B981;"><?php echo $stats['completed_count']; ?></div>
            </div>
        </div>

        <div class="filters">
            <a href="?status=all" class="filter-btn <?php echo $status_filter === 'all' ? 'active' : ''; ?>">Всі</a>
            <a href="?status=new" class="filter-btn <?php echo $status_filter === 'new' ? 'active' : ''; ?>">Нові</a>
            <a href="?status=in_progress" class="filter-btn <?php echo $status_filter === 'in_progress' ? 'active' : ''; ?>">В обробці</a>
            <a href="?status=completed" class="filter-btn <?php echo $status_filter === 'completed' ? 'active' : ''; ?>">Завершено</a>
            <a href="?status=cancelled" class="filter-btn <?php echo $status_filter === 'cancelled' ? 'active' : ''; ?>">Скасовано</a>
        </div>

        <div class="requests-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Компанія</th>
                        <th>Контакт</th>
                        <th>Email</th>
                        <th>Телефон</th>
                        <th>Кількість</th>
                        <th>Дата</th>
                        <th>Статус</th>
                        <th>Дії</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($requests)): ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 2rem; color: #718096;">
                                Запитів не знайдено
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($requests as $request): ?>
                            <tr>
                                <td><?php echo $request['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($request['company']); ?></strong></td>
                                <td><?php echo htmlspecialchars($request['name']); ?></td>
                                <td><?php echo htmlspecialchars($request['email']); ?></td>
                                <td><?php echo htmlspecialchars($request['phone']); ?></td>
                                <td><?php echo htmlspecialchars($request['quantity'] ?: '-'); ?></td>
                                <td><?php echo date('d.m.Y H:i', strtotime($request['created_at'])); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="id" value="<?php echo $request['id']; ?>">
                                        <select name="status" class="status-select" onchange="this.form.submit()" style="background-color: <?php echo $status_colors[$request['status']]; ?>; color: white;">
                                            <?php foreach ($status_labels as $value => $label): ?>
                                                <option value="<?php echo $value; ?>" <?php echo $request['status'] === $value ? 'selected' : ''; ?>>
                                                    <?php echo $label; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <div class="actions">
                                        <button class="btn-action btn-view" onclick="viewDetails(<?php echo htmlspecialchars(json_encode($request)); ?>)">
                                            Переглянути
                                        </button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Ви впевнені, що хочете видалити цей запит?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $request['id']; ?>">
                                            <button type="submit" class="btn-action btn-delete">Видалити</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal для деталей -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Деталі запиту</h2>
                <button class="btn-close" onclick="closeModal()">&times;</button>
            </div>
            <div id="modalBody"></div>
        </div>
    </div>

    <script>
        function viewDetails(request) {
            const modal = document.getElementById('detailsModal');
            const modalBody = document.getElementById('modalBody');
            
            const statusLabels = {
                'new': 'Новий',
                'in_progress': 'В обробці',
                'completed': 'Завершено',
                'cancelled': 'Скасовано'
            };
            
            modalBody.innerHTML = `
                <div class="detail-row">
                    <div class="detail-label">ID запиту</div>
                    <div class="detail-value">#${request.id}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Компанія</div>
                    <div class="detail-value">${request.company}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Контактна особа</div>
                    <div class="detail-value">${request.name}${request.position ? ' (' + request.position + ')' : ''}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Email</div>
                    <div class="detail-value"><a href="mailto:${request.email}">${request.email}</a></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Телефон</div>
                    <div class="detail-value"><a href="tel:${request.phone}">${request.phone}</a></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Кількість замків</div>
                    <div class="detail-value">${request.quantity || 'Не вказано'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Повідомлення</div>
                    <div class="detail-value">${request.message || 'Повідомлення відсутнє'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Статус</div>
                    <div class="detail-value">${statusLabels[request.status]}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Дата створення</div>
                    <div class="detail-value">${new Date(request.created_at).toLocaleString('uk-UA')}</div>
                </div>
            `;
            
            modal.classList.add('show');
        }
        
        function closeModal() {
            document.getElementById('detailsModal').classList.remove('show');
        }
        
        // Закриття модалки при кліці поза нею
        window.onclick = function(event) {
            const modal = document.getElementById('detailsModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
