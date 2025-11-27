<?php
session_start();
require_once("../connections/db.php");
require_once("../connections/common_functions.php");

if (!isset($_SESSION["conected"]) || $_SESSION["conected"] !== true) {
    header("Location: ../index.php");
    exit;
}

include('../lay/menu.php');
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/home.css">
    <title>Dashboard - MedSet</title>
</head>

<body class="bg2 min-vh-100 d-flex justify-content-center align-items-center p-0">
    <div class="container bgcont rounded d-flex flex-column gap-4 p-4 m-0">
        <div id="alertContainer"></div>

        <div class="w-100 d-flex align-items-center justify-content-between gap-5">
            <div class="bg1 w-25 rounded p-3 h-100">
                <div id="statsContainer" class="h-100">
                    <div class="text-center">
                        <div class="spinner-border spinner-border-sm t1" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                        <small class="t1">Carregando estatísticas...</small>
                    </div>
                </div>
            </div>

            <div class="w-100 h-100 d-flex justify-content-evenly">
                <div class="h-100 w-100 d-flex flex-column">
                    <div class="d-flex align-items-center justify-content-between">
                        <button class="btn p-0" data-bs-toggle="modal" data-bs-target="#addMedModal">
                            <i class="bi bi-plus-circle icon_plus"></i>
                        </button>
                        <h3 class="m-0 text-end">Medicamentos de Hoje</h3>
                        <button class="btn icon_re" id="refreshMeds">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                    <div class="bg3 rounded flex-grow-1">
                        <div class="table-container p-3 h-100">
                            <table class="table table-hover m-0 h-100">
                                <thead>
                                    <tr class="table_med">
                                        <th scope="col">Medicamento</th>
                                        <th scope="col">Dosagem</th>
                                        <th scope="col" class="text-center">Horário</th>
                                        <th scope="col" class="text-center">Status</th>
                                        <th scope="col" class="text-end">Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="todayMedsContainer">
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Carregando...</span>
                                            </div>
                                            <p class="text-muted mt-2">Carregando medicamentos...</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-100 d-flex align-items-bottom justify-content-between gap-5">
            <div class=" h-100 w-75 d-flex justify-content-evenly align-items-center">
                <div class="h-100 w-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="m-0">Próximas Consultas</h3>
                        <button class="btn icon_re" id="refreshAppointments">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                    <div class="bg4 rounded flex-grow-1 scrolly">
                        <div class="p-3" id="upcomingAppointmentsContainer">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Carregando...</span>
                                </div>
                                <p class="text-muted mt-2">Carregando consultas...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg1 w-25 h-100 rounded p-3">
                <div class="mt-4">
                    <h6 class="border-bottom pb-2">Notificações</h6>
                    <div id="notificationsContainer" class="small">
                    </div>
                </div>
            </div>

        </div>
    </div>

    <?php include('../modals/add_med_modal.php'); ?>

    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Medicamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmMessage">Deseja confirmar a tomada deste medicamento?</p>
                    <div class="mb-3">
                        <label for="confirmNotes" class="form-label">Observações (opcional):</label>
                        <textarea class="form-control" id="confirmNotes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="confirmTakeBtn">
                        <i class="bi bi-check-lg"></i> Confirmar
                    </button>
                    <button type="button" class="btn btn-warning" id="confirmSkipBtn">
                        <i class="bi bi-x-lg"></i> Pular
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script src="../js/home.js"></script>
</body>

</html>