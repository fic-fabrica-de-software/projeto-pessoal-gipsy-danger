<?php
require_once("../connections/db.php");

$user_id = $_SESSION["user_id"];
$doctors = [];

$doctor_stmt = $conn->prepare("SELECT doctor_id, doctor_name, doctor_specialty FROM doctors WHERE user_id = ? ORDER BY doctor_name");
$doctor_stmt->bind_param("i", $user_id);
$doctor_stmt->execute();
$doctor_result = $doctor_stmt->get_result();

while ($doctor = $doctor_result->fetch_assoc()) {
    $doctors[] = $doctor;
}
$doctor_stmt->close();
?>

<div class="modal fade" id="addMedModal" tabindex="-1" aria-labelledby="addMedModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMedModalLabel">Adicionar Medicamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addMedForm" method="POST">
                <div class="modal-body">
                    <div id="formAlerts"></div>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 text-primary">Informações Básicas</h6>

                            <div class="mb-3">
                                <label for="med_nome" class="form-label">Nome do Medicamento *</label>
                                <input type="text" class="form-control" id="med_nome" name="med_nome" required>
                            </div>

                            <div class="mb-3">
                                <label for="med_brand" class="form-label">Marca/Fabricante</label>
                                <input type="text" class="form-control" id="med_brand" name="med_brand">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Dosagem *</label>
                                <div class="row g-2">
                                    <div class="col-5">
                                        <input type="number" class="form-control" name="med_dosage"
                                            placeholder="Quantidade" min="0.1" step="0.1" required>
                                    </div>
                                    <div class="col-7">
                                        <select class="form-select" name="med_type" required>
                                            <option value="">Tipo...</option>
                                            <option value="comprimido">Comprimido</option>
                                            <option value="capsula">Cápsula</option>
                                            <option value="gotas">Gotas</option>
                                            <option value="ml">ML</option>
                                            <option value="mg">MG</option>
                                            <option value="aplicacao">Aplicação</option>
                                            <option value="injetavel">Injetável</option>
                                            <option value="pomada">Pomada</option>
                                            <option value="creme">Creme</option>
                                            <option value="supositorio">Supositório</option>
                                            <option value="spray">Spray</option>
                                            <option value="inalacao">Inalação</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Concentração *</label>
                                <div class="row g-2">
                                    <div class="col-5">
                                        <input type="number" class="form-control" name="med_milligram"
                                            placeholder="Ex: 500" step="0.1" required>
                                    </div>
                                    <div class="col-7">
                                        <select class="form-select" name="med_milligram_unit" required>
                                            <option value="mg">mg</option>
                                            <option value="g">g</option>
                                            <option value="mcg">mcg</option>
                                            <option value="ml">ml</option>
                                            <option value="UI">UI</option>
                                            <option value="%">%</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="doctor_id" class="form-label">Médico Prescritor</label>
                                <select class="form-select" id="doctor_id" name="doctor_id">
                                    <option value="">Selecione um médico...</option>
                                    <?php foreach ($doctors as $doctor): ?>
                                        <option value="<?= $doctor['doctor_id'] ?>">
                                            <?= htmlspecialchars($doctor['doctor_name']) ?> -
                                            <?= htmlspecialchars($doctor['doctor_specialty']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">
                                    <a href="doctors.php" target="_blank">Cadastrar novo médico</a>
                                </small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 text-primary">Horários e Frequência</h6>

                            <div class="mb-3">
                                <label class="form-label">Horário *</label>
                                <input type="time" class="form-control" name="med_time" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Dias da Semana *</label>
                                <div class="border rounded p-3">
                                    <?php
                                    $dias_semana = [
                                        'Domingo',
                                        'Segunda',
                                        'Terça',
                                        'Quarta',
                                        'Quinta',
                                        'Sexta',
                                        'Sábado'
                                    ];
                                    foreach ($dias_semana as $index => $dia) {
                                        echo "<div class='form-check'>
                                                <input class='form-check-input day-checkbox' type='checkbox' 
                                                       name='med_weekday[]' value='$index' id='dia$index'>
                                                <label class='form-check-label' for='dia$index'>$dia</label>
                                              </div>";
                                    }
                                    ?>
                                </div>
                                <small class="text-muted">Selecione pelo menos um dia</small>
                            </div>

                            <div class="mb-3">
                                <label for="med_frequency" class="form-label">Frequência</label>
                                <select class="form-select" id="med_frequency" name="med_frequency">
                                    <option value="diario">Diário</option>
                                    <option value="alternado">Dia sim, dia não</option>
                                    <option value="semanal">Semanal</option>
                                    <option value="mensal">Mensal</option>
                                    <option value="sob-demanda">Sob demanda</option>
                                </select>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label for="med_begindate" class="form-label">Data Início</label>
                                    <input type="date" class="form-control" id="med_begindate" name="med_begindate">
                                </div>
                                <div class="col-6">
                                    <label for="med_enddate" class="form-label">Data Término</label>
                                    <input type="date" class="form-control" id="med_enddate" name="med_enddate">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 text-primary">Controle de Estoque</h6>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="med_acquisition_type" class="form-label">Tipo de Aquisição *</label>
                                    <select class="form-select" id="med_acquisition_type" name="med_acquisition_type"
                                        required>
                                        <option value="comprado">Comprado</option>
                                        <option value="manipulado">Manipulado</option>
                                    </select>
                                    <small class="text-muted">
                                        <span id="acquisitionHelp" class="text-info">
                                            <i class="bi bi-info-circle"></i>
                                            Comprado: alerta 7 dias antes de acabar | Manipulado: alerta 14 dias antes
                                        </span>
                                    </small>
                                </div>

                                <div class="col-md-6">
                                    <label for="med_remaining" class="form-label">Quantidade Restante *</label>
                                    <input type="number" class="form-control" id="med_remaining" name="med_remaining"
                                        min="0" required placeholder="Ex: 30">
                                    <small class="text-muted">Quantidade atual em estoque</small>
                                </div>

                                <div class="col-md-4">
                                    <label for="med_alert_days" class="form-label">Dias para Alerta</label>
                                    <input type="number" class="form-control" id="med_alert_days" name="med_alert_days"
                                        min="1" max="30" value="7">
                                    <small class="text-muted">Dias antes de acabar para alertar</small>
                                </div>

                                <div class="col-md-4">
                                    <label for="med_expirydate" class="form-label">Data de Validade</label>
                                    <input type="date" class="form-control" id="med_expirydate" name="med_expirydate">
                                    <small class="text-muted">Opcional - para medicamentos com validade</small>
                                </div>

                                <div class="col-md-4">
                                    <label for="med_price" class="form-label">Preço (R$)</label>
                                    <input type="number" class="form-control" id="med_price" name="med_price"
                                        step="0.01" min="0" placeholder="0.00">
                                </div>

                                <div class="col-12">
                                    <label for="med_place_purchase" class="form-label">Local de Aquisição</label>
                                    <input type="text" class="form-control" id="med_place_purchase"
                                        name="med_place_purchase" placeholder="Farmácia, laboratório, etc.">
                                </div>

                                <div class="col-12">
                                    <label for="med_notes" class="form-label">Observações</label>
                                    <textarea class="form-control" id="med_notes" name="med_notes" rows="2"
                                        placeholder="Instruções especiais, efeitos colaterais, etc."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="bi bi-plus-circle"></i> Adicionar Medicamento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function calculateRemainingDays() {
        const remaining = parseInt($('#med_remaining').val()) || 0;
        const frequency = $('#med_frequency').val();

        if (remaining > 0) {
            let daysPerUnit = 1;

            switch (frequency) {
                case 'diario':
                    daysPerUnit = 1;
                    break;
                case 'alternado':
                    daysPerUnit = 2;
                    break;
                case 'semanal':
                    daysPerUnit = 7;
                    break;
                case 'mensal':
                    daysPerUnit = 30;
                    break;
            }

            const totalDays = remaining * daysPerUnit;
            const alertDays = parseInt($('#med_alert_days').val()) || 7;
            const daysUntilAlert = totalDays - alertDays;

            if (daysUntilAlert > 0) {
                $('#remainingDaysHelp').remove();
                $('#med_remaining').after(
                    `<small id="remainingDaysHelp" class="text-success">
                    <i class="bi bi-calendar-check"></i> 
                    Estoque dura aproximadamente ${totalDays} dias. Alerta em ${daysUntilAlert} dias.
                </small>`
                );
            }
        }
    }

    $('#med_remaining, #med_frequency, #med_alert_days').on('change', calculateRemainingDays);
    $(document).ready(function () {
        const today = new Date().toISOString().split('T')[0];
        $('#med_begindate').attr('min', today);
        $('#med_enddate').attr('min', today);
        $('#med_expirydate').attr('min', today);
        $('#addMedForm').on('submit', function (e) {
            e.preventDefault();
            const checkedDays = $('.day-checkbox:checked').length;
            if (checkedDays === 0) {
                showFormAlert('Selecione pelo menos um dia da semana', 'danger');
                return;
            }

            const beginDate = $('#med_begindate').val();
            const endDate = $('#med_enddate').val();
            if (beginDate && endDate && beginDate > endDate) {
                showFormAlert('A data de início não pode ser após a data de término', 'danger');
                return;
            }

            submitForm();
        });

        $('#addMedModal').on('hidden.bs.modal', function () {
            $('#formAlerts').empty();
            $('#addMedForm')[0].reset();
        });
    });

    function submitForm() {
        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();

        submitBtn.prop('disabled', true).html('<i class="bi bi-arrow-clockwise spinner-border spinner-border-sm"></i> Processando...');

        const formData = new FormData($('#addMedForm')[0]);

        $.ajax({
            url: '../connections/create_med.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (data) {
                if (data.success) {
                    showFormAlert(data.message, 'success');
                    setTimeout(() => {
                        $('#addMedModal').modal('hide');

                        if (typeof medManager !== 'undefined') {
                            medManager.loadMedications();
                        }
                        if (typeof dashboard !== 'undefined') {
                            dashboard.loadTodayMeds();
                            dashboard.loadStats();
                        }

                        if (typeof showAlert === 'function') {
                            showAlert(data.message, 'success');
                        }
                    }, 1500);
                } else {
                    showFormAlert(data.message, 'danger');
                }
            },
            error: function (xhr, status, error) {
                showFormAlert('Erro ao adicionar medicamento. Tente novamente.', 'danger');
                console.error('Erro:', error);
            },
            complete: function () {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    }

    function showFormAlert(message, type) {
        const alert = $(`
        <div class="alert alert-${type} alert-dismissible fade show">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);

        $('#formAlerts').html(alert);

        setTimeout(() => {
            alert.alert('close');
        }, 5000);
    }

    function validateDates() {
        const beginDate = $('#med_begindate').val();
        const endDate = $('#med_enddate').val();
        const expiryDate = $('#med_expirydate').val();
        const today = new Date().toISOString().split('T')[0];

        if (beginDate && beginDate < today) {
            showFormAlert('A data de início não pode ser no passado', 'warning');
            $('#med_begindate').val(today);
        }

        if (endDate && endDate < today) {
            showFormAlert('A data de término não pode ser no passado', 'warning');
            $('#med_enddate').val('');
        }

        if (expiryDate && expiryDate < today) {
            showFormAlert('A data de validade não pode ser no passado', 'warning');
            $('#med_expirydate').val('');
        }
    }

    $('#med_begindate, #med_enddate, #med_expirydate').on('change', validateDates);

    function selectAllDays() {
        $('.day-checkbox').prop('checked', true);
    }

    function clearAllDays() {
        $('.day-checkbox').prop('checked', false);
    }

    $('#med_acquisition_type').on('change', function () {
        const acquisitionType = $(this).val();
        const alertDaysInput = $('#med_alert_days');

        if (acquisitionType === 'manipulado') {
            alertDaysInput.val(14);
            $('#acquisitionHelp').html(
                '<i class="bi bi-info-circle"></i> Manipulado: alerta 14 dias antes de acabar (prazo maior para manipulação)'
            );
        } else {
            alertDaysInput.val(7);
            $('#acquisitionHelp').html(
                '<i class="bi bi-info-circle"></i> Comprado: alerta 7 dias antes de acabar (prazo menor para compra)'
            );
        }
    });
</script>