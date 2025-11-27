<?php
// appointments.php
session_start();
include("../lay/menu.php");
require_once("../connections/db.php");
require_once("../connections/security_functions.php");

if (!isset($_SESSION["conected"]) || $_SESSION["conected"] != true) {
    header("Location: ../index.php");
    exit;
}

// Buscar consultas do usuário
$user_id = $_SESSION["user_id"];
$appointments = [];
$upcoming_appointments = [];

$stmt = $conn->prepare("
    SELECT a.*, d.doctor_name, d.doctor_specialty 
    FROM appointments a 
    LEFT JOIN doctors d ON a.doctor_id = d.doctor_id 
    WHERE a.user_id = ? 
    ORDER BY a.appointment_date, a.appointment_time
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $appointments = $result->fetch_all(MYSQLI_ASSOC);
    
    // Filtrar próximas consultas (próximos 30 dias)
    $today = date('Y-m-d');
    $next_month = date('Y-m-d', strtotime('+30 days'));
    
    foreach ($appointments as $appointment) {
        if ($appointment['appointment_date'] >= $today && $appointment['appointment_date'] <= $next_month) {
            $upcoming_appointments[] = $appointment;
        }
    }
}
$stmt->close();

// Preparar dados para o HTML
$has_appointments = !empty($appointments);
$has_upcoming = !empty($upcoming_appointments);
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
    <title>Consultas - MedSet</title>
</head>

<body class="backgroundf min-vh-100">
    <div class="container-fluid py-4">
        <!-- Alertas -->
        <div id="alertContainer"></div>

        <div class="row">
            <div class="col-12">
                <div class="bgcont rounded p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="m-0">Minhas Consultas</h3>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAppointmentModal">
                            <i class="bi bi-plus-circle"></i> Nova Consulta
                        </button>
                    </div>

                    <!-- Próximas Consultas -->
                    <div class="mb-5">
                        <h5 class="mb-3">Próximas Consultas</h5>
                        <?php if ($has_upcoming): ?>
                            <div class="row" id="upcomingAppointments">
                                <!-- As consultas serão carregadas via JavaScript -->
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-calendar-x display-4 text-muted"></i>
                                <p class="text-muted mt-2">Nenhuma consulta agendada para os próximos 30 dias</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Todas as Consultas -->
                    <div>
                        <h5 class="mb-3">Todas as Consultas</h5>
                        <?php if ($has_appointments): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="bg4">
                                        <tr>
                                            <th>Data</th>
                                            <th>Horário</th>
                                            <th>Tipo</th>
                                            <th>Médico</th>
                                            <th>Local</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody id="allAppointments">
                                        <!-- As consultas serão carregadas via JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-calendar-plus display-4 text-muted"></i>
                                <h5 class="text-muted mt-3">Nenhuma consulta agendada</h5>
                                <p class="text-muted">Comece agendando sua primeira consulta</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Adicionar Consulta -->
    <div class="modal fade" id="addAppointmentModal" tabindex="-1" aria-labelledby="addAppointmentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAppointmentModalLabel">Nova Consulta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="appointmentForm" action="../connections/create_appointment.php" method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="appointment_date" class="form-label">Data *</label>
                                <input type="date" class="form-control" id="appointment_date" name="appointment_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="appointment_time" class="form-label">Horário *</label>
                                <input type="time" class="form-control" id="appointment_time" name="appointment_time" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="appointment_type" class="form-label">Tipo de Consulta</label>
                            <select class="form-select" id="appointment_type" name="appointment_type">
                                <option value="">Selecione...</option>
                                <option value="consulta-rotina">Consulta de Rotina</option>
                                <option value="retorno">Retorno</option>
                                <option value="emergencia">Emergência</option>
                                <option value="exame">Exame</option>
                                <option value="cirurgia">Cirurgia</option>
                                <option value="terapia">Terapia</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="doctor_id" class="form-label">Médico</label>
                            <select class="form-select" id="doctor_id" name="doctor_id">
                                <option value="">Selecione um médico...</option>
                                <!-- Opções serão carregadas via JavaScript -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="appointment_location" class="form-label">Local</label>
                            <input type="text" class="form-control" id="appointment_location" name="appointment_location" placeholder="Hospital, clínica, etc.">
                        </div>
                        <div class="mb-3">
                            <label for="appointment_notes" class="form-label">Observações</label>
                            <textarea class="form-control" id="appointment_notes" name="appointment_notes" rows="3" placeholder="Sintomas, preparação necessária, etc."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Agendar Consulta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    
    <!-- Dados para JavaScript -->
    <script>
        const appointmentsData = <?= json_encode($appointments) ?>;
        const upcomingAppointmentsData = <?= json_encode($upcoming_appointments) ?>;
        const doctorsData = <?php 
            $doctors_stmt = $conn->prepare("SELECT doctor_id, doctor_name FROM doctors WHERE user_id = ?");
            $doctors_stmt->bind_param("i", $user_id);
            $doctors_stmt->execute();
            $doctors_result = $doctors_stmt->get_result();
            $doctors = [];
            while ($doctor = $doctors_result->fetch_assoc()) {
                $doctors[] = $doctor;
            }
            $doctors_stmt->close();
            echo json_encode($doctors);
        ?>;
    </script>
    
    <script src="../js/appointments.js"></script>
</body>
</html>