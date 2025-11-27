<?php
session_start();
include("../lay/menu.php");
require_once("../connections/db.php");
require_once("../connections/common_functions.php");

if (!isset($_SESSION["conected"]) || $_SESSION["conected"] != true) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$doctors = [];

$stmt = $conn->prepare("SELECT * FROM doctors WHERE user_id = ? ORDER BY doctor_name");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $doctors = $result->fetch_all(MYSQLI_ASSOC);
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/medatt.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Médicos - MedSet</title>
</head>

<body class="backgroundf min-vh-100">
    <div class="container-fluid py-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['success']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="row">
            <div class="col-12">
                <div class="bgcont rounded p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="m-0">Meus Médicos</h3>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDoctorModal">
                            <i class="bi bi-plus-circle"></i> Novo Médico
                        </button>
                    </div>

                    <?php if (empty($doctors)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-person-badge display-1 text-muted"></i>
                            <h4 class="text-muted mt-3">Nenhum médico cadastrado</h4>
                            <p class="text-muted">Adicione seus médicos para vincular aos medicamentos</p>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($doctors as $doctor): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title"><?= htmlspecialchars($doctor['doctor_name']) ?></h5>
                                            <?php if ($doctor['doctor_specialty']): ?>
                                                <p class="card-text"><strong>Especialidade:</strong> <?= htmlspecialchars($doctor['doctor_specialty']) ?></p>
                                            <?php endif; ?>
                                            <?php if ($doctor['doctor_phone']): ?>
                                                <p class="card-text"><strong>Telefone:</strong> <?= htmlspecialchars($doctor['doctor_phone']) ?></p>
                                            <?php endif; ?>
                                            <?php if ($doctor['doctor_email']): ?>
                                                <p class="card-text"><strong>Email:</strong> <?= htmlspecialchars($doctor['doctor_email']) ?></p>
                                            <?php endif; ?>
                                            <?php if ($doctor['doctor_address']): ?>
                                                <p class="card-text"><strong>Endereço:</strong> <?= htmlspecialchars($doctor['doctor_address']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-footer">
                                            <div class="btn-group w-100">
                                                <button class="btn btn-outline-primary btn-sm">Editar</button>
                                                <button class="btn btn-outline-danger btn-sm">Excluir</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addDoctorModal" tabindex="-1" aria-labelledby="addDoctorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addDoctorModalLabel">Adicionar Médico</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../connections/create_doctor.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="doctor_name" class="form-label">Nome do Médico *</label>
                            <input type="text" class="form-control" id="doctor_name" name="doctor_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="doctor_specialty" class="form-label">Especialidade</label>
                            <input type="text" class="form-control" id="doctor_specialty" name="doctor_specialty">
                        </div>
                        <div class="mb-3">
                            <label for="doctor_phone" class="form-label">Telefone</label>
                            <input type="tel" class="form-control" id="doctor_phone" name="doctor_phone">
                        </div>
                        <div class="mb-3">
                            <label for="doctor_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="doctor_email" name="doctor_email">
                        </div>
                        <div class="mb-3">
                            <label for="doctor_address" class="form-label">Endereço</label>
                            <textarea class="form-control" id="doctor_address" name="doctor_address" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Adicionar Médico</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>