<?php
declare(strict_types = 1);

require_once(__DIR__ . '/../database/connection.db.php');
require_once(__DIR__ . '/../templates/sidebar.php');
?>

<link rel="stylesheet" href="../stylesheets/style.css">

<?php

function drawEditAnimal($csrf_token): void {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }


    $db = getDatabaseConnection();
    $userId = $_SESSION['user_id'];
    $animalId = isset($_GET['id']) ? intval($_GET['id']) : null;

    if (!$animalId) {
        die('Animal não especificado.' . ($_GET['id'] ?? 'Nenhum'));
    }

    $stmt = $db->prepare('SELECT animal_id, * FROM User_animals WHERE user_id = ? AND animal_id = ?');
    $stmt->execute([$userId, $animalId]);
    $animal = $stmt->fetch();

    if (!$animal) {
        die('Animal não encontrado.');
    }

    $speciesStmt = $db->query('SELECT animal_id, animal_name FROM Animal_types');
    $speciesList = $speciesStmt->fetchAll();

    ?>
    <main class="animals-layout">
        <aside class="side-nav">
            <?php drawNavbar(); ?>
        </aside>
        <section class="animals-content">
            <div class="form-container">
                <h2>Editar Animal</h2>
                <form action="../actions/action_editAnimal.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?>">
                    <input type="hidden" name="animal_id" value="<?= htmlspecialchars((string)$animal['animal_id']) ?>">
                    <label for="animal-picture">Fotografia</label>
                    <div class="upload-box">
                        <input type="file" id="animal-picture" name="animal-picture" accept="image/*">
                        <?php if (!empty($animal['animal_picture'])): ?>
                            <img src="<?= htmlspecialchars(str_replace('./', '../', $animal['animal_picture'])) ?>" alt="Animal" style="max-width:100px;">
                        <?php endif; ?>
                    </div>

                    <label for="name">Nome</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($animal['name']) ?>" required>

                    <label for="species">Espécie</label>
                    <select id="species" name="species" required>
                        <?php foreach ($speciesList as $species): ?>
                            <option value="<?= htmlspecialchars((string)$species['animal_id']) ?>"
                                <?= $species['animal_id'] == $animal['species'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($species['animal_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="age">Idade</label>
                    <input type="number" id="age" name="age" value="<?= htmlspecialchars((string)$animal['age']) ?>" required>

                <button type="submit">Salvar Alterações</button>
            </form>
            <form action="../actions/action_deleteAnimal.php" method="POST" onsubmit="return confirm('Tem certeza que deseja apagar este animal? Esta ação é irreversível.');" class="delete-animal-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?>">
                <input type="hidden" name="animal_id" value="<?= htmlspecialchars((string)$animal['animal_id']) ?>"> <button type="submit" id="erase-animal">Apagar Animal</button>
            </form>
        </div>
    </section>
</main>
<?php } ?>
