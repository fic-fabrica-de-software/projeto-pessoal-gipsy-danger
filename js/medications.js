// js/medications.js (CORRIGIDO)
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
        
        console.log('Carregando medicamentos...');
        
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
                console.log('Dados recebidos:', data);
                if (data.success) {
                    this.updateMedicationsUI(data.medications);
                    this.updatePaginationUI(data.pagination);
                } else {
                    this.showAlert(data.message, 'danger');
                }
            },
            error: (xhr, status, error) => {
                console.error('Erro completo:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error
                });
                
                let errorMessage = 'Erro ao carregar medicamentos';
                if (xhr.responseText) {
                    // Tentar extrair mensagem de erro do HTML se for o caso
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = xhr.responseText;
                    const errorText = tempDiv.textContent || tempDiv.innerText || '';
                    if (errorText.includes('Error') || errorText.includes('Exception')) {
                        errorMessage += ': ' + errorText.substring(0, 100);
                    }
                }
                
                this.showAlert(errorMessage, 'danger');
            },
            complete: () => {
                $('#refreshMeds').prop('disabled', false).html('<i class="bi bi-arrow-clockwise"></i>');
            }
        });
    }

    updateMedicationsUI(medications) {
        const container = $('#medicationsContainer');
        
        if (!medications || medications.length === 0) {
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
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        medications.forEach(med => {
            const days = this.getShortWeekdays(med.med_weekdays);
            const stockStatus = this.getStockStatus(med.med_remaining, med.med_alert_days || 7);
            
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
                        <span class="badge bg-secondary">${days}</span>
                    </td>
                    <td>
                        <span class="badge ${stockStatus.class}">
                            ${med.med_remaining || 0}
                        </span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-info" title="Ver Detalhes" onclick="medManager.viewDetails(${med.med_id})">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-outline-warning" title="Editar" onclick="medManager.editMedication(${med.med_id})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-outline-danger" title="Excluir" onclick="medManager.deleteMedication(${med.med_id}, '${this.escapeHtml(med.med_name)}')">
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

    updatePaginationUI(pagination) {
        const container = $('#paginationContainer');
        const list = $('#paginationList');
        
        if (!pagination || pagination.pages <= 1) {
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

    viewDetails(medId) {
        console.log('Buscando detalhes do medicamento:', medId);
        
        $.ajax({
            url: '../connections/get_med_details.php',
            method: 'GET',
            data: { med_id: medId },
            dataType: 'json',
            success: (data) => {
                if (data.success) {
                    this.showMedicationDetails(data.medication);
                } else {
                    this.showAlert(data.message, 'danger');
                }
            },
            error: (xhr, status, error) => {
                console.error('Erro ao carregar detalhes:', error);
                this.showAlert('Erro ao carregar detalhes do medicamento', 'danger');
            }
        });
    }

    showMedicationDetails(medication) {
        const detailsHtml = `
            <div class="row">
                <div class="col-md-6">
                    <h5>Informações Básicas</h5>
                    <table class="table table-sm">
                        <tr><td><strong>Nome:</strong></td><td>${this.escapeHtml(medication.med_name)}</td></tr>
                        <tr><td><strong>Marca:</strong></td><td>${this.escapeHtml(medication.med_brand || 'Não informada')}</td></tr>
                        <tr><td><strong>Dosagem:</strong></td><td>${medication.med_dosage} ${medication.med_type}</td></tr>
                        <tr><td><strong>Concentração:</strong></td><td>${medication.med_milligram} ${medication.med_milligram_unit}</td></tr>
                        <tr><td><strong>Horário:</strong></td><td>${medication.med_time.substring(0, 5)}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5>Frequência e Estoque</h5>
                    <table class="table table-sm">
                        <tr><td><strong>Dias:</strong></td><td>${medication.weekdays_names.join(', ')}</td></tr>
                        <tr><td><strong>Tipo:</strong></td><td>${medication.med_acquisition_type || 'comprado'}</td></tr>
                        <tr><td><strong>Estoque:</strong></td><td>${medication.med_remaining || 0} unidades</td></tr>
                    </table>
                </div>
            </div>
            ${medication.doctor_name ? `
            <div class="row mt-3">
                <div class="col-12">
                    <h5>Médico Prescritor</h5>
                    <table class="table table-sm">
                        <tr><td><strong>Nome:</strong></td><td>${this.escapeHtml(medication.doctor_name)}</td></tr>
                        <tr><td><strong>Especialidade:</strong></td><td>${this.escapeHtml(medication.doctor_specialty || 'Não informada')}</td></tr>
                    </table>
                </div>
            </div>
            ` : ''}
            ${medication.med_notes ? `
            <div class="row mt-3">
                <div class="col-12">
                    <h5>Observações</h5>
                    <div class="border rounded p-2 bg-light">
                        ${this.escapeHtml(medication.med_notes)}
                    </div>
                </div>
            </div>
            ` : ''}
        `;

        // Criar modal de detalhes
        if ($('#medDetailsModal').length === 0) {
            $('body').append(`
                <div class="modal fade" id="medDetailsModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Detalhes do Medicamento</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body" id="medDetailsContent">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        }

        $('#medDetailsContent').html(detailsHtml);
        $('#medDetailsModal').modal('show');
    }

    deleteMedication(medId, medName) {
        if (confirm(`Tem certeza que deseja excluir o medicamento "${medName}"?`)) {
            $.ajax({
                url: '../connections/delete_med.php',
                method: 'POST',
                data: { med_id: medId },
                success: (data) => {
                    if (data.success) {
                        this.showAlert(data.message, 'success');
                        this.loadMedications();
                    } else {
                        this.showAlert(data.message, 'danger');
                    }
                },
                error: (xhr, status, error) => {
                    this.showAlert('Erro ao excluir medicamento', 'danger');
                }
            });
        }
    }

    editMedication(medId) {
        this.showAlert('Funcionalidade de edição em desenvolvimento', 'info');
    }

    // Helper methods
    getShortWeekdays(weekdaysString) {
        const daysMap = {
            '0': 'Dom', '1': 'Seg', '2': 'Ter', '3': 'Qua',
            '4': 'Qui', '5': 'Sex', '6': 'Sáb'
        };
        
        if (!weekdaysString) return '';
        const daysArray = weekdaysString.split(',');
        return daysArray.map(day => daysMap[day] || '?').join(',');
    }

    getStockStatus(remaining, alertDays) {
        remaining = remaining || 0;
        alertDays = alertDays || 7;
        
        if (remaining === 0) {
            return { class: 'bg-danger', text: 'Estoque esgotado' };
        } else if (remaining <= alertDays) {
            return { class: 'bg-warning', text: 'Estoque baixo' };
        } else {
            return { class: 'bg-success', text: 'Estoque normal' };
        }
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
        
        $('#alertContainer').html(alert);
        
        setTimeout(() => {
            alert.alert('close');
        }, 5000);
    }
}

const medManager = new MedicationsManager();