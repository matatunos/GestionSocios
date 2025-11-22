<?php 
ob_start();
require_once __DIR__ . '/../../Helpers/Auth.php';

$poll = $data['poll'];
$options = $data['options'];
$hasVoted = $data['hasVoted'];
$userVotes = $data['userVotes'] ?? [];

// Calcular votos totales
$totalVotes = array_sum(array_column($options, 'votes'));

// Check if poll is active
$now = new DateTime();
$startDate = new DateTime($poll['start_date']);
$endDate = new DateTime($poll['end_date']);
$isActive = ($now >= $startDate && $now <= $endDate);

$title = $poll['title'];
?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-vote-yea"></i> <?php echo htmlspecialchars($poll['title']); ?>
        </h1>
        <div class="poll-meta">
            <span class="badge badge-<?php echo $isActive ? 'success' : 'secondary'; ?>">
                <?php echo $isActive ? 'Activa' : 'Cerrada'; ?>
            </span>
            <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($poll['start_date'])); ?></span>
            <span><i class="fas fa-calendar-times"></i> <?php echo date('d/m/Y H:i', strtotime($poll['end_date'])); ?></span>
            <span><i class="fas fa-user"></i> Creado por <?php echo htmlspecialchars($poll['creator_name']); ?></span>
        </div>
    </div>
    <div class="page-actions">
        <a href="index.php?page=polls" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
        <?php if (Auth::hasPermission('polls', 'delete') || Auth::user()['id'] == $poll['created_by']): ?>
            <?php if ($isActive): ?>
                <a href="index.php?page=polls&action=close&id=<?php echo $poll['id']; ?>" 
                   class="btn btn-warning" 
                   onclick="return confirm('¿Cerrar esta votación?')">
                    <i class="fas fa-lock"></i> Cerrar Votación
                </a>
            <?php endif; ?>
            <a href="index.php?page=polls&action=delete&id=<?php echo $poll['id']; ?>" 
               class="btn btn-danger" 
               onclick="return confirm('¿Eliminar esta votación? Esta acción no se puede deshacer.')">
                <i class="fas fa-trash"></i> Eliminar
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<?php if ($poll['description']): ?>
<div class="card">
    <div class="card-body">
        <h3><i class="fas fa-info-circle"></i> Descripción</h3>
        <p><?php echo nl2br(htmlspecialchars($poll['description'])); ?></p>
    </div>
</div>
<?php endif; ?>

<div class="poll-container">
    <!-- Voting Form (if active and not voted) -->
    <?php if ($isActive && !$hasVoted): ?>
    <div class="card vote-card">
        <div class="card-header">
            <h3><i class="fas fa-hand-paper"></i> Emitir Voto</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="index.php?page=polls&action=vote&id=<?php echo $poll['id']; ?>">
                <div class="options-list">
                    <?php foreach ($options as $option): ?>
                    <label class="option-label">
                        <input type="<?php echo $poll['allow_multiple'] ? 'checkbox' : 'radio'; ?>" 
                               name="<?php echo $poll['allow_multiple'] ? 'option_ids[]' : 'option_id'; ?>" 
                               value="<?php echo $option['id']; ?>" 
                               required>
                        <span><?php echo htmlspecialchars($option['option_text']); ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Enviar Voto
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Results -->
    <div class="card results-card">
        <div class="card-header">
            <h3><i class="fas fa-chart-bar"></i> Resultados</h3>
            <span class="total-votes"><?php echo $totalVotes; ?> voto<?php echo $totalVotes != 1 ? 's' : ''; ?></span>
        </div>
        <div class="card-body">
            <?php if ($totalVotes > 0): ?>
                <div class="results-list">
                    <?php foreach ($options as $option): 
                        $percentage = $totalVotes > 0 ? ($option['votes'] / $totalVotes * 100) : 0;
                        $isUserVote = in_array($option['id'], $userVotes);
                    ?>
                    <div class="result-item <?php echo $isUserVote ? 'user-voted' : ''; ?>">
                        <div class="result-header">
                            <span class="option-text">
                                <?php echo htmlspecialchars($option['option_text']); ?>
                                <?php if ($isUserVote && !$poll['is_anonymous']): ?>
                                    <i class="fas fa-check-circle" title="Tu voto"></i>
                                <?php endif; ?>
                            </span>
                            <span class="result-count"><?php echo $option['votes']; ?> voto<?php echo $option['votes'] != 1 ? 's' : ''; ?> (<?php echo number_format($percentage, 1); ?>%)</span>
                        </div>
                        <div class="result-bar">
                            <div class="result-fill" style="width: <?php echo $percentage; ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>Aún no hay votos registrados</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.poll-meta {
    display: flex;
    gap: 1.5rem;
    margin-top: 0.5rem;
    flex-wrap: wrap;
}

.poll-meta span {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-secondary);
}

.poll-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

.vote-card,
.results-card {
    height: fit-content;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 2px solid var(--border-light);
}

.card-header h3 {
    margin: 0;
    font-size: 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.total-votes {
    background: var(--primary-500);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: var(--radius-full);
    font-weight: 600;
    font-size: 0.9rem;
}

.options-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.option-label {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border: 2px solid var(--border-light);
    border-radius: var(--radius-lg);
    cursor: pointer;
    transition: all 0.2s;
}

.option-label:hover {
    border-color: var(--primary-500);
    background: var(--primary-50);
}

.option-label input {
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.option-label span {
    flex: 1;
    font-size: 1.05rem;
}

.results-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.result-item {
    padding: 1rem;
    background: var(--bg-secondary);
    border-radius: var(--radius-lg);
}

.result-item.user-voted {
    background: var(--success-50);
    border: 2px solid var(--success-500);
}

.result-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
    gap: 1rem;
}

.option-text {
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.option-text i {
    color: var(--success-500);
}

.result-count {
    color: var(--text-secondary);
    font-size: 0.95rem;
}

.result-bar {
    height: 30px;
    background: var(--bg-primary);
    border-radius: var(--radius-md);
    overflow: hidden;
}

.result-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary-500), var(--primary-600));
    transition: width 0.5s ease;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: var(--text-secondary);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

@media (max-width: 1024px) {
    .poll-container {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .poll-meta {
        font-size: 0.9rem;
    }
    
    .page-actions {
        width: 100%;
        display: flex;
        flex-wrap: wrap;
    }
    
    .page-actions .btn {
        flex: 1;
        min-width: 120px;
    }
}

/* Dark mode */
[data-theme="dark"] .card-header {
    border-bottom-color: var(--dark-border);
}

[data-theme="dark"] .option-label {
    border-color: var(--dark-border);
}

[data-theme="dark"] .option-label:hover {
    background: var(--dark-hover);
}

[data-theme="dark"] .result-item {
    background: var(--dark-secondary);
}

[data-theme="dark"] .result-item.user-voted {
    background: var(--success-900);
    border-color: var(--success-600);
}

[data-theme="dark"] .result-bar {
    background: var(--dark-bg);
}
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layout.php';
?>
