// js/medications.js
class MedicationsManager {
    constructor() {
        this.currentPage = 1;
        this.currentFilter = 'all';
        this.currentSearch = '';
        this.init();
    }

    init() {
        this.loadMedications();
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Filtros
        $('.filter-btn').on('click', (e) => {
            $('.filter-btn').removeClass('active');
            $(e.currentTarget).addClass('active');
            this.currentFilter = $(e.currentTarget).data('filter');
            this.currentPage = 1;
            this.loadMedications();
        });

        // Busca
        $('#searchBtn').on('click', () => {
            this.currentSearch = $('#searchInput').val();
            this.currentPage = 1;
            this.loadMedications();
        });

        $('#searchInput').on('keypress', (e) => {
            if (e.which === 13) {
                this.currentSearch = $('#searchInput').val();
                this.currentPage = 1;
                this.loadMedications();
            }
        });

        // Refresh
        $('#refreshMeds').on('click', () => this.loadMedications());
    }

    loadMedications() {
        $('#refreshMeds').prop('disabled', true).html('<i class="bi bi-arrow-clockwise spinner-border spinner-border-sm"></i>');

        $.ajax({
            url: '../connections/get_medications.php',
            method: 'GET',
            data: {
                filter: this.currentFilter,
                page: this.currentPage,
                search: this.currentSearch
            },
            dataType: 'json',
            success: (data) => {
                if (data.success) {
                    this.updateMedicationsUI(data.medications);
                    this.updatePaginationUI(data.pagination);
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

    updateMedicationsUI(medications) {
        const container = $('#medicationsContainer');

        if (medications.length === 0) {
            container.html(`
            <div class="text-center py-5">
                <i class="bi bi-capsule display-1 text-muted"></i>
                <h4 class="text-muted mt-3">Nenhum medicamento encontrado</h4>
                <p class="text-muted">Tente alterar os filtros ou adicionar um novo medicamento</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMedModal">
                    Adicionar Medicamento
                </button>
            </div>
        `);
            return;
        }

        let html = `
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="bg4">
                    <tr>
                        <th>Medicamento</th>
                        <th>Dosagem</th>
                        <th>Horário</th>
                        <th>Dias</th>
                        <th>Estoque</th>
                        <th>Médico</th>
                        <th>Confirmado</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
    `;

        medications.forEach(med => {
            const times = med.times_taken || 0;
            const days = this.getWeekdayNames(med.med_weekdays);
            const stockStatus = this.getStockStatus(med.med_remaining, med.med_alert_days);

            html += `
            <tr>
                <td>
                    <strong>${this.escapeHtml(med.med_name)}</strong>
                    ${med.med_brand ? `<br><small class="text-muted">${this.escapeHtml(med.med_brand)}</small>` : ''}
                </td>
                <td>
                    ${med.med_dosage} ${med.med_type}<br>
                    <small class="text-muted">${med.med_milligram} ${med.med_milligram_unit}</small>
                </td>
                <td>${med.med_time.substring(0, 5)}</td>
                <td>
                    <span class="badge bg-secondary" title="${days}">${this.getShortWeekdays(med.med_weekdays)}</span>
                </td>
                <td>
                    <span class="badge ${stockStatus.class}" title="${stockStatus.text}">
                        ${med.med_remaining}
                    </span>
                    <small class="text-muted d-block">${med.med_acquisition_type}</small>
                </td>
                <td>
                    ${med.doctor_name ? `
                        ${this.escapeHtml(med.doctor_name)}
                        ${med.doctor_specialty ? `<br><small class="text-muted">${this.escapeHtml(med.doctor_specialty)}</small>` : ''}
                    ` : '<span class="text-muted">-</span>'}
                </td>
                <td>
                    <span class="badge bg-success">${times}x</span>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-info" title="Editar" onclick="medManager.editMedication(${med.med_id})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-outline-danger" title="Excluir" onclick="medManager.deleteMedication(${med.med_id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
        });

        html += `
                </tbody>
            </table>
        </div>
    `;

        container.html(html);
    }

    // Novas funções auxiliares
    getWeekdayNames(weekdaysString) {
        const daysMap = {
            '0': 'Domingo',
            '1': 'Segunda',
            '2': 'Terça',
            '3': 'Quarta',
            '4': 'Quinta',
            '5': 'Sexta',
            '6': 'Sábado'
        };

        const daysArray = weekdaysString.split(',');
        return daysArray.map(day => daysMap[day] || 'Desconhecido').join(', ');
    }

    getShortWeekdays(weekdaysString) {
        const daysMap = {
            '0': 'Dom',
            '1': 'Seg',
            '2': 'Ter',
            '3': 'Qua',
            '4': 'Qui',
            '5': 'Sex',
            '6': 'Sáb'
        };

        const daysArray = weekdaysString.split(',');
        return daysArray.map(day => daysMap[day] || '?').join(',');
    }

    getStockStatus(remaining, alertDays) {
        if (remaining === 0) {
            return { class: 'bg-danger', text: 'Estoque esgotado' };
        } else if (remaining <= alertDays) {
            return { class: 'bg-warning', text: 'Estoque baixo' };
        } else {
            return { class: 'bg-success', text: 'Estoque normal' };
        }
    }

    updatePaginationUI(pagination) {
        const container = $('#paginationContainer');
        const list = $('#paginationList');

        if (pagination.pages <= 1) {
            container.addClass('d-none');
            return;
        }

        container.removeClass('d-none');

        let html = '';

        // Previous button
        if (pagination.page > 1) {
            html += `
                <li class="page-item">
                    <a class="page-link" href="#" onclick="medManager.changePage(${pagination.page - 1})">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
            `;
        }

        // Page numbers
        for (let i = 1; i <= pagination.pages; i++) {
            if (i === pagination.page) {
                html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
            } else {
                html += `
                    <li class="page-item">
                        <a class="page-link" href="#" onclick="medManager.changePage(${i})">${i}</a>
                    </li>
                `;
            }
        }

        // Next button
        if (pagination.page < pagination.pages) {
            html += `
                <li class="page-item">
                    <a class="page-link" href="#" onclick="medManager.changePage(${pagination.page + 1})">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            `;
        }

        list.html(html);
    }

    changePage(page) {
        this.currentPage = page;
        this.loadMedications();
        $('html, body').animate({ scrollTop: 0 }, 'slow');
    }

    getWeekdayNames(weekday) {
        const days = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
        return days[weekday] || 'Desconhecido';
    }

    editMedication(medId) {
        // Implementar edição
        this.showAlert('Funcionalidade de edição em desenvolvimento', 'info');
    }

    deleteMedication(medId) {
        if (confirm('Tem certeza que deseja excluir este medicamento?')) {
            $.ajax({
                url: '../connections/delete_med.php',
                method: 'POST',
                data: { med_id: medId },
                success: (data) => {
                    if (data.success) {
                        this.showAlert('Medicamento excluído com sucesso', 'success');
                        this.loadMedications();
                    } else {
                        this.showAlert(data.message, 'danger');
                    }
                },
                error: () => {
                    this.showAlert('Erro ao excluir medicamento', 'danger');
                }
            });
        }
    }

    viewHistory(medId) {
        // Implementar visualização do histórico
        this.showAlert('Funcionalidade de histórico em desenvolvimento', 'info');
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

        setTimeout(() => {
            alert.alert('close');
        }, 5000);
    }
}

const medManager = new MedicationsManager();