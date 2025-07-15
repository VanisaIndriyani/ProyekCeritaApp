// Flash Messages JavaScript Utility
// Reusable client-side flash message handling

class FlashMessages {
    constructor() {
        this.container = null;
        this.init();
    }

    init() {
        // Create container if it doesn't exist
        this.container = document.querySelector('.flash-messages');
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.className = 'flash-messages';
            document.body.appendChild(this.container);
        }

        // Auto-show existing messages on page load
        this.showExistingMessages();
        
        // Set up auto-dismiss for messages with data-auto-dismiss
        this.setupAutoDismiss();
    }

    showExistingMessages() {
        const messages = this.container.querySelectorAll('.flash-message');
        messages.forEach((message, index) => {
            setTimeout(() => {
                message.classList.add('show');
            }, index * 100); // Stagger animations
        });
    }

    setupAutoDismiss() {
        const messages = this.container.querySelectorAll('.flash-message[data-auto-dismiss="true"]');
        messages.forEach(message => {
            setTimeout(() => {
                this.dismissMessage(message);
            }, 4000);
        });
    }

    dismissMessage(messageElement) {
        messageElement.classList.add('hide');
        setTimeout(() => {
            if (messageElement.parentNode) {
                messageElement.parentNode.removeChild(messageElement);
            }
        }, 300);
    }

    show(type, message, autoDismiss = true) {
        const messageElement = this.createMessageElement(type, message, autoDismiss);
        this.container.appendChild(messageElement);
        
        // Trigger animation
        requestAnimationFrame(() => {
            messageElement.classList.add('show');
        });

        // Auto dismiss if enabled
        if (autoDismiss) {
            setTimeout(() => {
                this.dismissMessage(messageElement);
            }, 4000);
        }

        return messageElement;
    }

    success(message, autoDismiss = true) {
        return this.show('success', message, autoDismiss);
    }

    error(message, autoDismiss = true) {
        return this.show('error', message, autoDismiss);
    }

    warning(message, autoDismiss = true) {
        return this.show('warning', message, autoDismiss);
    }

    info(message, autoDismiss = true) {
        return this.show('info', message, autoDismiss);
    }

    createMessageElement(type, message, autoDismiss) {
        const element = document.createElement('div');
        element.className = `flash-message flash-${type}`;
        
        if (autoDismiss) {
            element.setAttribute('data-auto-dismiss', 'true');
        }

        // Add accessibility attributes
        element.setAttribute('role', 'alert');
        element.setAttribute('aria-live', type === 'error' ? 'assertive' : 'polite');

        const icon = this.getIconForType(type);
        
        element.innerHTML = `
            <span class="flash-icon">${icon}</span>
            <span class="flash-text">${this.escapeHtml(message)}</span>
            <button class="flash-close" onclick="flashMessages.dismissMessage(this.parentElement)">&times;</button>
        `;

        return element;
    }

    getIconForType(type) {
        switch (type) {
            case 'success':
                return '✓';
            case 'error':
                return '✗';
            case 'warning':
                return '⚠';
            case 'info':
            default:
                return 'ℹ';
        }
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    clear() {
        const messages = this.container.querySelectorAll('.flash-message');
        messages.forEach(message => {
            this.dismissMessage(message);
        });
    }
}

// Global instance
const flashMessages = new FlashMessages();

// Legacy compatibility functions (for backward compatibility with existing code)
function showSuccess(message, title = '') {
    const fullMessage = title ? `${title}: ${message}` : message;
    flashMessages.success(fullMessage);
}

function showError(message, title = '') {
    const fullMessage = title ? `${title}: ${message}` : message;
    flashMessages.error(fullMessage);
}

function showWarning(message, title = '') {
    const fullMessage = title ? `${title}: ${message}` : message;
    flashMessages.warning(fullMessage);
}

function showInfo(message, title = '') {
    const fullMessage = title ? `${title}: ${message}` : message;
    flashMessages.info(fullMessage);
}

// Export for modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FlashMessages;
}

// Make available globally
window.flashMessages = flashMessages;
window.showSuccess = showSuccess;
window.showError = showError;
window.showWarning = showWarning;
window.showInfo = showInfo;
