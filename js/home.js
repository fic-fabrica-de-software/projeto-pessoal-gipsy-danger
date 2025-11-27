// js/home.js (versão AJAX)
class MedSetDashboard {
    constructor() {
        this.currentMedId = null;
        this.init();
    }

    init() {
        this.loadAllData();
        this.setupEventListeners();
        this.setupAutoRefresh();
    }

    setupEventListeners() {
        // Botões de refresh
        $('#refreshMeds').on('click', () => this.loadTodayMeds());
        $('#refreshAppointments').on('click', () => this.loadUpcomingAppointments());

        // Modal de confirmação
        $('#confirmTakeBtn').on('click', () => this.confirmMedication('taken'));
        $('#confirmSkipBtn').on('click', () => this.confirmMedication('skipped'));

        // Auto-refresh a cada 30 segundos
        setInterval(() => this.loadAllData(), 30000);
    }

    setupAutoRefresh() {
        // Recarregar dados a cada 30 segundos
        setInterval(() => {
            this.loadStats();
            this.loadTodayMeds();
            this.loadUpcomingAppointments();
        }, 30000);
    }

    loadAllData() {
        this.loadStats();
        this.loadTodayMeds();
        this.loadUpcomingAppointments();
        this.loadNotifications();
    }

    loadStats() {
        $.ajax({
            url: '../connections/get_stats.php',
            method: 'GET',
            dataType: 'json',
            success: (data) => {
                if (data.success) {
                    this.updateStatsUI(data.stats);
                }
            },
            error: (xhr, status, error) => {
                console.error('Erro ao carregar estatísticas:', error);
            }
        });
    }

    loadTodayMeds() {
        $('#refreshMeds').prop('disabled', true).html('<i class="bi bi-arrow-clockwise spinner-border spinner-border-sm"></i>');

        $.ajax({
            url: '../connections/get_today_meds.php',
            method: 'GET',
            dataType: 'json',
            success: (data) => {
                if (data.success) {
                    this.updateTodayMedsUI(data.medications);
                } else {
                    this.showAlert(data.message, 'danger');
                }
            },
            error: (xhr, status, error) => {
                this.showAlert('Erro ao carregar medicamentos', 'danger');
                console.error('Erro:', error);
            },
            complete: () => {
                $('#refreshMeds').prop('disabled', false).html('<i class="bi bi-arrow-clockwise"></i>');
            }
        });
    }

    loadUpcomingAppointments() {
        $('#refreshAppointments').prop('disabled', true).html('<i class="bi bi-arrow-clockwise spinner-border spinner-border-sm"></i>');

        $.ajax({
            url: '../connections/get_upcoming_appointments.php',
            method: 'GET',
            dataType: 'json',
            success: (data) => {
                if (data.success) {
                    this.updateAppointmentsUI(data.appointments);
                }
            },
            error: (xhr, status, error) => {
                this.showAlert('Erro ao carregar consultas', 'danger');
                console.error('Erro:', error);
            },
            complete: () => {
                $('#refreshAppointments').prop('disabled', false).html('<i class="bi bi-arrow-clockwise"></i>');
            }
        });
    }

    loadNotifications() {
        $.ajax({
            url: '../connections/get_notifications.php',
            method: 'GET',
            dataType: 'json',
            success: (data) => {
                if (data.success) {
                    this.updateNotificationsUI(data.notifications);
                }
            }
        });
    }

    updateStatsUI(stats) {
        const html = `
            <div class="d-flex flex-column h-100">
                <div class="d-flex flex-column justify-content-between h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Medicamentos:</span>
                        <span class="badge bg-light text-dark">${stats.total_meds || 0}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Consultas Agendadas:</span>
                        <span class="badge bg-light text-dark">${stats.upcoming_apps || 0}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Médicos:</span>
                        <span class="badge bg-light text-dark">${stats.total_doctors || 0}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Confirmados Hoje:</span>
                        <span class="badge bg-light text-dark">${stats.today_taken || 0}</span>
                    </div>
                </div>
                <div>
                    <hr>
                    <small class="">
                        <i class="bi bi-info-circle"></i>
                        Atualizado em ${new Date().toLocaleTimeString('pt-BR')}
                    </small>
                </div>
            </div>
        `;
        $('#statsContainer').html(html);
    }

    updateTodayMedsUI(medications) {
        const container = $('#todayMedsContainer');

        if (medications.length === 0) {
            container.html(`
                <tr>
                    <td colspan="5" class="text-center py-4">
                        <i class="bi bi-capsule display-4 text-muted"></i>
                        <p class="text-muted mt-2">Nenhum medicamento para hoje</p>
                    </td>
                </tr>
            `);
            return;
        }

        let html = '';
        medications.forEach(med => {
            const time = med.med_time.substring(0, 5);
            const dosage = `${med.med_dosage} ${med.med_type} - ${med.med_milligram} ${med.med_milligram_unit}`;
            const isPast = this.isMedicationTimePast(med.med_time);
            const status = med.today_status || 'pending';
            const statusInfo = this.getStatusInfo(status);

            html += `
                <tr class="${status !== 'pending' ? 'table-success' : (isPast ? 'table-warning' : '')}">
                    <td>
                        <strong>${this.escapeHtml(med.med_name)}</strong>
                        ${med.doctor_name ? `<br><small class="text-muted">Por: ${this.escapeHtml(med.doctor_name)}</small>` : ''}
                    </td>
                    <td>${dosage}</td>
                    <td class="text-center">
                        <span class="badge ${isPast ? 'bg-secondary' : 'bg-primary'}">${time}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge ${statusInfo.class}">${statusInfo.text}</span>
                    </td>
                    <td class="text-end">
                        ${status === 'pending' ? `
                            <button class="btn btn-success btn-sm confirm-btn" data-med-id="${med.med_id}" data-med-name="${this.escapeHtml(med.med_name)}">
                                <i class="bi bi-check-lg"></i> Confirmar
                            </button>
                        ` : `
                            <span class="text-success">
                                <i class="bi bi-check2-all"></i> Confirmado
                            </span>
                        `}
                    </td>
                </tr>
            `;
        });

        container.html(html);

        // Re-bind event listeners
        $('.confirm-btn').on('click', (e) => {
            const medId = $(e.currentTarget).data('med-id');
            const medName = $(e.currentTarget).data('med-name');
            this.openConfirmModal(medId, medName);
        });
    }

    updateAppointmentsUI(appointments) {
        const container = $('#upcomingAppointmentsContainer');

        if (appointments.length === 0) {
            container.html(`
                <div class="text-center py-4">
                    <i class="bi bi-calendar-x display-4 text-muted"></i>
                    <p class="text-muted mt-2">Nenhuma consulta agendada para esta semana</p>
                </div>
            `);
            return;
        }

        let html = '';
        appointments.forEach(appointment => {
            const date = new Date(appointment.appointment_date);
            const today = new Date();
            const isToday = date.toDateString() === today.toDateString();

            html += `
                <div class="appointment-item mb-3 p-3 border rounded bg-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="mb-1 ${isToday ? 'text-warning' : 'text-dark'}">
                                ${isToday ? 'HOJE - ' : ''}${this.formatDate(appointment.appointment_date)}
                                <span class="badge bg-primary ms-2">${appointment.appointment_time.substring(0, 5)}</span>
                            </h6>
                            ${appointment.doctor_name ? `
                                <p class="mb-1"><strong>Médico:</strong> ${this.escapeHtml(appointment.doctor_name)}</p>
                            ` : ''}
                            ${appointment.appointment_type ? `
                                <p class="mb-1"><strong>Tipo:</strong> ${this.getAppointmentType(appointment.appointment_type)}</p>
                            ` : ''}
                            ${appointment.appointment_location ? `
                                <p class="mb-1"><strong>Local:</strong> ${this.escapeHtml(appointment.appointment_location)}</p>
                            ` : ''}
                            ${appointment.appointment_notes ? `
                                <p class="mb-0 text-muted"><small>${this.escapeHtml(appointment.appointment_notes)}</small></p>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
        });

        container.html(html);
    }

    updateNotificationsUI(notifications) {
        const container = $('#notificationsContainer');

        if (notifications.length === 0) {
            container.html('<p class="text-light">Nenhuma notificação</p>');
            return;
        }

        let html = '';
        notifications.forEach(notification => {
            html += `
                <div class="notification-item mb-2 p-2 rounded ${notification.is_read ? 'bg-light text-dark' : 'bg-warning'}">
                    <strong>${this.escapeHtml(notification.title)}</strong>
                    <p class="mb-0 small">${this.escapeHtml(notification.message)}</p>
                    <small class="text-muted">${this.formatTime(notification.created_at)}</small>
                </div>
            `;
        });

        container.html(html);
    }

    openConfirmModal(medId, medName) {
        this.currentMedId = medId;
        $('#confirmMessage').html(`Deseja confirmar a tomada de <strong>${medName}</strong>?`);
        $('#confirmNotes').val('');
        $('#confirmModal').modal('show');
    }

    confirmMedication(status) {
        if (!this.currentMedId) return;

        const notes = $('#confirmNotes').val();
        const button = status === 'taken' ? $('#confirmTakeBtn') : $('#confirmSkipBtn');
        const originalHtml = button.html();

        button.prop('disabled', true).html('<i class="bi bi-arrow-clockwise spinner-border spinner-border-sm"></i>');

        $.ajax({
            url: '../connections/confirm_med.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                med_id: this.currentMedId,
                status: status,
                notes: notes
            }),
            success: (data) => {
                if (data.success) {
                    this.showAlert(data.message, 'success');
                    $('#confirmModal').modal('hide');
                    this.loadTodayMeds();
                    this.loadStats();
                } else {
                    this.showAlert(data.message, 'danger');
                }
            },
            error: (xhr, status, error) => {
                this.showAlert('Erro ao confirmar medicamento', 'danger');
                console.error('Erro:', error);
            },
            complete: () => {
                button.prop('disabled', false).html(originalHtml);
            }
        });
    }

    // Helper methods
    isMedicationTimePast(medTime) {
        const now = new Date();
        const [hours, minutes] = medTime.split(':');
        const medDateTime = new Date();
        medDateTime.setHours(parseInt(hours), parseInt(minutes), 0, 0);
        return now > medDateTime;
    }

    getStatusInfo(status) {
        const statusMap = {
            'pending': { class: 'bg-secondary', text: 'Pendente' },
            'taken': { class: 'bg-success', text: 'Tomado' },
            'skipped': { class: 'bg-warning', text: 'Pulado' },
            'missed': { class: 'bg-danger', text: 'Esquecido' }
        };
        return statusMap[status] || statusMap.pending;
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('pt-BR', {
            weekday: 'short',
            day: '2-digit',
            month: '2-digit'
        });
    }

    formatTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleTimeString('pt-BR', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    getAppointmentType(type) {
        const types = {
            'consulta-rotina': 'Consulta de Rotina',
            'retorno': 'Retorno',
            'emergencia': 'Emergência',
            'exame': 'Exame',
            'cirurgia': 'Cirurgia',
            'terapia': 'Terapia'
        };
        return types[type] || type;
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    showAlert(message, type) {
        const alert = $(`
            <div class="alert alert-${type} alert-dismissible fade show">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);

        $('#alertContainer').append(alert);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            alert.alert('close');
        }, 5000);
    }
}

// Initialize dashboard when document is ready
$(document).ready(function () {
    new MedSetDashboard();
});

class StockAlertManager {
    constructor() {
        this.init();
    }

    init() {
        this.loadStockAlerts();
        setInterval(() => this.loadStockAlerts(), 60000); // Verificar a cada minuto
    }

    loadStockAlerts() {
        $.ajax({
            url: '../connections/get_stock_alerts.php',
            method: 'GET',
            dataType: 'json',
            success: (data) => {
                if (data.success) {
                    this.updateAlertsUI(data.alerts);
                }
            },
            error: (error) => {
                console.error('Erro ao carregar alertas:', error);
            }
        });
    }

    updateAlertsUI(alerts) {
        const container = $('#stockAlertsContainer');

        if (!container.length) {
            // Criar container se não existir
            $('.bg1 .mt-4').first().after(`
                <div class="mt-4">
                    <h6 class="border-bottom pb-2 text-warning">Alertas de Estoque</h6>
                    <div id="stockAlertsContainer"></div>
                </div>
            `);
        }

        const alertsContainer = $('#stockAlertsContainer');

        if (alerts.length === 0) {
            alertsContainer.html('<p class="text-light small">✅ Estoque em dia</p>');
            return;
        }

        let html = '';
        alerts.forEach(alert => {
            const alertClass = alert.alert_type === 'out_of_stock' ? 'bg-danger' :
                alert.alert_type === 'low_stock' ? 'bg-warning' : 'bg-info';

            html += `
                <div class="alert ${alertClass} alert-dismissible fade show p-2 mb-2" role="alert">
                    <small class="d-block">${alert.alert_message}</small>
                    <button type="button" class="btn-close btn-close-white" data-alert-id="${alert.alert_id}"></button>
                </div>
            `;
        });

        alertsContainer.html(html);

        // Adicionar event listeners para fechar alertas
        $('[data-alert-id]').on('click', (e) => {
            const alertId = $(e.currentTarget).data('alert-id');
            this.markAlertAsRead(alertId, $(e.currentTarget).closest('.alert'));
        });
    }

    markAlertAsRead(alertId, alertElement) {
        $.ajax({
            url: '../connections/mark_alert_read.php',
            method: 'POST',
            data: { alert_id: alertId },
            success: (data) => {
                if (data.success) {
                    alertElement.alert('close');
                }
            }
        });
    }
}

// Inicializar no dashboard
$(document).ready(function () {
    new StockAlertManager();
});