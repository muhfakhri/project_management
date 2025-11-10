/**
 * Toast Notification System
 * Displays user-friendly notifications for actions and permission errors
 */

class ToastNotification {
    constructor() {
        this.container = null;
        this.init();
    }

    init() {
        // Create container if not exists
        if (!document.querySelector('.toast-container')) {
            this.container = document.createElement('div');
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        } else {
            this.container = document.querySelector('.toast-container');
        }
    }

    /**
     * Show a toast notification
     * @param {string} type - success, error, warning, info, permission
     * @param {string} title - Toast title
     * @param {string} message - Toast message
     * @param {number} duration - Auto dismiss duration in ms (0 = no auto dismiss)
     */
    show(type = 'info', title = '', message = '', duration = 5000) {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;

        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ',
            permission: '🔒'
        };

        const icon = icons[type] || icons.info;

        toast.innerHTML = `
            <div class="toast-icon">${icon}</div>
            <div class="toast-content">
                ${title ? `<div class="toast-title">${title}</div>` : ''}
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close" aria-label="Close">×</button>
            ${duration > 0 ? '<div class="toast-progress"></div>' : ''}
        `;

        this.container.appendChild(toast);

        // Close button
        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', () => this.dismiss(toast));

        // Show animation
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);

        // Auto dismiss
        if (duration > 0) {
            setTimeout(() => {
                this.dismiss(toast);
            }, duration);
        }

        return toast;
    }

    dismiss(toast) {
        toast.classList.remove('show');
        toast.classList.add('hide');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }

    // Convenience methods
    success(title, message, duration = 5000) {
        return this.show('success', title, message, duration);
    }

    error(title, message, duration = 5000) {
        return this.show('error', title, message, duration);
    }

    warning(title, message, duration = 5000) {
        return this.show('warning', title, message, duration);
    }

    info(title, message, duration = 5000) {
        return this.show('info', title, message, duration);
    }

    permission(title, message, duration = 6000) {
        return this.show('permission', title, message, duration);
    }
}

// Initialize global toast instance
window.toast = new ToastNotification();

// Laravel Flash Message Support
document.addEventListener('DOMContentLoaded', function() {
    // Check for Laravel flash messages
    const flashSuccess = document.querySelector('[data-flash-success]');
    const flashError = document.querySelector('[data-flash-error]');
    const flashWarning = document.querySelector('[data-flash-warning]');
    const flashInfo = document.querySelector('[data-flash-info]');

    if (flashSuccess) {
        const message = flashSuccess.getAttribute('data-flash-success');
        toast.success('Success', message);
    }

    if (flashError) {
        const message = flashError.getAttribute('data-flash-error');
        toast.error('Error', message);
    }

    if (flashWarning) {
        const message = flashWarning.getAttribute('data-flash-warning');
        toast.warning('Warning', message);
    }

    if (flashInfo) {
        const message = flashInfo.getAttribute('data-flash-info');
        toast.info('Info', message);
    }
});

// Permission denied interceptor for restricted links/buttons
document.addEventListener('click', function(e) {
    const target = e.target.closest('[data-permission-required]');
    if (target) {
        const permission = target.getAttribute('data-permission-required');
        const userRole = target.getAttribute('data-user-role');
        const allowedRoles = target.getAttribute('data-allowed-roles')?.split(',') || [];

        if (!allowedRoles.includes(userRole)) {
            e.preventDefault();
            const action = target.getAttribute('data-action-name') || 'this action';
            const requiredRole = allowedRoles.join(' or ');
            
            toast.permission(
                'Access Denied',
                `Only ${requiredRole} can ${action}.`,
                6000
            );
        }
    }
});
