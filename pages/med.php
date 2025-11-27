<?php
session_start();
include("../lay/menu.php");

if (!isset($_SESSION["conected"]) || $_SESSION["conected"] != true) {
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/medatt.css">
    <title>Medicamentos - MedSet</title>
</head>

<body class="backgroundf min-vh-100">
    <div class="container-fluid py-4">
        <div id="alertContainer"></div>

        <div class="row">
            <div class="col-md-3">
                <div class="bg1 rounded p-4 text-white">
                    <h4 class="mb-4">Gerenciar</h4>
                    <button class="btn btn-warning w-100 mb-3" data-bs-toggle="modal" data-bs-target="#addMedModal">
                        <i class="bi bi-plus-circle"></i> Novo Medicamento
                    </button>
                    <div class="list-group">
                        <a href="doctors.php" class="list-group-item list-group-item-action bg3">
                            <i class="bi bi-person-badge"></i> Meus Médicos
                        </a>
                        <a href="appointments.php" class="list-group-item list-group-item-action bg3">
                            <i class="bi bi-calendar-check"></i> Minhas Consultas
                        </a>
                    </div>
                    
                    <div class="mt-4">
                        <h6 class="border-bottom pb-2">Filtros</h6>
                        <div class="btn-group-vertical w-100">
                            <button class="btn btn-outline-light btn-sm filter-btn active" data-filter="all">
                                Todos os Medicamentos
                            </button>
                            <button class="btn btn-outline-light btn-sm filter-btn" data-filter="today">
                                Para Hoje
                            </button>
                            <button class="btn btn-outline-light btn-sm filter-btn" data-filter="week">
                                Esta Semana
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="bgcont rounded p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="m-0">Meus Medicamentos</h3>
                        <div class="d-flex gap-2">
                            <div class="input-group" style="width: 250px;">
                                <input type="text" class="form-control" placeholder="Buscar..." id="searchInput">
                                <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <button class="btn btn-outline-primary" id="refreshMeds">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                    </div>

                    <div id="medicationsContainer">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Carregando...</span>
                            </div>
                            <p class="text-muted mt-2">Carregando medicamentos...</p>
                        </div>
                    </div>
                    <nav aria-label="Page navigation" id="paginationContainer" class="mt-4 d-none">
                        <ul class="pagination justify-content-center" id="paginationList">
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <?php include('../modals/add_med_modal.php'); ?>
    <script src="../js/medications.js"></script>
</body>
</html>