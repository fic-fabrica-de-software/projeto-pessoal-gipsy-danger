// js/home.js (SISTEMA DE CONFIRMAÇÃO FUNCIONAL)
class MedSetDashboard {
    constructor() {
        this.init();
    }

    init() {
        this.loadAllData();
        this.setupEventListeners();
    }

    setupEventListeners() {
        $('#refreshMeds').on('click', () => this.loadTodayMeds());
        $('#refreshAppointments').on('click', () => this.loadUpcomingAppointments());
    }

    loadAllData() {
        this.loadStats();
        this.loadTodayMeds();
        this.loadUpcomingAppointments();
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
                console.error('Erro ao carregar consultas:', error);
            },
            complete: () => {
                $('#refreshAppointments').prop('disabled', false).html('<i class="bi bi-arrow-clockwise"></i>');
            }
        });
    }

    updateStatsUI(stats) {
        const container = $('#statsContainer');
        
        const html = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span>Medicamentos:</span>
                <span class="badge bg-light text-dark">${stats.total_meds || 0}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span>Consultas:</span>
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
            <hr>
            <small class="text-light">
                <i class="bi bi-info-circle"></i>
                Atualizado em ${new Date().toLocaleTimeString('pt-BR')}
            </small>
        `;
        
        container.html(html);
    }

    updateTodayMedsUI(medications) {
        const container = $('#todayMedsContainer');
        
        if (!medications || medications.length === 0) {
            container.html(`
                <tr>
                    <td colspan="4" class="text-center py-4">
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
            const isConfirmed = med.status === 'confirmed';
            const buttonHtml = isConfirmed ? 
                '<td colspan="2" class="text-end"><span class="text-success">Confirmado</span></td>' :
                `<td class="text-end"><button class="btn btn-success btn-sm confirm-btn" data-med-id="${med.med_id}">
                    <i class="bi bi-check-lg"></i> Confirmar
                </button></td>`;
            
            const rowClass = isConfirmed ? 'table-success' : '';
            
            html += `
                <tr class="${rowClass}">
                    <td>
                        <strong>${this.escapeHtml(med.med_name)}</strong>
                        ${med.doctor_name ? `<br><small class="text-muted">Por: ${this.escapeHtml(med.doctor_name)}</small>` : ''}
                    </td>
                    <td>${dosage}</td>
                    <td class="text-center">
                        <span class="badge ${isConfirmed ? 'bg-success' : 'bg-primary'}">${time}</span>
                    </td>
                        ${buttonHtml}
                    
                </tr>
            `;
        });
        
        container.html(html);
        
        $('.confirm-btn').on('click', (e) => {
            const medId = $(e.currentTarget).data('med-id');
            this.confirmMedication(medId, $(e.currentTarget));
        });
    }

    confirmMedication(medId, button) {
        const originalHtml = button.html();
        
        button.prop('disabled', true).html('<i class="bi bi-arrow-clockwise spinner-border spinner-border-sm"></i>');

        $.ajax({
            url: '../connections/confirm_med.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                med_id: medId
            }),
            success: (data) => {
                if (data.success) {
                    this.showAlert(data.message, 'success');
                    this.loadTodayMeds();
                    this.loadStats();
                } else {
                    this.showAlert(data.message, 'danger');
                    button.prop('disabled', false).html(originalHtml);
                }
            },
            error: (xhr, status, error) => {
                this.showAlert('Erro ao confirmar medicamento', 'danger');
                console.error('Erro:', error);
                button.prop('disabled', false).html(originalHtml);
            }
        });
    }

    updateAppointmentsUI(appointments) {
        const container = $('#upcomingAppointmentsContainer');
        
        if (!appointments || appointments.length === 0) {
            container.html(`
                <div class="text-center py-4">
                    <i class="bi bi-calendar-x display-4 text-muted"></i>
                    <p class="text-muted mt-2">Nenhuma consulta agendada</p>
                </div>
            `);
            return;
        }
        
        let html = '';
        appointments.forEach(appointment => {
            const date = new Date(appointment.appointment_date);
            const isToday = date.toDateString() === new Date().toDateString();
            
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
                            ${appointment.appointment_location ? `
                                <p class="mb-1"><strong>Local:</strong> ${this.escapeHtml(appointment.appointment_location)}</p>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.html(html);
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit'
        });
    }

    escapeHtml(text) {
        if (!text) return '';
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
        
        setTimeout(() => {
            alert.alert('close');
        }, 3000);
    }
}

const dashboard = new MedSetDashboard();