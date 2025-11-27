document.addEventListener('DOMContentLoaded', function() {
    const doctorSelect = document.getElementById('doctor_id');
    if (doctorSelect) {
        doctorsData.forEach(doctor => {
            const option = document.createElement('option');
            option.value = doctor.doctor_id;
            option.textContent = doctor.doctor_name;
            doctorSelect.appendChild(option);
        });
    }

    const upcomingContainer = document.getElementById('upcomingAppointments');
    if (upcomingContainer && upcomingAppointmentsData.length > 0) {
        upcomingAppointmentsData.forEach(appointment => {
            const appointmentCard = createAppointmentCard(appointment);
            upcomingContainer.appendChild(appointmentCard);
        });
    }

    const allAppointmentsContainer = document.getElementById('allAppointments');
    if (allAppointmentsContainer && appointmentsData.length > 0) {
        appointmentsData.forEach(appointment => {
            const appointmentRow = createAppointmentRow(appointment);
            allAppointmentsContainer.appendChild(appointmentRow);
        });
    }

    checkSessionAlerts();
});

function createAppointmentCard(appointment) {
    const col = document.createElement('div');
    col.className = 'col-md-6 col-lg-4 mb-3';
    
    const card = document.createElement('div');
    card.className = 'card h-100 border-primary';
    
    const cardBody = document.createElement('div');
    cardBody.className = 'card-body';
    
    const appointmentDate = new Date(appointment.appointment_date + 'T' + appointment.appointment_time);
    const today = new Date();
    const isToday = appointmentDate.toDateString() === today.toDateString();
    
    const dateClass = isToday ? 'text-warning' : 'text-primary';
    
    cardBody.innerHTML = `
        <div class="d-flex justify-content-between align-items-start mb-2">
            <h6 class="card-title ${dateClass}">
                ${isToday ? 'HOJE - ' : ''}${formatDate(appointment.appointment_date)}
            </h6>
            <span class="badge bg-primary">${appointment.appointment_time.substring(0, 5)}</span>
        </div>
        ${appointment.doctor_name ? `<p class="card-text mb-1"><strong>Médico:</strong> ${appointment.doctor_name}</p>` : ''}
        ${appointment.appointment_type ? `<p class="card-text mb-1"><strong>Tipo:</strong> ${getAppointmentType(appointment.appointment_type)}</p>` : ''}
        ${appointment.appointment_location ? `<p class="card-text mb-1"><strong>Local:</strong> ${appointment.appointment_location}</p>` : ''}
        ${appointment.appointment_notes ? `<p class="card-text"><small class="text-muted">${appointment.appointment_notes}</small></p>` : ''}
    `;
    
    card.appendChild(cardBody);
    col.appendChild(card);
    
    return col;
}

function createAppointmentRow(appointment) {
    const row = document.createElement('tr');
    
    const appointmentDate = new Date(appointment.appointment_date);
    const today = new Date();
    const isPast = appointmentDate < today;
    
    const dateClass = isPast ? 'text-muted' : '';
    
    row.innerHTML = `
        <td class="${dateClass}">${formatDate(appointment.appointment_date)}</td>
        <td class="${dateClass}">${appointment.appointment_time.substring(0, 5)}</td>
        <td class="${dateClass}">${getAppointmentType(appointment.appointment_type) || '-'}</td>
        <td class="${dateClass}">${appointment.doctor_name || '-'}</td>
        <td class="${dateClass}">${appointment.appointment_location || '-'}</td>
        <td>
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-info" title="Editar">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-outline-danger" title="Excluir">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </td>
    `;
    
    return row;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

function getAppointmentType(type) {
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

function checkSessionAlerts() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success')) {
        showAlert(urlParams.get('success'), 'success');
    }
    if (urlParams.get('error')) {
        showAlert(urlParams.get('error'), 'danger');
    }
}

function showAlert(message, type) {
    const alertContainer = document.getElementById('alertContainer');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    alertContainer.appendChild(alert);
}