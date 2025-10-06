// JavaScript untuk interaktivitas
document.addEventListener('DOMContentLoaded', function() {
    
    // Alert otomatis hilang setelah 5 detik
    var alerts = document.querySelectorAll('.alert');
    for(var i = 0; i < alerts.length; i++) {
        setTimeout(function() {
            var alert = new bootstrap.Alert(alerts[i]);
            alert.close();
        }, 5000);
    }
    
    // Password strength indicator
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strengthText = document.getElementById('password-strength');
            
            if (!strengthText) {
                const strengthDiv = document.createElement('div');
                strengthDiv.id = 'password-strength';
                strengthDiv.className = 'mt-2';
                this.parentElement.appendChild(strengthDiv);
            }
            
            let strength = 0;
            let message = '';
            let colorClass = '';
            
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            switch(strength) {
                case 0:
                case 1:
                    message = 'Weak password';
                    colorClass = 'text-danger';
                    break;
                case 2:
                case 3:
                    message = 'Medium password';
                    colorClass = 'text-warning';
                    break;
                case 4:
                case 5:
                    message = 'Strong password';
                    colorClass = 'text-success';
                    break;
            }
            
            const strengthElement = document.getElementById('password-strength');
            if (strengthElement && password.length > 0) {
                strengthElement.innerHTML = `<small class="${colorClass}"><i class="bi bi-shield-check"></i> ${message}</small>`;
            } else if (strengthElement) {
                strengthElement.innerHTML = '';
            }
        });
    }
    
    // Password confirmation validation
    const confirmPasswordInput = document.getElementById('confirm_password');
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            const password = document.getElementById('password') || document.getElementById('new_password');
            const confirmPassword = this;
            
            if (password && confirmPassword.value.length > 0) {
                if (password.value === confirmPassword.value) {
                    confirmPassword.classList.remove('is-invalid');
                    confirmPassword.classList.add('is-valid');
                } else {
                    confirmPassword.classList.remove('is-valid');
                    confirmPassword.classList.add('is-invalid');
                }
            } else {
                confirmPassword.classList.remove('is-valid', 'is-invalid');
            }
        });
    }
    
    // Form submission confirmation
    const forms = document.querySelectorAll('form[data-confirm]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const message = this.getAttribute('data-confirm');
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // Table row highlighting
    const tableRows = document.querySelectorAll('table tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('click', function(e) {
            if (e.target.tagName !== 'BUTTON' && e.target.tagName !== 'A') {
                this.classList.toggle('table-active');
            }
        });
    });
    
    // Search input auto-submit with debounce
    const searchInputs = document.querySelectorAll('input[name="search"]');
    searchInputs.forEach(input => {
        let timeout = null;
        input.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                // Auto-submit is disabled by default, can be enabled
                // this.form.submit();
            }, 500);
        });
    });
    
    // Tooltip initialization
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Popover initialization
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Add loading state to buttons on form submit
    const submitButtons = document.querySelectorAll('button[type="submit"]');
    submitButtons.forEach(button => {
        button.closest('form')?.addEventListener('submit', function(e) {
            if (this.checkValidity()) {
                button.disabled = true;
                const originalText = button.innerHTML;
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
                
                // Re-enable after 5 seconds as fallback
                setTimeout(() => {
                    button.disabled = false;
                    button.innerHTML = originalText;
                }, 5000);
            }
        });
    });
    
    // Session timeout warning
    let lastActivity = Date.now();
    const sessionTimeout = 1800000; // 30 minutes in milliseconds
    const warningTime = 300000; // 5 minutes before timeout
    
    function checkSessionTimeout() {
        const now = Date.now();
        const timeSinceLastActivity = now - lastActivity;
        
        if (timeSinceLastActivity >= sessionTimeout - warningTime && timeSinceLastActivity < sessionTimeout) {
            // Show warning (only once)
            if (!document.getElementById('session-warning')) {
                const warning = document.createElement('div');
                warning.id = 'session-warning';
                warning.className = 'alert alert-warning alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                warning.style.zIndex = '9999';
                warning.innerHTML = `
                    <i class="bi bi-exclamation-triangle"></i> 
                    Your session will expire in 5 minutes due to inactivity.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.appendChild(warning);
            }
        }
    }
    
    // Update last activity time
    ['mousedown', 'keydown', 'scroll', 'touchstart'].forEach(event => {
        document.addEventListener(event, () => {
            lastActivity = Date.now();
            const warning = document.getElementById('session-warning');
            if (warning) {
                warning.remove();
            }
        }, true);
    });
    
    // Check session timeout every minute
    setInterval(checkSessionTimeout, 60000);
    
    // Confirmation dialogs
    window.confirmDelete = function(userId, email) {
        if (confirm(`Are you sure you want to delete user: ${email}?\n\nThis action cannot be undone.`)) {
            document.getElementById('deleteUserId').value = userId;
            document.getElementById('deleteForm').submit();
        }
    };
    
    // Print functionality
    window.printPage = function() {
        window.print();
    };
    
    // Export table to CSV
    window.exportTableToCSV = function(tableId, filename) {
        const table = document.getElementById(tableId);
        const rows = table.querySelectorAll('tr');
        const csv = [];
        
        rows.forEach(row => {
            const cols = row.querySelectorAll('td, th');
            const csvRow = [];
            cols.forEach(col => {
                csvRow.push('"' + col.textContent.trim().replace(/"/g, '""') + '"');
            });
            csv.push(csvRow.join(','));
        });
        
        const csvContent = csv.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        link.setAttribute('href', url);
        link.setAttribute('download', filename || 'export.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };
    
    // Console welcome message
    console.log('%c User Management System ', 'background: #0d6efd; color: white; font-size: 16px; padding: 10px;');
    console.log('%c Developed for Tugas Kominfo ', 'background: #6c757d; color: white; font-size: 12px; padding: 5px;');
});
