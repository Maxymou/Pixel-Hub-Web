// Gestionnaire de thème
class ThemeManager {
    constructor() {
        this.theme = localStorage.getItem('theme') || 'light';
        this.init();
    }

    init() {
        document.documentElement.setAttribute('data-bs-theme', this.theme);
        this.updateThemeButton();
    }

    toggle() {
        this.theme = this.theme === 'light' ? 'dark' : 'light';
        document.documentElement.setAttribute('data-bs-theme', this.theme);
        localStorage.setItem('theme', this.theme);
        this.updateThemeButton();
    }

    updateThemeButton() {
        const button = document.getElementById('theme-toggle');
        if (button) {
            button.innerHTML = this.theme === 'light' 
                ? '<i class="fas fa-moon"></i>' 
                : '<i class="fas fa-sun"></i>';
        }
    }
}

// Gestionnaire de widgets
class WidgetManager {
    constructor() {
        this.widgets = new Map();
        this.init();
    }

    init() {
        this.loadWidgets();
        this.setupDragAndDrop();
    }

    loadWidgets() {
        document.querySelectorAll('.widget').forEach(widget => {
            const id = widget.dataset.widgetId;
            if (id) {
                this.widgets.set(id, {
                    element: widget,
                    position: widget.dataset.position || 0
                });
            }
        });
    }

    setupDragAndDrop() {
        document.querySelectorAll('.widget').forEach(widget => {
            widget.draggable = true;
            widget.addEventListener('dragstart', this.handleDragStart.bind(this));
            widget.addEventListener('dragend', this.handleDragEnd.bind(this));
            widget.addEventListener('dragover', this.handleDragOver.bind(this));
            widget.addEventListener('drop', this.handleDrop.bind(this));
        });
    }

    handleDragStart(e) {
        e.target.classList.add('dragging');
        e.dataTransfer.setData('text/plain', e.target.dataset.widgetId);
    }

    handleDragEnd(e) {
        e.target.classList.remove('dragging');
    }

    handleDragOver(e) {
        e.preventDefault();
        const widget = e.target.closest('.widget');
        if (widget) {
            widget.classList.add('drop-target');
        }
    }

    handleDrop(e) {
        e.preventDefault();
        const widget = e.target.closest('.widget');
        if (widget) {
            widget.classList.remove('drop-target');
            const draggedId = e.dataTransfer.getData('text/plain');
            const draggedWidget = this.widgets.get(draggedId);
            if (draggedWidget) {
                this.reorderWidgets(draggedWidget.element, widget);
            }
        }
    }

    reorderWidgets(draggedWidget, targetWidget) {
        const container = draggedWidget.parentElement;
        const draggedRect = draggedWidget.getBoundingClientRect();
        const targetRect = targetWidget.getBoundingClientRect();
        
        if (draggedRect.top < targetRect.top) {
            container.insertBefore(draggedWidget, targetWidget);
        } else {
            container.insertBefore(draggedWidget, targetWidget.nextSibling);
        }

        this.saveWidgetOrder();
    }

    saveWidgetOrder() {
        const order = Array.from(document.querySelectorAll('.widget')).map((widget, index) => ({
            id: widget.dataset.widgetId,
            position: index
        }));

        fetch('/api/widgets/reorder', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ order })
        }).catch(error => console.error('Erreur lors de la sauvegarde de l\'ordre:', error));
    }
}

// Gestionnaire de notifications
class NotificationManager {
    constructor() {
        this.container = document.getElementById('notifications');
        this.init();
    }

    init() {
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.id = 'notifications';
            this.container.className = 'position-fixed top-0 end-0 p-3';
            document.body.appendChild(this.container);
        }
    }

    show(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show`;
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        this.container.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    success(message) {
        this.show(message, 'success');
    }

    error(message) {
        this.show(message, 'danger');
    }

    warning(message) {
        this.show(message, 'warning');
    }
}

// Gestionnaire de formulaires
class FormManager {
    constructor() {
        this.init();
    }

    init() {
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', this.handleSubmit.bind(this));
        });
    }

    handleSubmit(e) {
        const form = e.target;
        const submitButton = form.querySelector('button[type="submit"]');
        
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Chargement...';
        }

        // Simuler une requête AJAX
        setTimeout(() => {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerHTML = 'Soumettre';
            }
        }, 1000);
    }
}

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
    const themeManager = new ThemeManager();
    const widgetManager = new WidgetManager();
    const notificationManager = new NotificationManager();
    const formManager = new FormManager();

    // Gestionnaire de thème
    document.getElementById('theme-toggle')?.addEventListener('click', () => {
        themeManager.toggle();
    });

    // Gestionnaire de notifications
    window.notify = (message, type) => {
        notificationManager.show(message, type);
    };

    // Initialisation des tooltips Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Gestionnaire de chargement
    window.showLoading = () => {
        const loading = document.createElement('div');
        loading.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-dark bg-opacity-50';
        loading.innerHTML = '<div class="spinner text-light"></div>';
        document.body.appendChild(loading);
        return loading;
    };

    window.hideLoading = (loading) => {
        if (loading) {
            loading.remove();
        }
    };
}); 